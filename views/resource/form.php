<?php
$pageTitle = ($record ? 'Edit ' : 'Create ') . $meta['label'];
$hasFiles = false;
foreach ($meta['fields'] as $field) {
    if (($field['type'] ?? '') === 'file') {
        $hasFiles = true;
        break;
    }
}
?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= e(app_url($record ? $meta['route'] . '/' . $record['id'] : $meta['route'])) ?>" <?= $hasFiles ? 'enctype="multipart/form-data"' : '' ?>>
            <?= csrf_field() ?>
            <div class="row g-3">
                <?php foreach ($meta['fields'] as $field): ?>
                    <?php $value = old($field['name'], $record[$field['name']] ?? ''); if (($field['name'] ?? '') === 'password') { $value = ''; } ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= e($field['label']) ?></label>
                        <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                            <textarea name="<?= e($field['name']) ?>" class="form-control" rows="4"><?= e($value) ?></textarea>
                        <?php elseif (($field['type'] ?? '') === 'select'): ?>
                            <select name="<?= e($field['name']) ?>" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (($field['options'] ?? []) as $option): ?>
                                    <option value="<?= e($option) ?>" <?= (string)$value === (string)$option ? 'selected' : '' ?>><?= e($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (($field['type'] ?? '') === 'file'): ?>
                            <input type="file" name="<?= e($field['name']) ?>" class="form-control">
                            <?php if (!empty($record[$field['name']])): ?>
                                <small class="text-muted d-block mt-1">Current: <?= e($record[$field['name']]) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <input type="<?= e($field['type'] ?? 'text') ?>" name="<?= e($field['name']) ?>" value="<?= e($value) ?>" class="form-control">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-accent" type="submit">Save</button>
                <a class="btn btn-outline-secondary" href="<?= e(app_url($meta['route'])) ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>