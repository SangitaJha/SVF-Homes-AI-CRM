<?php $pageTitle = 'Notifications'; ?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h5">Recent Notifications</h3>
                <div class="list-group list-group-flush">
                    <?php foreach ($records as $record): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-semibold"><?= e($record['title'] ?? '') ?></div>
                                    <small class="text-muted"><?= e($record['message'] ?? '') ?></small>
                                </div>
                                <span class="badge text-bg-secondary"><?= e($record['status'] ?? '') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h5">Send Reminder</h3>
                <form method="post" action="<?= e(app_url('/notifications/send')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Channel</label><select name="channel" class="form-select"><option>WhatsApp</option><option>Email</option><option>SMS</option><option>In-App</option></select></div>
                    <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" placeholder="Payment Reminder"></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="5"></textarea></div>
                    <button class="btn btn-accent" type="submit">Queue Notification</button>
                </form>
            </div>
        </div>
    </div>
</div>