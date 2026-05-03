<?php
$pager   = $pager ?? ['page' => 1, 'totalPages' => 1, 'total' => 0, 'perPage' => 12];
$pg      = (int) ($pager['page'] ?? 1);
$totalPages = (int) ($pager['totalPages'] ?? 1);
$total   = (int) ($pager['total'] ?? 0);
$searchQuery = $searchQuery ?? '';
$msg = session()->getFlashdata('message');
$warn = session()->getFlashdata('warning');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>
<?php if ($warn) : ?>
    <p class="muted" role="status"><?= esc($warn) ?></p>
<?php endif; ?>

<p class="page-intro">Browse registered works in your catalog. Search filters the list; pagination keeps large libraries manageable.</p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <?= form_open(site_url('works'), ['method' => 'get', 'class' => 'toolbar__grow', 'style' => 'display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;']) ?>
            <input class="input toolbar__search" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" placeholder="Search title, creator, owner…" aria-label="Search works">
            <button type="submit" class="btn btn--secondary btn--sm">Search</button>
            <?php if ($searchQuery !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('works') ?>">Clear</a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--primary" href="<?= site_url('works/create') ?>">Register work</a>
        <button type="button" class="btn btn--secondary" disabled title="Export not wired yet">Export CSV</button>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Work ID</th>
            <th>Title</th>
            <th>Type</th>
            <th>Creator</th>
            <th>Owner</th>
            <th>Registered</th>
            <th>Status</th>
            <th>Licenses</th>
            <th>Risk</th>
            <th>Updated</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (($works ?? []) === []) : ?>
            <tr>
                <td colspan="11" class="muted">No works match your criteria. Register a work or adjust your search.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($works as $w) : ?>
                <tr>
                    <td><?= esc($w['work_id']) ?></td>
                    <td><strong><?= esc($w['title']) ?></strong></td>
                    <td><?= esc($w['type']) ?></td>
                    <td><?= esc($w['creator']) ?></td>
                    <td><?= esc($w['owner']) ?></td>
                    <td><?= esc($w['registration_date']) ?></td>
                    <td>
                        <?php
                        $st = (string) $w['copyright_status'];
                        $tone = preg_match('/pending|audit/i', $st) ? 'warning' : (preg_match('/registered/i', $st) ? 'success' : 'neutral');
                        echo view('components/badges', ['label' => str_replace('_', ' ', $st), 'tone' => $tone]);
                        ?>
                    </td>
                    <td><?= esc((string) $w['license_count']) ?></td>
                    <td>
                        <?php
                        $risk = $w['risk_level'];
                        $rt = $risk === 'High' ? 'danger' : ($risk === 'Medium' ? 'warning' : 'neutral');
                        echo view('components/badges', ['label' => $risk, 'tone' => $rt]);
                        ?>
                    </td>
                    <td><?= esc($w['last_updated']) ?></td>
                    <td class="table-actions">
                        <a class="btn btn--ghost btn--sm" href="<?= site_url('works/' . $w['work_id']) ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>

<?php
$pageHref = function (int $p) use ($searchQuery): string {
    $q = ['page' => $p];
    if ($searchQuery !== '') {
        $q['q'] = $searchQuery;
    }

    return site_url('works?' . http_build_query($q));
};
?>
<div class="pager" role="navigation" aria-label="Pagination">
    <span class="pager__meta">Page <?= esc((string) $pg) ?> of <?= esc((string) $totalPages) ?> · <?= esc((string) $total) ?> assets</span>
    <div class="pager__buttons">
        <?php if ($pg > 1) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= esc($pageHref($pg - 1), 'url') ?>">Previous</a>
        <?php else : ?>
            <button type="button" class="btn btn--secondary btn--sm" disabled>Previous</button>
        <?php endif; ?>
        <?php if ($pg < $totalPages) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= esc($pageHref($pg + 1), 'url') ?>">Next</a>
        <?php else : ?>
            <button type="button" class="btn btn--secondary btn--sm" disabled>Next</button>
        <?php endif; ?>
    </div>
</div>
