<?php

declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return $value === false || $value === null || $value === '' ? $default : $value;
}

function config(string $key, mixed $default = null): mixed
{
    static $cache = [];
    [$file, $path] = array_pad(explode('.', $key, 2), 2, null);
    if (!$file) {
        return $default;
    }

    if (!isset($cache[$file])) {
        $configPath = __DIR__ . '/../config/' . $file . '.php';
        $cache[$file] = is_file($configPath) ? require $configPath : [];
    }

    if ($path === null) {
        return $cache[$file];
    }

    $value = $cache[$file];
    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function app_url(string $path = ''): string
{
    $base = rtrim((string)env('APP_URL', ''), '/');
    if ($base === '') {
        $scriptBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $base = $scriptBase === '' ? '' : $scriptBase;
    }

    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return app_url('assets/' . ltrim($path, '/'));
}

function storage_path(string $path = ''): string
{
    return __DIR__ . '/../storage/' . ltrim($path, '/');
}

function uploads_path(string $path = ''): string
{
    return __DIR__ . '/../uploads/' . ltrim($path, '/');
}

function ensure_directory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function format_currency(float|int|string|null $amount): string
{
    return (string)config('app.currency_symbol', '₹') . number_format((float)$amount, 2);
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d M Y', strtotime($date));
}

function format_datetime(?string $dateTime): string
{
    if (!$dateTime) {
        return '-';
    }

    return date('d M Y, h:i A', strtotime($dateTime));
}

function upload_file(array $file, string $directory, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $originalName = (string)($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    $targetDirectory = uploads_path(trim($directory, '/'));
    ensure_directory($targetDirectory);

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
        return null;
    }

    return 'uploads/' . trim($directory, '/') . '/' . $filename;
}

function delete_uploaded_file(?string $path): void
{
    if (!$path) {
        return;
    }

    $absolute = __DIR__ . '/../' . ltrim($path, '/');
    if (is_file($absolute)) {
        unlink($absolute);
    }
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, mixed $value = null): mixed
{
    if (func_num_args() === 2) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function collect_old_input(array $input): void
{
    $_SESSION['_old'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function flash_errors(): array
{
    $errors = $_SESSION['_flash']['errors'] ?? [];
    unset($_SESSION['_flash']['errors']);

    return is_array($errors) ? $errors : [];
}

function csrf_token(): string
{
    $name = (string)env('CSRF_TOKEN_NAME', 'csrf_token');
    if (empty($_SESSION[$name])) {
        $_SESSION[$name] = bin2hex(random_bytes(32));
    }
    return $_SESSION[$name];
}

function csrf_field(): string
{
    $name = (string)env('CSRF_TOKEN_NAME', 'csrf_token');
    return '<input type="hidden" name="' . e($name) . '" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $name = (string)env('CSRF_TOKEN_NAME', 'csrf_token');
    $posted = $_POST[$name] ?? '';
    if (!hash_equals((string)($_SESSION[$name] ?? ''), (string)$posted)) {
        http_response_code(419);
        exit('CSRF token mismatch');
    }
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_check(): bool
{
    return !empty($_SESSION['user']);
}

function auth_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function require_auth(): void
{
    if (!auth_check()) {
        redirect('/login');
    }
}

function require_role(array|string $roles): void
{
    $roles = (array)$roles;
    if (!in_array(auth_role(), $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function can_access_resource(string $resource): bool
{
    $roles = config('permissions.' . $resource);
    return $roles === null || in_array(auth_role(), (array)$roles, true);
}

function require_resource_permission(string $resource): void
{
    if (!can_access_resource($resource)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $layout = $layout ?? 'app';
    ob_start();
    require __DIR__ . '/../views/' . $template . '.php';
    $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/' . $layout . '.php';
}

function current_script_folder(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return basename(dirname($scriptName));
}

function audit_log(string $module, string $action, string $description, ?int $userId = null): void
{
    try {
        $db = \App\Core\Database::connection();
        $statement = $db->prepare('INSERT INTO audit_logs (user_id, module, action, description, ip_address, created_at) VALUES (:user_id, :module, :action, :description, :ip_address, NOW())');
        $statement->execute([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable) {
        // Ignore logging failures so the core flow continues.
    }
}

function activity_log(string $module, string $action, string $description, ?int $userId = null): void
{
    try {
        $db = \App\Core\Database::connection();
        $statement = $db->prepare('INSERT INTO activities (user_id, module, action, description, created_at) VALUES (:user_id, :module, :action, :description, NOW())');
        $statement->execute([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
        ]);
    } catch (Throwable) {
        // Ignore logging failures so the core flow continues.
    }
}

function nav_active(array|string $folders): string
{
    $folders = (array)$folders;
    return in_array(current_script_folder(), $folders, true) ? 'active' : '';
}

function module_url(string $module, string $page = 'index.php', array $params = []): string
{
    $path = trim($module, '/') . '/' . ltrim($page, '/');
    if ($params) {
        $path .= '?' . http_build_query($params);
    }

    return app_url($path);
}

function normalize_input_value(array $field, mixed $value): string
{
    $type = $field['type'] ?? 'text';
    if ($value === null) {
        return '';
    }

    if ($type === 'date' && $value !== '') {
        return substr((string)$value, 0, 10);
    }

    if ($type === 'datetime-local' && $value !== '') {
        return str_replace(' ', 'T', substr((string)$value, 0, 16));
    }

    return (string)$value;
}

function storage_json_read(string $path, array $default = []): array
{
    if (!is_file($path)) {
        return $default;
    }

    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? $data : $default;
}

function storage_json_write(string $path, array $data): bool
{
    ensure_directory(dirname($path));
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}
