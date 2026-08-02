<?php
declare(strict_types=1);

$authPage = $authPage ?? false;
$pageTitle = $pageTitle ?? (string)config('app.name', 'SVF Homes AI CRM');
$currentUser = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/app.css')) ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="<?= $authPage ? 'auth-shell' : 'crm-shell' ?>">
<?php if ($authPage): ?>
<div class="auth-shell-bg"></div>
<?php else: ?>
<div class="crm-shell-bg"></div>
<div class="d-flex min-vh-100 crm-layout">
<?php endif; ?>