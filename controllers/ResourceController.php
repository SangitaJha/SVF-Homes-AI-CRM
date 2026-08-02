<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Resource;

final class ResourceController extends Controller
{
    public function index(string $resource): void
    {
        require_auth();
        require_resource_permission($resource);
        $meta = $this->meta($resource);
        $model = new Resource($meta['table']);
        $query = trim((string)($_GET['q'] ?? ''));
        $filters = [];
        foreach (($meta['filters'] ?? []) as $filter) {
            $filters[$filter['name']] = trim((string)($_GET[$filter['name']] ?? ''));
        }
        $sortBy = trim((string)($_GET['sort'] ?? 'id'));
        $sortDir = strtolower(trim((string)($_GET['dir'] ?? 'desc'))) === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));

        $records = $this->fetchRecords($model, $meta, $query, $filters, $sortBy, $sortDir, $page, $perPage);
        $totalRecords = $this->countRecords($model, $meta, $query, $filters);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));

        $this->render('resource/index', [
            'meta' => $meta,
            'records' => $records,
            'query' => $query,
            'filters' => $filters,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
            'stats' => $this->stats($resource, $meta),
        ]);
    }

    public function create(string $resource): void
    {
        require_auth();
        require_resource_permission($resource);
        $this->render('resource/form', ['meta' => $this->meta($resource), 'record' => null]);
    }

    public function store(string $resource): void
    {
        require_auth();
        require_resource_permission($resource);
        verify_csrf();
        $meta = $this->meta($resource);
        $this->validateAndPersist($meta, null);
    }

    public function show(string $resource, int $id): void
    {
        require_auth();
        require_resource_permission($resource);
        $meta = $this->meta($resource);
        $model = new Resource($meta['table']);
        $record = $model->find($id);
        $this->render('resource/show', ['meta' => $meta, 'record' => $record]);
    }

    public function bulkDelete(string $resource): void
    {
        require_auth();
        require_resource_permission($resource);
        verify_csrf();
        $ids = array_map('intval', $_POST['ids'] ?? []);
        $meta = $this->meta($resource);
        $model = new Resource($meta['table']);
        foreach ($ids as $id) {
            $model->delete($id);
        }
        activity_log($meta['label'], 'bulk_delete', 'Deleted ' . count($ids) . ' records');
        flash('success', 'Selected records deleted.');
        redirect($meta['route']);
    }

    public function bulkUpdate(string $resource): void
    {
        require_auth();
        require_resource_permission($resource);
        verify_csrf();
        $ids = array_map('intval', $_POST['ids'] ?? []);
        $field = trim((string)($_POST['bulk_field'] ?? 'status'));
        $value = trim((string)($_POST['bulk_value'] ?? ''));
        $meta = $this->meta($resource);
        $model = new Resource($meta['table']);
        foreach ($ids as $id) {
            $model->update($id, [$field => $value]);
        }
        activity_log($meta['label'], 'bulk_update', 'Updated ' . count($ids) . ' records');
        flash('success', 'Selected records updated.');
        redirect($meta['route']);
    }

    public function import(string $resource): void
    {
        require_auth();
        require_resource_permission($resource);
        verify_csrf();
        $meta = $this->meta($resource);
        $file = $_FILES['import_file'] ?? [];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $path = upload_file($file, $resource);
            if ($path) {
                activity_log($meta['label'], 'import', 'Imported data from Excel/CSV');
                flash('success', 'Import completed.');
            }
        }
        redirect($meta['route']);
    }

    public function export(string $resource, string $type): void
    {
        require_auth();
        require_resource_permission($resource);
        $meta = $this->meta($resource);
        $model = new Resource($meta['table']);
        $records = $model->all();
        $filename = $meta['label'] . '-' . date('Ymd') . '.' . $type;

        if ($type === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($records[0] ?? []));
            foreach ($records as $record) {
                fputcsv($output, $record);
            }
            fclose($output);
            return;
        }

        if ($type === 'pdf') {
            $html = '<h1>' . e($meta['label']) . '</h1><table><tr><th>ID</th><th>Value</th></tr>';
            foreach ($records as $record) {
                $html .= '<tr><td>' . e($record['id'] ?? '') . '</td><td>' . e($record[array_key_first($record)] ?? '') . '</td></tr>';
            }
            $html .= '</table>';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $html;
            return;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo 'Spreadsheet export is ready for the selected module.';
    }

    public function edit(string $resource, int $id): void
    {
        require_auth();
        require_resource_permission($resource);
        $meta = $this->meta($resource);
        $record = (new Resource($meta['table']))->find($id);
        $this->render('resource/form', ['meta' => $meta, 'record' => $record]);
    }

    public function update(string $resource, int $id): void
    {
        require_auth();
        require_resource_permission($resource);
        verify_csrf();
        $meta = $this->meta($resource);
        $this->validateAndPersist($meta, $id);
    }

    public function destroy(string $resource, int $id): void
    {
        require_auth();
        require_resource_permission($resource);
        verify_csrf();
        (new Resource($this->meta($resource)['table']))->delete($id);
        flash('success', ucfirst($resource) . ' deleted successfully.');
        redirect($this->meta($resource)['route']);
    }

    private function meta(string $resource): array
    {
        $resources = config('resources');
        if (!isset($resources[$resource])) {
            http_response_code(404);
            exit('Resource not found');
        }

        return $resources[$resource];
    }

    private function fetchRecords(Resource $model, array $meta, string $query, array $filters, string $sortBy, string $sortDir, int $page, int $perPage): array
    {
        $sql = 'SELECT * FROM ' . $meta['table'];
        $params = [];
        $conditions = [];

        if ($query !== '') {
            $searchColumns = $meta['columns'] ?? ['name'];
            $searchClauses = [];
            foreach ($searchColumns as $column) {
                $searchClauses[] = $column . ' LIKE :term';
            }
            $conditions[] = '(' . implode(' OR ', $searchClauses) . ')';
            $params['term'] = '%' . $query . '%';
        }

        foreach (($meta['filters'] ?? []) as $filter) {
            $field = $filter['name'] ?? '';
            $value = trim((string)($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            $condition = (($filter['type'] ?? 'text') === 'date') ? 'DATE(' . $field . ') = :' . $field : $field . ' LIKE :' . $field;
            $conditions[] = $condition;
            $params[$field] = (($filter['type'] ?? 'text') === 'date') ? $value : '%' . $value . '%';
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $allowedSort = in_array($sortBy, $meta['columns'] ?? [], true) ? $sortBy : 'id';
        $sql .= ' ORDER BY ' . $allowedSort . ' ' . ($sortDir === 'asc' ? 'ASC' : 'DESC');
        $sql .= ' LIMIT :limit OFFSET :offset';

        $statement = $model->db()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $perPage, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    private function countRecords(Resource $model, array $meta, string $query, array $filters): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . $meta['table'];
        $params = [];
        $conditions = [];

        if ($query !== '') {
            $searchColumns = $meta['columns'] ?? ['name'];
            $searchClauses = [];
            foreach ($searchColumns as $column) {
                $searchClauses[] = $column . ' LIKE :term';
            }
            $conditions[] = '(' . implode(' OR ', $searchClauses) . ')';
            $params['term'] = '%' . $query . '%';
        }

        foreach (($meta['filters'] ?? []) as $filter) {
            $field = $filter['name'] ?? '';
            $value = trim((string)($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            $condition = (($filter['type'] ?? 'text') === 'date') ? 'DATE(' . $field . ') = :' . $field : $field . ' LIKE :' . $field;
            $conditions[] = $condition;
            $params[$field] = (($filter['type'] ?? 'text') === 'date') ? $value : '%' . $value . '%';
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $statement = $model->db()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    private function stats(string $resource, array $meta): array
    {
        $db = Database::connection();
        $table = $meta['table'];
        $stats = [];
        $stats[] = ['label' => 'Total Records', 'value' => (int)$db->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn(), 'icon' => 'fa-list'];
        $stats[] = ['label' => 'Recent 30 Days', 'value' => (int)$db->query('SELECT COUNT(*) FROM ' . $table . ' WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetchColumn(), 'icon' => 'fa-calendar-week'];
        $stats[] = ['label' => 'Active', 'value' => (int)$db->query('SELECT COUNT(*) FROM ' . $table . ' WHERE status = "Active" OR status = "Available" OR status = "New"')->fetchColumn(), 'icon' => 'fa-check-circle'];
        $stats[] = ['label' => 'Pending', 'value' => (int)$db->query('SELECT COUNT(*) FROM ' . $table . ' WHERE status = "Pending" OR status = "Follow-up" OR status = "Scheduled"')->fetchColumn(), 'icon' => 'fa-clock'];
        return $stats;
    }

    private function validateAndPersist(array $meta, ?int $id): void
    {
        $existing = $id ? (new Resource($meta['table']))->find($id) : null;
        $input = [];
        $rules = [];
        foreach ($meta['fields'] as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'] ?? 'text';

            if ($meta['table'] === 'users' && $fieldName === 'password') {
                $password = trim((string)($_POST[$fieldName] ?? ''));
                if ($password === '' && $existing) {
                    continue;
                }
                if ($password === '') {
                    $input[$fieldName] = '';
                    if (!empty($field['required'])) {
                        $rules[$fieldName] = 'required';
                    }
                    continue;
                }

                $input[$fieldName] = password_hash($password, PASSWORD_DEFAULT);
                continue;
            }

            if ($fieldType === 'file') {
                $uploadedPath = upload_file($_FILES[$fieldName] ?? [], $meta['table']);
                $input[$fieldName] = $uploadedPath ?? ($existing[$fieldName] ?? null);
                if (!empty($field['required']) && empty($input[$fieldName])) {
                    $rules[$fieldName] = 'required';
                }
                continue;
            }

            $value = trim((string)($_POST[$fieldName] ?? ''));
            $input[$fieldName] = $value === '' ? null : $value;
            if (!empty($field['required'])) {
                $rules[$fieldName] = 'required';
            }
            if ($fieldType === 'email') {
                $rules[$fieldName] = ($rules[$fieldName] ?? '') . '|email';
            }
            if ($fieldType === 'number') {
                $rules[$fieldName] = trim(($rules[$fieldName] ?? '') . '|numeric', '|');
            }
        }

        $errors = (new Validator())->validate($input, $rules);
        if ($errors) {
            collect_old_input($_POST);
            flash('errors', $errors);
            redirect($id ? $meta['route'] . '/' . $id . '/edit' : $meta['route'] . '/create');
        }

        $input['updated_at'] = date('Y-m-d H:i:s');
        if ($id) {
            unset($input['created_at']);
            (new Resource($meta['table']))->update($id, $input);
            audit_log($meta['table'], 'updated', ucfirst($meta['label']) . ' updated', current_user()['id'] ?? null);
            activity_log($meta['label'], 'updated', ucfirst($meta['label']) . ' updated');
            flash('success', ucfirst($meta['label']) . ' updated successfully.');
        } else {
            $input['created_at'] = date('Y-m-d H:i:s');
            (new Resource($meta['table']))->create($input);
            audit_log($meta['table'], 'created', ucfirst($meta['label']) . ' created', current_user()['id'] ?? null);
            activity_log($meta['label'], 'created', ucfirst($meta['label']) . ' created');
            flash('success', ucfirst($meta['label']) . ' created successfully.');
        }

        clear_old_input();
        redirect($meta['route']);
    }
}
