<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_auth();

$pageTitle = 'Reports';
$authPage = false;
$db = \App\Core\Database::connection();
$cards = [
    ['label' => 'Daily Leads', 'value' => (int)$db->query('SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()')->fetchColumn()],
    ['label' => 'Weekly Leads', 'value' => (int)$db->query('SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->fetchColumn()],
    ['label' => 'Monthly Leads', 'value' => (int)$db->query('SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetchColumn()],
    ['label' => 'Revenue', 'value' => format_currency((float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid'")->fetchColumn())],
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="crm-main flex-grow-1">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="row g-3 mb-4">
            <?php foreach ($cards as $card): ?>
                <div class="col-md-3">
                    <div class="card crm-card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-white-50 small text-uppercase"><?= e($card['label']) ?></div>
                            <div class="display-6 fw-bold mt-2"><?= e((string)$card['value']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card crm-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div>
                        <h3 class="h5 mb-1">Reports Export</h3>
                        <p class="text-white-50 mb-0">Generate downloadable sales and revenue summaries.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light"><i class="bi bi-filetype-pdf me-1"></i>PDF</a>
                        <a href="#" class="btn btn-outline-light"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Metric</th><th>Value</th></tr></thead>
                        <tbody>
                        <?php foreach ($cards as $card): ?>
                            <tr><td><?= e($card['label']) ?></td><td><?= e((string)$card['value']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>