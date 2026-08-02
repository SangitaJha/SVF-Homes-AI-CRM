<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_auth();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$callerTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
$callerFile = $callerTrace[1]['file'] ?? '';
$callerScriptName = str_replace('\\', '/', $callerFile);
$modules = config('resources', []);

$moduleKey = $moduleKey ?? basename(dirname($scriptName));
$action = $action ?? pathinfo(basename($scriptName), PATHINFO_FILENAME);

if (!isset($modules[$moduleKey]) && $callerScriptName !== '') {
    $fallbackModule = basename(dirname($callerScriptName));
    if (isset($modules[$fallbackModule])) {
        $moduleKey = $fallbackModule;
    }
}

if (!in_array($action, ['index', 'add', 'edit', 'delete'], true) && $callerScriptName !== '') {
    $fallbackAction = pathinfo(basename($callerScriptName), PATHINFO_FILENAME);
    if (in_array($fallbackAction, ['index', 'add', 'edit', 'delete'], true)) {
        $action = $fallbackAction;
    }
}

if (!isset($modules[$moduleKey])) {
    http_response_code(404);
    exit('Module not found');
}

require_resource_permission($moduleKey);

$module = $modules[$moduleKey];
$db = \App\Core\Database::connection();
$table = $module['table'];
$fields = $module['fields'];
$columns = $module['columns'] ?? array_map(static fn(array $field) => $field['name'], $fields);
$pageTitle = $module['label'];
$recordId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$record = null;

$fieldLookup = [];
foreach ($fields as $field) {
    $fieldLookup[$field['name']] = $field;
}

if ($recordId > 0) {
    $statement = $db->prepare('SELECT * FROM `' . $table . '` WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $recordId]);
    $record = $statement->fetch() ?: null;
}

if ($action === 'delete' && is_post()) {
    verify_csrf();
    if ($record) {
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'file' && !empty($record[$field['name']])) {
                delete_uploaded_file((string)$record[$field['name']]);
            }
        }

        $statement = $db->prepare('DELETE FROM `' . $table . '` WHERE id = :id');
        $statement->execute(['id' => $recordId]);
        flash('success', $module['label'] . ' deleted successfully.');
    }

    redirect(trim($module['route'], '/'));
}

if (in_array($action, ['add', 'edit'], true) && is_post()) {
    verify_csrf();
    $input = [];
    $errors = [];

    foreach ($fields as $field) {
        $name = $field['name'];
        $type = $field['type'] ?? 'text';
        $posted = $_POST[$name] ?? '';

        if (($fieldLookup[$name]['type'] ?? '') === 'file') {
            $uploaded = upload_file($_FILES[$name] ?? [], $table);
            $input[$name] = $uploaded ?: ($record[$name] ?? null);
            continue;
        }

        if ($table === 'users' && $name === 'password') {
            $password = trim((string)$posted);
            if ($password === '') {
                if ($action === 'add') {
                    $errors[$name][] = 'Password is required.';
                } elseif ($record) {
                    $input[$name] = $record[$name];
                }
                continue;
            }

            $input[$name] = password_hash($password, PASSWORD_DEFAULT);
            continue;
        }

        $value = trim((string)$posted);

        if (!empty($field['required']) && $value === '') {
            $errors[$name][] = 'This field is required.';
        }

        if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$name][] = 'Enter a valid email address.';
        }

        if ($type === 'number' && $value !== '' && !is_numeric($value)) {
            $errors[$name][] = 'Enter a valid number.';
        }

        if (($field['options'] ?? null) && $value !== '' && !in_array($value, $field['options'], true)) {
            $errors[$name][] = 'Choose a valid option.';
        }

        if (!empty($field['unique']) && $value !== '') {
            $uniqueSql = 'SELECT id FROM `' . $table . '` WHERE `' . $name . '` = :value';
            $uniqueParams = ['value' => $value];
            if ($recordId > 0) {
                $uniqueSql .= ' AND id != :current_id';
                $uniqueParams['current_id'] = $recordId;
            }
            $uniqueSql .= ' LIMIT 1';
            $uniqueStmt = $db->prepare($uniqueSql);
            $uniqueStmt->execute($uniqueParams);
            if ($uniqueStmt->fetchColumn()) {
                $errors[$name][] = $field['label'] . ' already exists.';
            }
        }

        $input[$name] = $value === '' ? null : $value;
    }

    if (!$errors) {
        foreach ($input as $col => $val) {
            if (is_string($col) && substr($col, -3) === '_id' && $val !== null) {
                $parentTable = rtrim($col, '_id') . 's';
                try {
                    $check = $db->prepare('SELECT 1 FROM `' . $parentTable . '` WHERE id = :id LIMIT 1');
                    $check->execute(['id' => $val]);
                    $found = $check->fetchColumn();
                } catch (Throwable $e) {
                    $found = false;
                }

                if (!$found) {
                    $errors[$col][] = 'Selected ' . str_replace('_', ' ', rtrim($col, '_id')) . ' does not exist.';
                }
            }
        }
    }

    if (!$errors) {
        if ($action === 'add') {
            $columnsSql = array_keys($input);
            $placeholders = array_map(static fn($column) => ':' . $column, $columnsSql);
            $statement = $db->prepare('INSERT INTO `' . $table . '` (`' . implode('`,`', $columnsSql) . '`, created_at, updated_at) VALUES (' . implode(',', $placeholders) . ', NOW(), NOW())');
            try {
                $statement->execute($input);
                flash('success', $module['label'] . ' created successfully.');
                redirect(trim($module['route'], '/'));
            } catch (PDOException $exception) {
                $errorInfo = $exception->errorInfo ?? [];
                if (($errorInfo[0] ?? '') === '23000' && ($errorInfo[1] ?? 0) === 1062) {
                    $errors['database'][] = 'A record with the same unique value already exists.';
                } else {
                    $errors['database'][] = 'A database error occurred while saving the record.';
                }
            }
        }

        if ($action === 'edit') {
            $sets = [];
            foreach ($input as $column => $value) {
                $sets[] = '`' . $column . '` = :' . $column;
            }
            $input['id'] = $recordId;
            $statement = $db->prepare('UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE id = :id');
            try {
                $statement->execute($input);
                flash('success', $module['label'] . ' updated successfully.');
                redirect(trim($module['route'], '/'));
            } catch (PDOException $exception) {
                $errorInfo = $exception->errorInfo ?? [];
                if (($errorInfo[0] ?? '') === '23000' && ($errorInfo[1] ?? 0) === 1062) {
                    $errors['database'][] = 'A record with the same unique value already exists.';
                } else {
                    $errors['database'][] = 'A database error occurred while saving the record.';
                }
            }
        }
    }

    if ($errors) {
        $_SESSION['_flash']['errors'] = $errors;
        collect_old_input($_POST);
        flash('error', 'Please fix the highlighted fields.');
    }
}

include __DIR__ . '/header.php';

if ($action !== 'index' && $action !== 'add' && $action !== 'edit' && $action !== 'delete') {
    $action = 'index';
}

if ($action !== 'delete') {
    include __DIR__ . '/sidebar.php';
    echo '<div class="crm-main flex-grow-1">';
    include __DIR__ . '/navbar.php';
    echo '<div class="container-fluid p-4">';
}

if ($action === 'index') {
    $search = trim((string)($_GET['q'] ?? ''));
    $sql = 'SELECT * FROM `' . $table . '`';
    $params = [];
    if ($search !== '') {
        $searchColumns = array_map(static fn($field) => '`' . $field['name'] . '`', $fields);
        $sql .= ' WHERE CONCAT_WS(" ", ' . implode(', ', $searchColumns) . ') LIKE :term';
        $params['term'] = '%' . $search . '%';
    }
    $sql .= ' ORDER BY id DESC LIMIT 200';
    $statement = $db->prepare($sql);
    $statement->execute($params);
    $records = $statement->fetchAll();
    ?>
    <div class="card crm-card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                <form class="d-flex gap-2" method="get">
                    <input type="search" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Search <?= e($module['label']) ?>">
                    <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <a class="btn btn-accent" href="<?= e(module_url($moduleKey, 'add.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Add <?= e($module['label']) ?></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <?php foreach ($columns as $column): ?>
                            <th><?= e(ucwords(str_replace('_', ' ', $column))) ?></th>
                        <?php endforeach; ?>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= e($row['id']) ?></td>
                            <?php foreach ($columns as $column): ?>
                                <td><?= e($row[$column] ?? '-') ?></td>
                            <?php endforeach; ?>
                            <td class="text-end">
                                <a href="<?= e(module_url($moduleKey, 'edit.php', ['id' => $row['id']])) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-pencil"></i></a>
                                <a href="<?= e(module_url($moduleKey, 'delete.php', ['id' => $row['id']])) ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

if (in_array($action, ['add', 'edit'], true)) {
    $recordValues = $record ?: [];
    ?>
    <div class="card crm-card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="small text-uppercase text-white-50"><?= e($module['label']) ?></div>
                    <h2 class="h4 mb-0"><?= e(ucfirst($action)) ?> <?= e($module['label']) ?></h2>
                </div>
                <a href="<?= e(module_url($moduleKey)) ?>" class="btn btn-outline-light">Back</a>
            </div>
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <?php if ($record): ?><input type="hidden" name="id" value="<?= e($record['id']) ?>"><?php endif; ?>
                <div class="row g-3">
                    <?php foreach ($fields as $field): ?>
                        <?php if ($table === 'users' && $field['name'] === 'password' && $action === 'edit'): ?>
                            <?php $fieldValue = ''; ?>
                        <?php else: ?>
                            <?php $fieldValue = old($field['name'], normalize_input_value($field, $recordValues[$field['name']] ?? '')); ?>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label"><?= e($field['label']) ?></label>
                            <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                                <textarea name="<?= e($field['name']) ?>" class="form-control" rows="4"><?= e($fieldValue) ?></textarea>
                            <?php elseif (($field['type'] ?? '') === 'select'): ?>
                                <select name="<?= e($field['name']) ?>" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach (($field['options'] ?? []) as $option): ?>
                                        <option value="<?= e($option) ?>" <?= $fieldValue === (string)$option ? 'selected' : '' ?>><?= e($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif (($field['type'] ?? '') === 'file'): ?>
                                <input type="file" name="<?= e($field['name']) ?>" class="form-control">
                                <?php if (!empty($recordValues[$field['name']])): ?>
                                    <div class="small text-white-50 mt-2">Current: <a href="<?= e(app_url($recordValues[$field['name']])) ?>" target="_blank"><?= e(basename((string)$recordValues[$field['name']])) ?></a></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <input type="<?= e($field['type'] ?? 'text') ?>" name="<?= e($field['name']) ?>" value="<?= e($fieldValue) ?>" class="form-control">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-accent" type="submit">Save</button>
                    <a class="btn btn-outline-light" href="<?= e(module_url($moduleKey)) ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
}

if ($action === 'delete') {
    ?>
    <div class="card crm-card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h4 mb-3">Delete <?= e($module['label']) ?></h2>
            <?php if ($record): ?>
                <p class="text-white-50">Are you sure you want to delete this record?</p>
                <div class="table-responsive mb-4">
                    <table class="table table-dark table-borderless align-middle">
                        <tbody>
                        <?php foreach ($columns as $column): ?>
                            <tr>
                                <th><?= e(ucwords(str_replace('_', ' ', $column))) ?></th>
                                <td><?= e($record[$column] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($record['id']) ?>">
                    <button class="btn btn-danger" type="submit">Yes, delete</button>
                    <a class="btn btn-outline-light" href="<?= e(module_url($moduleKey)) ?>">Cancel</a>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Record not found.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

if ($action !== 'delete') {
    echo '</div></div>';
}

include __DIR__ . '/footer.php';