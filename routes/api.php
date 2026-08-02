<?php

declare(strict_types=1);

use App\Controllers\ApiController;

$router->get('/api/v1/metrics', [ApiController::class, 'metrics']);
$router->get('/api/v1/leads', [ApiController::class, 'leads']);
$router->get('/api/v1/customers', [ApiController::class, 'customers']);
$router->get('/api/v1/projects', [ApiController::class, 'projects']);
$router->get('/api/v1/bookings', [ApiController::class, 'bookings']);
$router->get('/api/v1/payments', [ApiController::class, 'payments']);
$router->get('/api/v1/notifications', [ApiController::class, 'notifications']);
$router->post('/api/v1/ai/score', [ApiController::class, 'aiScore']);
$router->post('/api/v1/ai/chat', [ApiController::class, 'aiChat']);
