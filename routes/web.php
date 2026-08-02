<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ResourceController;
use App\Controllers\AiController;
use App\Controllers\ReportsController;
use App\Controllers\NotificationsController;
use App\Controllers\LabourController;

$router->get('/', function (): void {
    if (auth_check()) {
        redirect('/dashboard');
    }
    redirect('/login');
});

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/change-password', [AuthController::class, 'changePassword']);
$router->post('/change-password', [AuthController::class, 'updatePassword']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$resourceRoutes = config('resources');
foreach ($resourceRoutes as $key => $meta) {
    $router->get($meta['route'], fn() => (new ResourceController())->index($key));
    $router->post($meta['route'] . '/bulk-delete', fn() => (new ResourceController())->bulkDelete($key));
    $router->post($meta['route'] . '/bulk-update', fn() => (new ResourceController())->bulkUpdate($key));
    $router->post($meta['route'] . '/import', fn() => (new ResourceController())->import($key));
    $router->get($meta['route'] . '/export/{type}', fn($type) => (new ResourceController())->export($key, (string)$type));
    $router->get($meta['route'] . '/create', fn() => (new ResourceController())->create($key));
    $router->post($meta['route'], fn() => (new ResourceController())->store($key));
    $router->get($meta['route'] . '/{id}', fn($id) => (new ResourceController())->show($key, (int)$id));
    $router->get($meta['route'] . '/{id}/edit', fn($id) => (new ResourceController())->edit($key, (int)$id));
    $router->post($meta['route'] . '/{id}', fn($id) => (new ResourceController())->update($key, (int)$id));
    $router->post($meta['route'] . '/{id}/delete', fn($id) => (new ResourceController())->destroy($key, (int)$id));
}

$router->get('/ai', [AiController::class, 'index']);
$router->get('/ai/automation-dashboard', [\App\Controllers\AiAutomationController::class, 'dashboard']);
$router->get('/ai/automation-rules', [\App\Controllers\AiAutomationController::class, 'automationRules']);
$router->get('/ai/insights', [\App\Controllers\AiAutomationController::class, 'insights']);
$router->post('/ai/chat', [AiController::class, 'chat']);
$router->post('/ai/lead-score', [AiController::class, 'leadScore']);
$router->post('/ai/whatsapp-message', [AiController::class, 'whatsappMessage']);
$router->post('/ai/email-message', [AiController::class, 'emailMessage']);
$router->post('/ai/call-summary', [AiController::class, 'callSummary']);
$router->post('/ai/property-recommendation', [AiController::class, 'propertyRecommendation']);
$router->get('/ai/dashboard-insights', [AiController::class, 'dashboardInsights']);
$router->post('/ai/document-reader', [AiController::class, 'documentReader']);
$router->post('/ai/voice-command', [AiController::class, 'voiceCommand']);
$router->post('/ai/sop-generator', [AiController::class, 'sopGenerator']);

$router->get('/reports', [ReportsController::class, 'index']);
$router->get('/reports/export/pdf', [ReportsController::class, 'exportPdf']);
$router->get('/reports/export/excel', [ReportsController::class, 'exportExcel']);

$router->get('/notifications', [NotificationsController::class, 'index']);
$router->post('/notifications/send', [NotificationsController::class, 'sendReminder']);

$router->get('/labour', [LabourController::class, 'dashboard']);
$router->get('/labour/attendance', [LabourController::class, 'attendance']);
$router->post('/labour/attendance', [LabourController::class, 'storeAttendance']);
$router->post('/labour/attendance/{id}/delete', fn($id) => (new LabourController())->deleteAttendance((int)$id));
$router->get('/labour/work', [LabourController::class, 'work']);
$router->post('/labour/work', [LabourController::class, 'storeWork']);
$router->post('/labour/work/{id}/delete', fn($id) => (new LabourController())->deleteWork((int)$id));
$router->get('/labour/export/{type}/{format}', fn($type, $format) => (new LabourController())->export((string)$type, (string)$format));
