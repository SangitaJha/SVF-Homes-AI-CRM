<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_auth();
require_resource_permission('land_acquisition');

$section = trim((string)($_GET['section'] ?? 'dashboard'));
$controller = new App\Controllers\LandAcquisitionController();

if ($section === 'dashboard') {
    $controller->dashboard();
    return;
}

if (is_post() && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $controller->deleteModule($section, (int)$_GET['delete']);
    return;
}

if (is_post()) {
    $controller->storeModule($section);
    return;
}

if ($section !== '' && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $controller->deleteModule($section, (int)$_GET['delete']);
    return;
}

$controller->module($section);
