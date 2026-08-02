<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_auth();

$pageTitle = 'Settings';
$authPage = false;
$settingsFile = storage_path('settings.json');
$settings = storage_json_read($settingsFile, [
    'company_name' => (string)config('app.name', 'SVF Homes AI CRM'),
    'support_email' => 'support@svf.com',
    'whatsapp_number' => '+91 90000 00000',
    'timezone' => (string)config('app.timezone', 'Asia/Kolkata'),
]);

if (is_post()) {
    verify_csrf();
    $settings = [
        'company_name' => trim((string)($_POST['company_name'] ?? 'SVF Homes AI CRM')),
        'support_email' => trim((string)($_POST['support_email'] ?? 'support@svf.com')),
        'whatsapp_number' => trim((string)($_POST['whatsapp_number'] ?? '')),
        'timezone' => trim((string)($_POST['timezone'] ?? 'Asia/Kolkata')),
    ];
    storage_json_write($settingsFile, $settings);
    flash('success', 'Settings saved successfully.');
    redirect('settings');
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="crm-main flex-grow-1">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <div class="container-fluid p-4">
        <?php if ($message = flash('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= e($message) ?></div><?php endif; ?>
        <div class="card crm-card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h5 mb-4">Application Settings</h3>
                <form method="post" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Support Email</label>
                        <input type="email" name="support_email" class="form-control" value="<?= e($settings['support_email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="<?= e($settings['whatsapp_number']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Timezone</label>
                        <input type="text" name="timezone" class="form-control" value="<?= e($settings['timezone']) ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-accent" type="submit">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>