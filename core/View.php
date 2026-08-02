<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function make(string $template, array $data = []): void
    {
        view($template, $data);
    }
}
