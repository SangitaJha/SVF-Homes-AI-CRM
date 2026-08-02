<?php $pageTitle = $meta['label'] . ' Details'; ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (!$record): ?>
            <div class="alert alert-warning mb-0">Record not found.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($record as $key => $value): ?>
                    <div class="col-md-6">
                        <div class="detail-card p-3 rounded-3">
                            <div class="text-uppercase small text-muted"><?= e(ucwords(str_replace('_', ' ', $key))) ?></div>
                            <div class="fw-semibold"><?= e($value ?? '-') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>