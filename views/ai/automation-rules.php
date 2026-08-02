<?php

declare(strict_types=1);

$rules = $rules ?? [];
?>
<div class="page-hero card mb-4">
    <div class="card-body p-4 p-xl-5">
        <div class="eyebrow"><i class="bi bi-diagram-3"></i> Automation Builder</div>
        <h2 class="h3 mb-2">No-code Workflow Rules</h2>
        <p class="text-muted mb-0">Define AI-driven business automations for lead handling, booking confirmation, payment processing, and project launches.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">Configured Rules</h3>
            <button class="btn btn-accent" type="button">New Rule</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Rule</th>
                        <th>Trigger</th>
                        <th>Actions</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td><strong><?= e($rule['name']) ?></strong></td>
                            <td><?= e($rule['trigger']) ?></td>
                            <td><?= e(implode(' • ', (array)($rule['actions'] ?? []))) ?></td>
                            <td><span class="badge text-bg-success">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
