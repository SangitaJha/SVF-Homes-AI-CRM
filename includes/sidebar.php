<?php
declare(strict_types=1);

$resources = config('resources');
$menu = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => module_url('dashboard')],
];
foreach ($resources as $resourceKey => $resourceMeta) {
    $menu[] = [
        'key' => $resourceKey,
        'label' => $resourceMeta['label'] ?? ucfirst($resourceKey),
        'icon' => 'bi-table',
        'url' => app_url($resourceMeta['route'] ?? '/' . ltrim($resourceKey, '/')),
    ];
}
$menu = array_merge($menu, [
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'bi-bar-chart-line', 'url' => app_url('/reports')],
    ['key' => 'ai', 'label' => 'AI Assistant', 'icon' => 'bi-stars', 'url' => app_url('/ai')],
    ['key' => 'users', 'label' => 'Users', 'icon' => 'bi-person-badge', 'url' => app_url('/users')],
    ['key' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear', 'url' => app_url('/settings')],
]);
?>
<aside class="crm-sidebar d-flex flex-column">
    <div class="sidebar-brand d-flex align-items-center justify-content-between gap-3 px-4 py-4">
        <a class="d-flex align-items-center gap-3 text-decoration-none" href="<?= e(module_url('dashboard')) ?>">
            <div class="brand-mark">SVF</div>
            <div class="brand-copy">
                <div class="fw-bold text-white"><?= e(config('app.name', 'SVF Homes AI CRM')) ?></div>
                <div class="small text-white-50">Enterprise CRM</div>
            </div>
        </a>
        <button class="btn btn-icon sidebar-toggle d-none d-lg-flex" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>
    <div class="px-3 pb-3 flex-grow-1 overflow-auto">
        <div class="nav nav-pills flex-column gap-1">
            <?php foreach ($menu as $item): ?>
                <?php $active = nav_active($item['key']); ?>
                <a class="nav-link crm-nav-link <?= $active ?>" href="<?= e($item['url']) ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i><span class="nav-label"><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="sidebar-footer p-4">
        <div class="small text-white-50">Signed in as</div>
        <div class="fw-semibold text-white"><?= e($currentUser['name'] ?? 'Guest') ?></div>
        <div class="small text-white-50 mb-3"><?= e($currentUser['role'] ?? '') ?></div>
        <a class="btn btn-outline-light w-100" href="<?= e(module_url('auth', 'logout.php')) ?>">Logout</a>
    </div>
</aside>