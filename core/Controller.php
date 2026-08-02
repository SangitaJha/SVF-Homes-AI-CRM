<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $template, array $data = []): void
    {
        view($template, $data);
    }

    protected function json(array $data, int $status = 200): never
    {
        json_response($data, $status);
    }
}
