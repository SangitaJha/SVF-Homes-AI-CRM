<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_auth();

$pageTitle = 'AI Assistant';
$authPage = false;
$db = \App\Core\Database::connection();
$result = '';

if (is_post()) {
    verify_csrf();
    $tool = (string)($_POST['tool'] ?? 'chat');

    if ($tool === 'chat') {
        $prompt = strtolower(trim((string)($_POST['prompt'] ?? '')));
        if (str_contains($prompt, 'follow')) {
            $result = 'Show todays follow-ups from the dashboard and follow-ups module.';
        } elseif (str_contains($prompt, 'pending')) {
            $result = 'Pending payments are available in the payments module.';
        } elseif (str_contains($prompt, 'hot')) {
            $result = 'Hot leads are leads with Interested or Negotiation status and higher AI scores.';
        } elseif (str_contains($prompt, 'booking')) {
            $result = 'Todays bookings are tracked in the bookings module.';
        } else {
            $result = 'Ask about follow-ups, pending payments, hot leads, bookings, or available flats.';
        }
    }

    if ($tool === 'score') {
        $budget = (float)($_POST['budget'] ?? 0);
        $siteVisit = !empty($_POST['site_visit']);
        $followupsCount = (int)($_POST['followups'] ?? 0);
        $interest = (int)($_POST['interest'] ?? 0);
        $score = 20 + ($budget >= 5000000 ? 35 : ($budget >= 2500000 ? 20 : 10)) + ($siteVisit ? 20 : 0) + ($followupsCount >= 2 ? 10 : 0) + ($interest >= 7 ? 15 : ($interest >= 4 ? 8 : 0));
        $score = min(100, $score);
        $label = $score >= 75 ? 'Hot' : ($score >= 45 ? 'Warm' : 'Cold');
        $result = 'AI Lead Score: ' . $score . ' (' . $label . ')';
    }

    if ($tool === 'whatsapp') {
        $name = trim((string)($_POST['name'] ?? 'Customer'));
        $project = trim((string)($_POST['project'] ?? 'our project'));
        $result = "Hi {$name}, thank you for your interest in {$project}. We wanted to share a quick update and help you with the next step.";
    }

    if ($tool === 'email') {
        $name = trim((string)($_POST['name'] ?? 'Customer'));
        $project = trim((string)($_POST['project'] ?? 'our project'));
        $result = "Dear {$name},\n\nThank you for your interest in {$project}. Our team has prepared the next step for you.\n\nRegards,\nSVF Homes Team";
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="crm-main flex-grow-1">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card crm-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3">AI Chat Assistant</h3>
                        <form method="post" class="mb-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tool" value="chat">
                            <textarea name="prompt" class="form-control mb-3" rows="5" placeholder="Show today's follow-ups"></textarea>
                            <button class="btn btn-accent" type="submit">Ask AI</button>
                        </form>
                        <div class="alert alert-info mb-0"><?= e($result ?: 'Ask the assistant for CRM insights.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card crm-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Lead Scoring</h3>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tool" value="score">
                            <div class="col-md-6"><input class="form-control" name="budget" placeholder="Budget"></div>
                            <div class="col-md-6"><input class="form-control" name="followups" placeholder="Follow-ups"></div>
                            <div class="col-md-6"><input class="form-control" name="interest" placeholder="Interest (1-10)"></div>
                            <div class="col-md-6"><input class="form-control" name="site_visit" value="1" placeholder="Site visit scheduled"></div>
                            <div class="col-12"><button class="btn btn-accent" type="submit">Score Lead</button></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card crm-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-3">WhatsApp Generator</h3>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tool" value="whatsapp">
                            <div class="col-12"><input class="form-control" name="name" placeholder="Customer Name"></div>
                            <div class="col-12"><input class="form-control" name="project" placeholder="Project Name"></div>
                            <div class="col-12"><button class="btn btn-outline-light" type="submit">Generate</button></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card crm-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-3">Email Generator</h3>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tool" value="email">
                            <div class="col-12"><input class="form-control" name="name" placeholder="Customer Name"></div>
                            <div class="col-12"><input class="form-control" name="project" placeholder="Project Name"></div>
                            <div class="col-12"><button class="btn btn-outline-light" type="submit">Generate</button></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card crm-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h3 class="h6 mb-3">AI Insights</h3>
                        <div class="text-white-50 small mb-2">Live CRM Signals</div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">Hot leads: <?= e((string)$db->query("SELECT COUNT(*) FROM leads WHERE ai_score >= 75")->fetchColumn()) ?></li>
                            <li class="mb-2">Available flats: <?= e((string)$db->query("SELECT COUNT(*) FROM flats WHERE availability='Available'")->fetchColumn()) ?></li>
                            <li class="mb-2">Pending payments: <?= e((string)$db->query("SELECT COUNT(*) FROM payments WHERE status IN ('Pending','Overdue')")->fetchColumn()) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($result): ?>
            <div class="card crm-card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h3 class="h5">Result</h3>
                    <pre class="mb-0 text-white-50"><?= e($result) ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>