<?php $pageTitle = $meta['label']; $stats = $stats ?? []; $filters = $filters ?? []; $sortBy = $sortBy ?? 'id'; $sortDir = $sortDir ?? 'desc'; $page = max(1, (int)($page ?? 1)); $perPage = max(1, (int)($perPage ?? 20)); $totalPages = max(1, (int)($totalPages ?? 1)); $totalRecords = (int)($totalRecords ?? 0); ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
            <form class="d-flex flex-wrap gap-2" method="get">
                <input type="search" name="q" value="<?= e($query) ?>" class="form-control form-control-sm" placeholder="Search <?= e($meta['label']) ?>">
                <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(app_url($meta['route'])) ?>">Reset</a>
            </form>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-accent" href="<?= e(app_url($meta['route'] . '/create')) ?>">Add <?= e($meta['label']) ?></a>
                <a class="btn btn-sm btn-outline-primary" href="<?= e(app_url($meta['route'] . '/export/excel')) ?>">Export Excel</a>
                <a class="btn btn-sm btn-outline-info" href="<?= e(app_url($meta['route'] . '/export/csv')) ?>">Export CSV</a>
                <a class="btn btn-sm btn-outline-dark" href="<?= e(app_url($meta['route'] . '/export/pdf')) ?>">Export PDF</a>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="window.print()">Print</button>
            </div>
        </div>

        <?php if (!empty($meta['filters'])): ?>
            <form class="row g-2 align-items-end" method="get">
                <?php foreach ($meta['filters'] as $filter): ?>
                    <?php $filterValue = $filters[$filter['name']] ?? ''; ?>
                    <div class="col-md-2">
                        <label class="form-label small text-muted"><?= e($filter['label']) ?></label>
                        <?php if (($filter['type'] ?? 'text') === 'select'): ?>
                            <select name="<?= e($filter['name']) ?>" class="form-select form-select-sm">
                                <option value="">All</option>
                                <?php foreach (($filter['options'] ?? []) as $option): ?>
                                    <option value="<?= e($option) ?>" <?= (string)$filterValue === (string)$option ? 'selected' : '' ?>><?= e($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (($filter['type'] ?? 'text') === 'date'): ?>
                            <input type="date" name="<?= e($filter['name']) ?>" value="<?= e($filterValue) ?>" class="form-control form-control-sm">
                        <?php else: ?>
                            <input type="text" name="<?= e($filter['name']) ?>" value="<?= e($filterValue) ?>" class="form-control form-control-sm">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-accent w-100" type="submit">Apply Filters</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if (!empty($stats)): ?>
            <div class="row g-3 mt-3">
                <?php foreach ($stats as $stat): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="rounded-3 border p-3 bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small"><?= e($stat['label']) ?></div>
                                    <div class="fw-bold fs-4"><?= e($stat['value']) ?></div>
                                </div>
                                <div class="text-primary fs-4"><i class="fa-solid <?= e($stat['icon'] ?? 'fa-chart-simple') ?>"></i></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= e(app_url($meta['route'] . '/bulk-delete')) ?>" class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <?= csrf_field() ?>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete selected records?');">Bulk Delete</button>
                <select name="bulk_field" class="form-select form-select-sm" style="width:auto;">
                    <option value="status">Status</option>
                    <option value="assigned_to">Assigned To</option>
                </select>
                <input type="text" name="bulk_value" class="form-control form-control-sm" style="width:140px;" placeholder="Value">
                <button class="btn btn-sm btn-outline-primary" type="submit" formaction="<?= e(app_url($meta['route'] . '/bulk-update')) ?>">Bulk Update</button>
            </div>
            <div class="text-muted small">Showing <?= e($totalRecords) ?> records • Page <?= e($page) ?> of <?= e($totalPages) ?></div>
        </form>

        <form method="post" action="<?= e(app_url($meta['route'] . '/import')) ?>" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <?= csrf_field() ?>
            <input type="file" name="import_file" class="form-control form-control-sm" style="max-width:260px;">
            <button class="btn btn-sm btn-outline-success" type="submit">Import Excel</button>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <?php foreach ($meta['columns'] as $column): ?>
                            <th>
                                <a class="text-decoration-none" href="<?= e(app_url($meta['route'] . '?q=' . urlencode($query) . '&sort=' . urlencode($column) . '&dir=' . ($sortBy === $column && $sortDir === 'asc' ? 'desc' : 'asc'))) ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $column))) ?>
                                </a>
                            </th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?= e($record['id'] ?? '') ?>"></td>
                        <td><?= e($record['id'] ?? '') ?></td>
                        <?php foreach ($meta['columns'] as $column): ?>
                            <td><?= e($record[$column] ?? '-') ?></td>
                        <?php endforeach; ?>
                        <td class="d-flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(app_url($meta['route'] . '/' . $record['id'])) ?>">View</a>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(app_url($meta['route'] . '/' . $record['id'] . '/edit')) ?>">Edit</a>
                            <form method="post" action="<?= e(app_url($meta['route'] . '/' . $record['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this record?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-3" aria-label="Pagination">
                <ul class="pagination pagination-sm">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= e(app_url($meta['route'] . '?q=' . urlencode($query) . '&page=' . $i . '&per_page=' . $perPage)) ?>"><?= e($i) ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById('select-all')?.addEventListener('change', function () {
        document.querySelectorAll('input[name="ids[]"]').forEach(function (checkbox) {
            checkbox.checked = document.getElementById('select-all').checked;
        });
    });
</script>