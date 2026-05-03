<?php
$rows = $rows ?? [];
$searchQuery = $searchQuery ?? '';
$usageType = $usageType ?? '';
$migrationRequired = $migrationRequired ?? false;
$msg = session()->getFlashdata('message');
$errs = session()->getFlashdata('errors');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>
<?php if (is_array($errs) && $errs !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <h2 class="card__title">Action required</h2>
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($errs as $e) : ?>
                <li><?= esc(is_array($e) ? json_encode($e) : (string) $e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if ($migrationRequired) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-warning, #a73);">
        <h2 class="card__title">Database migration needed</h2>
        <p class="muted" style="margin:0;">The <code>usage_reports</code> table is still on the legacy schema or missing monitoring columns. From the project root run:</p>
        <pre style="margin:0.75rem 0 0;padding:0.75rem;overflow:auto;background:rgba(0,0,0,0.2);border-radius:6px;"><code>php spark migrate</code></pre>
        <p class="muted" style="margin:0.75rem 0 0;">This applies <code>2026-05-03-180000_RenameLicenseUsageSnapshotsAndCreateMonitoringUsageReports</code> (license rows move to <code>license_usage_snapshots</code>).</p>
    </div>
<?php endif; ?>

<p class="page-intro">Manual detections of catalog works appearing online or elsewhere.</p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <?= form_open(site_url('usage-reports'), ['method' => 'get', 'class' => 'toolbar__grow', 'style' => 'display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;']) ?>
            <input type="hidden" name="usage_type" value="<?= esc($usageType, 'attr') ?>">
            <input class="input toolbar__search" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" placeholder="Search work, source, notes…" aria-label="Search usage reports">
            <button type="submit" class="btn btn--secondary btn--sm">Search</button>
            <?php if ($searchQuery !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports' . ($usageType !== '' ? '?usage_type=' . rawurlencode($usageType) : '')) ?>">Clear text</a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--primary" href="<?= site_url('usage-reports/create') ?>">Report usage</a>
    </div>
</div>

<p class="muted" style="margin: -0.25rem 0 1rem;">Filter by status:
    <a href="<?= site_url('usage-reports') ?>">All</a>
    · <a href="<?= site_url('usage-reports?usage_type=suspected') ?>">Suspected</a>
    · <a href="<?= site_url('usage-reports?usage_type=infringement') ?>">Infringement</a>
    · <a href="<?= site_url('usage-reports?usage_type=authorized') ?>">Authorized</a>
</p>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Work</th>
            <th>Source</th>
            <th>Channel type</th>
            <th>Usage</th>
            <th>Detected</th>
            <th>Method</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($rows === []) : ?>
            <tr>
                <td colspan="7" class="muted">No usage reports yet.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($rows as $r) : ?>
                <?php $id = (int) ($r['id'] ?? 0); ?>
                <tr>
                    <td>
                        <?php if ((int) ($r['work_id'] ?? 0) > 0) : ?>
                            <a href="<?= site_url('works/' . (int) $r['work_id']) ?>"><?= esc((string) ($r['work_title'] ?? '')) ?></a>
                        <?php else : ?>
                            <?= esc((string) ($r['work_title'] ?? '')) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= esc((string) ($r['detected_source'] ?? '')) ?></td>
                    <td><?= esc((string) ($r['detected_type'] ?? '')) ?></td>
                    <td>
                        <?= view('components/badges', ['label' => (string) ($r['usage_type_label'] ?? ''), 'tone' => (string) ($r['usage_tone'] ?? 'neutral')]) ?>
                    </td>
                    <td><?= esc((string) ($r['detected_at'] ?? '')) ?></td>
                    <td><?= esc((string) ($r['detection_method'] ?? '')) ?></td>
                    <td class="table-actions">
                        <?php if ($id > 0) : ?>
                            <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports/' . $id) ?>">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>
