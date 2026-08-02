<?php
$monthlyLeadLabels = array_map(static fn($row) => $row['month'] ?? '', array_reverse($monthlyLeads));
$monthlyLeadValues = array_map(static fn($row) => (int)($row['total'] ?? 0), array_reverse($monthlyLeads));
$monthlyRevenueLabels = array_map(static fn($row) => $row['month'] ?? '', array_reverse($monthlyRevenue));
$monthlyRevenueValues = array_map(static fn($row) => (float)($row['total'] ?? 0), array_reverse($monthlyRevenue));
?>
<div class="page-hero card mb-4">
    <div class="card-body p-4 p-xl-5 d-flex flex-column flex-xl-row align-items-start justify-content-between gap-3">
        <div>
            <div class="eyebrow"><i class="bi bi-graph-up-arrow"></i> Executive dashboard</div>
            <h2 class="h3 mb-2">Real-time visibility across sales, delivery, and operations</h2>
            <p class="text-muted mb-0">Monitor every lead, booking, payment, and site milestone in a premium workspace designed for high-growth real estate teams.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-accent" href="<?= e(app_url('/leads')) ?>">Open Leads</a>
            <a class="btn btn-outline-primary" href="<?= e(app_url('/ai')) ?>">AI Assistant</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'Total Leads', 'value' => $metrics['totalLeads'], 'icon' => 'fa-bullhorn'],
        ['label' => "Today's Leads", 'value' => $metrics['todaysLeads'], 'icon' => 'fa-calendar-day'],
        ['label' => 'Hot Leads', 'value' => $metrics['hotLeads'], 'icon' => 'fa-fire'],
        ['label' => 'Customers', 'value' => $metrics['customers'], 'icon' => 'fa-users'],
        ['label' => 'Projects', 'value' => $metrics['projects'], 'icon' => 'fa-city'],
        ['label' => 'Bookings', 'value' => $metrics['bookings'], 'icon' => 'fa-calendar-check'],
        ['label' => 'Revenue', 'value' => format_currency($metrics['revenue']), 'icon' => 'fa-indian-rupee-sign'],
        ['label' => 'Pending Payments', 'value' => $metrics['pendingPayments'], 'icon' => 'fa-triangle-exclamation'],
        ['label' => 'Available Flats', 'value' => $metrics['availableFlats'], 'icon' => 'fa-building'],
        ['label' => 'Land Leads', 'value' => $metrics['landLeads'], 'icon' => 'fa-map-location-dot'],
        ['label' => 'Negotiations', 'value' => $metrics['negotiations'], 'icon' => 'fa-handshake'],
    ] as $card): ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label"><?= e($card['label']) ?></div>
                        <div class="metric-value"><?= e($card['value']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid <?= e($card['icon']) ?>"></i></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-1">Construction operations</h2>
        <p class="text-muted mb-0">Labour attendance and site progress for today.</p>
    </div>
    <a class="btn btn-sm btn-outline-primary" href="<?= e(app_url('/labour')) ?>">Open Labour Dashboard</a>
</div>
<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'Labour Today', 'value' => $constructionMetrics['labourToday'], 'icon' => 'fa-people-group'],
        ['label' => 'Labour Present', 'value' => $constructionMetrics['labourPresent'], 'icon' => 'fa-person-circle-check'],
        ['label' => 'Labour Absent', 'value' => $constructionMetrics['labourAbsent'], 'icon' => 'fa-person-circle-xmark'],
        ['label' => 'Labour Cost', 'value' => format_currency($constructionMetrics['labourCost']), 'icon' => 'fa-money-bill-wave'],
        ['label' => 'Completed Work', 'value' => $constructionMetrics['completedWork'], 'icon' => 'fa-clipboard-check'],
        ['label' => 'Delayed Work', 'value' => $constructionMetrics['delayedWork'], 'icon' => 'fa-triangle-exclamation'],
        ['label' => 'Project Progress', 'value' => number_format($constructionMetrics['projectProgress'], 1) . '%', 'icon' => 'fa-chart-line'],
    ] as $card): ?>
        <div class="col-12 col-sm-6 col-xl-3">
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

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h6 mb-1">Monthly leads</h3>
                        <p class="text-muted mb-0">Pipeline performance trend</p>
                    </div>
                </div>
                <div class="dashboard-chart-shell">
                    <canvas id="monthlyLeadsChart"
                        data-chart-labels='<?= e(json_encode($monthlyLeadLabels)) ?>'
                        data-chart-values='<?= e(json_encode($monthlyLeadValues)) ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h6 mb-1">Monthly revenue</h3>
                        <p class="text-muted mb-0">Revenue growth overview</p>
                    </div>
                </div>
                <div class="dashboard-chart-shell">
                    <canvas id="monthlyRevenueChart"
                        data-chart-labels='<?= e(json_encode($monthlyRevenueLabels)) ?>'
                        data-chart-values='<?= e(json_encode($monthlyRevenueValues)) ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Today's follow-ups</h3>
                <div class="list-group list-group-flush">
                    <?php foreach ($followups as $followup): ?>
                        <div class="list-group-item px-0">
                            <div class="fw-semibold"><?= e($followup['assigned_to'] ?? 'Team') ?></div>
                            <small class="text-muted"><?= e(format_datetime($followup['followup_at'] ?? null)) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Recent activities</h3>
                <div class="timeline">
                    <?php foreach ($activities as $activity): ?>
                        <div class="timeline-item">
                            <div class="fw-semibold"><?= e($activity['module'] ?? 'System') ?></div>
                            <small class="text-muted"><?= e($activity['description'] ?? '') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Notifications</h3>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item px-0">
                            <div class="fw-semibold"><?= e($notification['title'] ?? '') ?></div>
                            <small class="text-muted"><?= e($notification['channel'] ?? '') ?> · <?= e($notification['status'] ?? '') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Booking status</h3>
                <div class="dashboard-chart-shell">
                    <canvas id="bookingStatusChart" data-chart-labels='<?= e(json_encode(array_column($bookingStatus, 'status'))) ?>' data-chart-values='<?= e(json_encode(array_column($bookingStatus, 'total'))) ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Payment collection</h3>
                <div class="dashboard-chart-shell">
                    <canvas id="paymentCollectionChart" data-chart-labels='<?= e(json_encode(array_column($paymentCollection, 'status'))) ?>' data-chart-values='<?= e(json_encode(array_column($paymentCollection, 'total'))) ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6"><div class="card h-100"><div class="card-body"><h3 class="h6 mb-3">Labour attendance trend</h3><div class="dashboard-chart-shell"><canvas id="dashboardLabourAttendanceChart" data-chart-labels='<?= e(json_encode(array_column($attendanceTrend, 'date'))) ?>' data-chart-values='<?= e(json_encode(array_map('intval', array_column($attendanceTrend, 'present')))) ?>'></canvas></div></div></div></div>
    <div class="col-lg-6"><div class="card h-100"><div class="card-body"><h3 class="h6 mb-3">Construction progress trend</h3><div class="dashboard-chart-shell"><canvas id="dashboardProgressChart" data-chart-labels='<?= e(json_encode(array_column($progressTrend, 'date'))) ?>' data-chart-values='<?= e(json_encode(array_map('floatval', array_column($progressTrend, 'progress')))) ?>'></canvas></div></div></div></div>
</div>

<div class="card mt-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="h6 mb-1">AI operations insights</h3>
            <p class="text-muted mb-0">Ask for today's attendance, delayed activities, site reports, or tomorrow's labour requirement.</p>
        </div>
        <a class="btn btn-accent" href="<?= e(app_url('/ai')) ?>">Open AI Assistant</a>
    </div>
</div>
