<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][] = [$path, $handler];
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $path = $this->request->path();

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper((string)$_POST['_method']);
        }

        foreach ($this->routes[$method] ?? [] as [$route, $handler]) {
            $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $route);
            $pattern = '#^' . rtrim($pattern, '/') . '/?$#';
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $this->execute($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        echo 'Page not found';
    }

    private function execute(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        [$class, $method] = $handler;
        $controller = new $class();
        call_user_func_array([$controller, $method], $params);
    }
}
