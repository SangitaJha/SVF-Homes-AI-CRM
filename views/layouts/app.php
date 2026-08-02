<?php $currentUser = current_user(); $resources = config('resources'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="crm-body app-shell">
<div class="app-background"></div>
<div class="d-flex min-vh-100 crm-layout">
    <aside class="crm-sidebar p-2 d-flex flex-column">
        <div class="sidebar-brand d-flex align-items-center justify-content-between gap-3 px-2 py-3">
            <a class="d-flex align-items-center gap-3 text-decoration-none" href="<?= e(app_url('/dashboard')) ?>">
                <img src="<?= e(app_url('/SVFLOGO.png')) ?>" alt="SVF Homes logo" class="brand-mark">
                <div class="brand-copy">
                    <div class="text-white fw-bold"><?= e(config('app.name')) ?></div>
                    <small class="text-white-50">Enterprise CRM</small>
                </div>
            </a>
            <button class="btn btn-icon sidebar-toggle d-none d-lg-flex" type="button" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <nav class="nav flex-column gap-1 mt-3">
            <a class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/dashboard' ? 'active' : '' ?>" href="<?= e(app_url('/dashboard')) ?>"><i class="fa-solid fa-gauge-high me-2"></i><span class="nav-label">Dashboard</span></a>
            <?php $resourceIcons = [
                'leads' => 'fa-bullhorn',
                'customers' => 'fa-users',
                'projects' => 'fa-city',
                'flats' => 'fa-building',
                'sitevisits' => 'fa-location-dot',
                'quotations' => 'fa-file-invoice',
                'bookings' => 'fa-calendar-check',
                'payments' => 'fa-indian-rupee-sign',
                'followups' => 'fa-phone-volume',
                'documents' => 'fa-folder-open',
                'notifications' => 'fa-bell',
                'contractors' => 'fa-person-gear',
                'labours' => 'fa-person-vcard',
                'employees' => 'fa-person-badge',
                'leave_requests' => 'fa-calendar2-week',
                'payrolls' => 'fa-wallet2',
                'suppliers' => 'fa-truck',
                'materials' => 'fa-box-seam',
                'inventories' => 'fa-boxes',
                'purchases' => 'fa-cart3',
                'tasks' => 'fa-list-check',
                'meetings' => 'fa-calendar-event',
                'sop_templates' => 'fa-file-earmark-text',
                'sops' => 'fa-journal-text',
                'users' => 'fa-user-shield',
            ];
            foreach ($resources as $key => $resource):
                $route = $resource['route'] ?? '/' . ltrim($key, '/');
                $active = str_contains($_SERVER['REQUEST_URI'] ?? '', $route) ? 'active' : '';
                $icon = $resourceIcons[$key] ?? 'fa-table';
            ?>
                <a class="nav-link <?= $active ?>" href="<?= e(app_url($route)) ?>"><i class="fa-solid <?= e($icon) ?> me-2"></i><span class="nav-label"><?= e($resource['label'] ?? ucfirst($key)) ?></span></a>
            <?php endforeach; ?>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/land-acquisition') ? 'active' : '' ?>" href="<?= e(app_url('/land-acquisition/index.php')) ?>"><i class="fa-solid fa-map-location-dot me-2"></i><span class="nav-label">Land Acquisition</span></a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/reports') ? 'active' : '' ?>" href="<?= e(app_url('/reports')) ?>"><i class="fa-solid fa-chart-line me-2"></i><span class="nav-label">Reports</span></a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/ai') ? 'active' : '' ?>" href="<?= e(app_url('/ai/automation-dashboard')) ?>"><i class="fa-solid fa-robot me-2"></i><span class="nav-label">AI & Automation</span></a>
            <a class="nav-link mt-2 logout-link" href="<?= e(app_url('/logout')) ?>"><i class="fa-solid fa-right-from-bracket me-2"></i><span class="nav-label">Logout</span></a>
        </nav>
        <div class="sidebar-footer mt-auto pt-4">
            <div class="text-white-50 small">Logged in as</div>
            <div class="text-white fw-semibold"><?= e($currentUser['name'] ?? 'Guest') ?></div>
            <div class="text-white-50 small mb-3"><?= e($currentUser['role'] ?? '') ?></div>
        </div>
    </aside>
    <main class="crm-main flex-grow-1">
        <header class="crm-topbar px-3 px-lg-4 py-3 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <button class="btn btn-icon d-lg-none" type="button" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <div class="search-shell flex-grow-1">
                    <i class="bi bi-search text-muted"></i>
                    <input type="text" placeholder="Search leads, projects, follow-ups...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-primary btn-sm d-none d-md-inline-flex" href="<?= e(app_url('/ai')) ?>"><i class="bi bi-stars me-1"></i>AI</a>
                <a class="btn btn-accent btn-sm" href="<?= e(app_url('/leads/create')) ?>"><i class="bi bi-plus-lg me-1"></i>Quick Add</a>
                <div class="topbar-icon"><i class="bi bi-bell"></i></div>
            </div>
        </header>
        <div class="container-fluid dashboard-content px-3 px-lg-4 py-4">
            <?php if ($message = flash('success')): ?>
                <div class="alert alert-success border-0 shadow-sm mb-3"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash('error')): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-3"><?= e($message) ?></div>
            <?php endif; ?>
            <?php $errors = flash_errors(); if ($errors): ?>
                <div class="alert alert-warning border-0 shadow-sm mb-3">
                    <?php foreach ($errors as $field => $messages): ?>
                        <div><strong><?= e(ucfirst($field)) ?>:</strong> <?= e(implode(' ', (array)$messages)) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>