<?php $pageTitle = 'Reports'; ?>
<div class="row g-4">
    <div class="col-lg-4"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e($reports['daily'] ?? 0) ?></div><div class="text-muted">Daily Leads</div></div></div></div>
    <div class="col-lg-4"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e($reports['weekly'] ?? 0) ?></div><div class="text-muted">Weekly Leads</div></div></div></div>
    <div class="col-lg-4"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e($reports['monthly'] ?? 0) ?></div><div class="text-muted">Monthly Leads</div></div></div></div>
    <div class="col-lg-3"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e($reports['labour_present'] ?? 0) ?></div><div class="text-muted">Labour Present Today</div></div></div></div>
    <div class="col-lg-3"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e(format_currency($reports['labour_cost'] ?? 0)) ?></div><div class="text-muted">Labour Cost Today</div></div></div></div>
    <div class="col-lg-3"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e($reports['work_completed'] ?? 0) ?></div><div class="text-muted">Completed Work Today</div></div></div></div>
    <div class="col-lg-3"><div class="card report-card border-0 shadow-sm"><div class="card-body"><div class="report-kpi"><?= e($reports['work_delayed'] ?? 0) ?></div><div class="text-muted">Delayed Activities</div></div></div></div>
</div>
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body d-flex flex-wrap gap-2">
        <a class="btn btn-accent" href="<?= e(app_url('/reports/export/pdf')) ?>">Export PDF</a>
        <a class="btn btn-outline-primary" href="<?= e(app_url('/reports/export/excel')) ?>">Export Excel</a>
    </div>
</div>