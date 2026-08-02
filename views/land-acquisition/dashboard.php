<?php

declare(strict_types=1);

$pageTitle = 'Land Acquisition Dashboard';
$moduleLinks = [
    ['key' => 'requirements', 'label' => 'Land Requirements', 'icon' => 'fa-map-location-dot'],
    ['key' => 'leads', 'label' => 'Land Leads', 'icon' => 'fa-bullhorn'],
    ['key' => 'owners', 'label' => 'Owner Database', 'icon' => 'fa-user-group'],
    ['key' => 'site_visits', 'label' => 'Site Visits', 'icon' => 'fa-location-dot'],
    ['key' => 'document_verification', 'label' => 'Document Verification', 'icon' => 'fa-file-contract'],
    ['key' => 'land_evaluation', 'label' => 'Land Evaluation', 'icon' => 'fa-chart-line'],
    ['key' => 'negotiation', 'label' => 'Negotiation', 'icon' => 'fa-handshake'],
    ['key' => 'agreements', 'label' => 'JDA / Sale Agreement', 'icon' => 'fa-file-signature'],
    ['key' => 'approvals', 'label' => 'Approval Workflow', 'icon' => 'fa-check-to-slot'],
    ['key' => 'payments', 'label' => 'Payment Tracking', 'icon' => 'fa-wallet'],
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'fa-chart-column'],
    ['key' => 'ai-assistant', 'label' => 'AI Land Assistant', 'icon' => 'fa-robot'],
];
$monthlyTrendLabels = array_map(static fn(array $item): string => (string)($item['month'] ?? ''), $monthlyTrend);
$monthlyTrendValues = array_map(static fn(array $item): int => (int)($item['value'] ?? 0), $monthlyTrend);
$investmentTrendLabels = array_map(static fn(array $item): string => (string)($item['month'] ?? ''), $investmentTrend);
$investmentTrendValues = array_map(static fn(array $item): float => (float)($item['value'] ?? 0), $investmentTrend);
$locationDistributionLabels = array_map(static fn(array $item): string => (string)($item['label'] ?? ''), $locationDistribution);
$locationDistributionValues = array_map(static fn(array $item): int => (int)($item['value'] ?? 0), $locationDistribution);
?>
<div class="page-hero card mb-4">
    <div class="card-body p-4 p-xl-5 d-flex flex-column flex-xl-row align-items-start justify-content-between gap-3">
        <div>
            <div class="eyebrow"><i class="bi bi-map"></i> SVF AI CRM • Land Acquisition</div>
            <h2 class="h3 mb-2">End-to-end land acquisition control from requirement to project conversion</h2>
            <p class="text-muted mb-0">The module now covers requirement capture, lead management, owner records, legal verification, negotiation, approvals, payments, reporting, and AI recommendations in one responsive workspace.</p>
        </div>
        <a class="btn btn-accent" href="<?= e(app_url('/land-acquisition/index.php?section=requirements')) ?>">Create Requirement</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($metrics as $metric): ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label"><?= e($metric['label']) ?></div>
                        <div class="metric-value"><?= e((string)$metric['value']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid <?= e($metric['icon']) ?>"></i></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h6 mb-1">Monthly land acquisition trend</h3>
                        <p class="text-muted mb-0">Pipeline momentum across the last six months</p>
                    </div>
                </div>
                <canvas id="landAcquisitionTrend" height="150" data-chart-labels='<?= e(json_encode($monthlyTrendLabels)) ?>' data-chart-values='<?= e(json_encode($monthlyTrendValues)) ?>'></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h6 mb-1">Investment trend</h3>
                        <p class="text-muted mb-0">Capital deployment for active acquisitions</p>
                    </div>
                </div>
                <canvas id="landInvestmentTrend" height="150" data-chart-labels='<?= e(json_encode($investmentTrendLabels)) ?>' data-chart-values='<?= e(json_encode($investmentTrendValues)) ?>'></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Process modules</h3>
                <div class="row g-3">
                    <?php foreach ($moduleLinks as $moduleLink): ?>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <a class="module-link-card text-decoration-none h-100" href="<?= e(app_url('/land-acquisition/index.php?section=' . $moduleLink['key'])) ?>">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid <?= e($moduleLink['icon']) ?>"></i>
                                    <span class="fw-semibold"><?= e($moduleLink['label']) ?></span>
                                </div>
                                <small class="text-muted">Open workflow</small>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Location distribution</h3>
                <canvas id="locationDistributionChart" height="220" data-chart-labels='<?= e(json_encode($locationDistributionLabels)) ?>' data-chart-values='<?= e(json_encode($locationDistributionValues)) ?>'></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3 class="h6 mb-3">Latest land acquisition activity</h3>
        <div class="timeline">
            <?php foreach ($activityFeed as $item): ?>
                <div class="timeline-item">
                    <div class="fw-semibold"><?= e($item['title']) ?></div>
                    <small class="text-muted"><?= e($item['detail']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
