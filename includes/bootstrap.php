<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

load_env(__DIR__ . '/../.env');

date_default_timezone_set((string)config('app.timezone', 'Asia/Kolkata'));

if ((bool)config('app.debug', false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionName = $_ENV['SESSION_NAME'] ?? 'svf_homes_session';
    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120) * 60,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $basePath = __DIR__ . '/../';
    $file = $basePath . str_replace('\\', '/', $relative) . '.php';
    if (!is_file($file)) {
        $parts = explode('/', str_replace('\\', '/', $relative));
        if (!empty($parts)) {
            $parts[0] = strtolower($parts[0]);
            $file = $basePath . implode('/', $parts) . '.php';
        }
    }

    if (is_file($file)) {
        require $file;
    }
});
