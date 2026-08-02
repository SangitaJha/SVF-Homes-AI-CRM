<?php

declare(strict_types=1);

$pageTitle = 'AI & Automation Center';
$summary = $summary ?? [];
$alerts = $alerts ?? [];
?>
<div class="page-hero card mb-4">
    <div class="card-body p-4 p-xl-5 d-flex flex-column flex-xl-row align-items-start justify-content-between gap-3">
        <div>
            <div class="eyebrow"><i class="bi bi-robot"></i> AI & Automation Center</div>
            <h2 class="h3 mb-2">Executive AI Command Center</h2>
            <p class="text-muted mb-0">Monitor sales, finance, construction, payments, and HR from one intelligent cockpit.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="<?= e(app_url('/ai')) ?>">AI Assistant</a>
            <a class="btn btn-accent" href="<?= e(app_url('/ai/automation-rules')) ?>">Automation Rules</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'New Leads', 'value' => $summary['new_leads'] ?? 0, 'icon' => 'fa-bullhorn'],
        ['label' => 'Hot Leads', 'value' => $summary['hot_leads'] ?? 0, 'icon' => 'fa-fire'],
        ['label' => 'Pending Follow-ups', 'value' => $summary['pending_followups'] ?? 0, 'icon' => 'fa-phone-volume'],
        ['label' => 'Due Payments', 'value' => $summary['due_payments'] ?? 0, 'icon' => 'fa-wallet'],
        ['label' => 'Upcoming Registrations', 'value' => $summary['upcoming_registrations'] ?? 0, 'icon' => 'fa-file-signature'],
        ['label' => 'Construction Alerts', 'value' => $summary['construction_alerts'] ?? 0, 'icon' => 'fa-hard-hat'],
    ] as $card): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label"><?= e($card['label']) ?></div>
                        <div class="metric-value"><?= e((string)$card['value']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid <?= e($card['icon']) ?>"></i></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h5 mb-3">Today's AI Summary</h3>
                <p class="text-muted mb-0"><?= e($summary['today_summary'] ?? 'The AI engine is monitoring all core business operations.') ?></p>
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="small text-muted">Revenue Prediction</div>
                            <div class="fw-semibold"><?= e(format_currency($summary['revenue_prediction'] ?? 0)) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="small text-muted">Cash Flow Prediction</div>
                            <div class="fw-semibold"><?= e(format_currency($summary['cash_flow_prediction'] ?? 0)) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="small text-muted">Profit Prediction</div>
                            <div class="fw-semibold"><?= e(format_currency($summary['profit_prediction'] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h5 mb-3">AI Alerts</h3>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($alerts as $alert): ?>
                        <li class="border rounded p-3 mb-2">
                            <div class="fw-semibold"><?= e($alert['title']) ?></div>
                            <div class="small text-muted"><?= e($alert['detail']) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
