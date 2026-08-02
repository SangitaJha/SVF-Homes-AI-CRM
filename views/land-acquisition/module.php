<?php

declare(strict_types=1);

$pageTitle = $module['label'] ?? 'Land Acquisition';
$baseUrl = app_url('/land-acquisition/index.php?section=' . $section);
$moduleName = $module['label'] ?? 'Land Acquisition';
$moduleDescription = $module['description'] ?? 'Manage the land acquisition lifecycle in one place.';
$showForm = (bool)($module['showForm'] ?? false);
$records = $records ?? [];
?>
<div class="page-hero card mb-4">
    <div class="card-body p-4 p-xl-5 d-flex flex-column flex-xl-row align-items-start justify-content-between gap-3">
        <div>
            <div class="eyebrow"><i class="bi bi-map"></i> Land Acquisition</div>
            <h2 class="h3 mb-2"><?= e($moduleName) ?></h2>
            <p class="text-muted mb-0"><?= e($moduleDescription) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="<?= e(app_url('/land-acquisition/index.php')) ?>">Dashboard</a>
            <a class="btn btn-accent" href="<?= e($baseUrl) ?>">Refresh</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($summaryCards as $card): ?>
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

<?php if ($section === 'leads' && $pipelineStages): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($pipelineStages as $stage): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 mb-0"><?= e($stage['status']) ?></h3>
                            <span class="badge text-bg-light"><?= e((string)$stage['count']) ?></span>
                        </div>
                        <?php foreach ($stage['items'] as $item): ?>
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-semibold"><?= e($item['owner_name'] ?? ($item['lead_name'] ?? 'Record')) ?></div>
                                <small class="text-muted"><?= e($item['property_location'] ?? ($item['mobile'] ?? '')) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if ($showForm): ?>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="h6 mb-1"><?= $editRecord ? 'Edit record' : 'Add new record' ?></h3>
                            <p class="text-muted mb-0">Capture the latest information for this stage.</p>
                        </div>
                    </div>
                    <form method="post" action="<?= e($baseUrl) ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="edit_id" value="<?= e((string)($editRecord['id'] ?? '')) ?>">
                        <?php foreach ($module['fields'] as $field): ?>
                            <?php $fieldName = (string)$field['name']; ?>
                            <?php $fieldValue = $editRecord[$fieldName] ?? old($fieldName, ''); ?>
                            <div class="mb-3">
                                <label class="form-label" for="field-<?= e($fieldName) ?>"><?= e($field['label']) ?></label>
                                <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                                    <textarea class="form-control" id="field-<?= e($fieldName) ?>" name="<?= e($fieldName) ?>"><?= e((string)$fieldValue) ?></textarea>
                                <?php elseif (($field['type'] ?? 'text') === 'select'): ?>
                                    <select class="form-select" id="field-<?= e($fieldName) ?>" name="<?= e($fieldName) ?>">
                                        <?php foreach ($field['options'] ?? [] as $option): ?>
                                            <option value="<?= e($option) ?>" <?= ((string)$fieldValue === (string)$option) ? 'selected' : '' ?>><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif (($field['type'] ?? 'text') === 'file'): ?>
                                    <?php if ($editRecord): ?>
                                        <input type="hidden" name="existing_<?= e($fieldName) ?>" value="<?= e((string)$fieldValue) ?>">
                                    <?php endif; ?>
                                    <input class="form-control" id="field-<?= e($fieldName) ?>" type="file" name="<?= e($fieldName) ?>">
                                    <?php if (!empty($fieldValue)): ?>
                                        <small class="text-muted d-block mt-2"><a href="<?= e(app_url('/' . ltrim((string)$fieldValue, '/'))) ?>" target="_blank">View uploaded file</a></small>
                                    <?php endif; ?>
                                <?php elseif (($field['type'] ?? 'text') === 'date'): ?>
                                    <input class="form-control" id="field-<?= e($fieldName) ?>" type="date" name="<?= e($fieldName) ?>" value="<?= e((string)$fieldValue) ?>">
                                <?php elseif (($field['type'] ?? 'text') === 'number'): ?>
                                    <input class="form-control" id="field-<?= e($fieldName) ?>" type="number" name="<?= e($fieldName) ?>" value="<?= e((string)$fieldValue) ?>">
                                <?php elseif (($field['type'] ?? 'text') === 'email'): ?>
                                    <input class="form-control" id="field-<?= e($fieldName) ?>" type="email" name="<?= e($fieldName) ?>" value="<?= e((string)$fieldValue) ?>">
                                <?php else: ?>
                                    <input class="form-control" id="field-<?= e($fieldName) ?>" type="text" name="<?= e($fieldName) ?>" value="<?= e((string)$fieldValue) ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <button class="btn btn-accent w-100" type="submit">Save record</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-xl-<?= $showForm ? '8' : '12' ?>">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                    <div>
                        <h3 class="h6 mb-1">Current records</h3>
                        <p class="text-muted mb-0">Search, filter, review, and manage the active land acquisition pipeline.</p>
                    </div>
                    <form class="d-flex gap-2" method="get" action="<?= e($baseUrl) ?>">
                        <input class="form-control" type="text" name="q" placeholder="Search records" value="<?= e((string)($_GET['q'] ?? '')) ?>">
                        <button class="btn btn-outline-primary" type="submit">Search</button>
                    </form>
                </div>

                <?php if ($section === 'reports'): ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">Land Acquisition Summary</div>
                                <small class="text-muted">Executive briefing with key metrics and next actions.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">ROI Analysis</div>
                                <small class="text-muted">Downloadable export with profitability and break-even details.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">Payment Report</div>
                                <small class="text-muted">Due-date and payout status dashboard for accounts.</small>
                            </div>
                        </div>
                    </div>
                <?php elseif ($section === 'ai-assistant'): ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">AI Land Finder</div>
                                <small class="text-muted">High-demand zones suggested from CRM sales and historical demand patterns.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">AI Risk Detection</div>
                                <small class="text-muted">Alerts for legal, ownership, and market-based risks before final approval.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">AI Profit Calculator</div>
                                <small class="text-muted">Estimate revenue, expenses, profit, and ROI for every opportunity.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold">Board Report Generator</div>
                                <small class="text-muted">Draft executive summary, investment proposal, and director presentation notes.</small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Primary field</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= e((string)($record['id'] ?? '')) ?></td>
                                        <td>
                                            <?php $displayValue = $record[$module['columns'][0] ?? 'id'] ?? $record['owner_name'] ?? $record['lead_name'] ?? $record['requirement_name'] ?? ''; ?>
                                            <?= e((string)$displayValue) ?>
                                        </td>
                                        <td>
                                            <?php $secondary = $record[$module['columns'][1] ?? ''] ?? ''; ?>
                                            <?= e((string)$secondary) ?>
                                        </td>
                                        <td>
                                            <?php $statusValue = $record['status'] ?? $record['verification_status'] ?? $record['negotiation_status'] ?? $record['payment_status'] ?? $record['approval_status'] ?? ''; ?>
                                            <span class="badge text-bg-light"><?= e((string)$statusValue) ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a class="btn btn-sm btn-outline-primary" href="<?= e($baseUrl . '&edit=' . (int)($record['id'] ?? 0)) ?>">Edit</a>
                                                <form method="post" action="<?= e($baseUrl . '&delete=' . (int)($record['id'] ?? 0)) ?>" onsubmit="return confirm('Remove this record?');">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
