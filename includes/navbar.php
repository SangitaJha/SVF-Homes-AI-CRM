<?php
declare(strict_types=1);
?>
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
        <a class="btn btn-outline-primary btn-sm d-none d-md-inline-flex" href="<?= e(module_url('ai')) ?>"><i class="bi bi-stars me-1"></i>AI</a>
        <a class="btn btn-accent btn-sm" href="<?= e(module_url('leads', 'add.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Quick Add</a>
        <div class="topbar-icon"><i class="bi bi-bell"></i></div>
        <div class="dropdown">
            <button class="btn btn-glass btn-sm dropdown-toggle" data-bs-toggle="dropdown" type="button">
                <i class="bi bi-person-circle me-1"></i><?= e($currentUser['name'] ?? 'Account') ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                <li><a class="dropdown-item" href="<?= e(module_url('users', 'edit.php', ['id' => (int)($currentUser['id'] ?? 0)])) ?>">Profile</a></li>
                <li><a class="dropdown-item" href="<?= e(module_url('settings')) ?>">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= e(module_url('auth', 'logout.php')) ?>">Logout</a></li>
            </ul>
        </div>
    </div>
</header>