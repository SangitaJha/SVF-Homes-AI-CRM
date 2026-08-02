<?php

declare(strict_types=1);

namespace App\Middleware;

final class AuthMiddleware
{
    public function handle(array $roles = []): void
    {
        require_auth();
        if ($roles !== [] && !in_array(auth_role(), $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}