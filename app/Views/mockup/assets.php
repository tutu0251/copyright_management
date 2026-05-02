<p class="page-intro">Browse the static catalog of registered works. Sorting and filters are visual only; pagination uses query parameters.</p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <input class="input toolbar__search" type="search" placeholder="Search title, creator, owner…" aria-label="Search (mock)">
        <select class="select toolbar__filter" aria-label="Filter by type (mock)">
            <option>All types</option>
            <option>Image</option>
            <option>Audio</option>
            <option>Video</option>
            <option>Text</option>
            <option>Software</option>
            <option>Design</option>
            <option>Course</option>
        </select>
        <select class="select toolbar__filter" aria-label="Filter by status (mock)">
            <option>All statuses</option>
            <option>Registered</option>
            <option>Pending review</option>
            <option>Under audit</option>
        </select>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--primary" href="<?= site_url('mockup/register') ?>">Register work</a>
        <button type="button" class="btn btn--secondary" disabled>Export CSV</button>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th class="sortable"><span class="th-sort">Work ID <span class="sort-ico">↕</span></span></th>
            <th class="sortable"><span class="th-sort">Title <span class="sort-ico">↕</span></span></th>
            <th class="sortable"><span class="th-sort">Type <span class="sort-ico">↕</span></span></th>
            <th>Creator</th>
            <th>Owner</th>
            <th class="sortable"><span class="th-sort">Registered <span class="sort-ico">↕</span></span></th>
            <th>Status</th>
            <th>Licenses</th>
            <th>Risk</th>
            <th>Updated</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
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
                    echo view('components/badges', ['label' => $st, 'tone' => $tone]);
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
                    <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/work/' . $w['work_id']) ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>

<?php
$pager = $pager ?? ['page' => 1, 'totalPages' => 1, 'total' => 0, 'perPage' => 10];
$pg = (int) ($pager['page'] ?? 1);
$totalPages = (int) ($pager['totalPages'] ?? 1);
$total = (int) ($pager['total'] ?? 0);
?>
<div class="pager" role="navigation" aria-label="Pagination (mock)">
    <span class="pager__meta">Page <?= esc((string) $pg) ?> of <?= esc((string) $totalPages) ?> · <?= esc((string) $total) ?> assets</span>
    <div class="pager__buttons">
        <?php if ($pg > 1) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/assets?page=' . ($pg - 1)) ?>">Previous</a>
        <?php else : ?>
            <button type="button" class="btn btn--secondary btn--sm" disabled>Previous</button>
        <?php endif; ?>
        <?php if ($pg < $totalPages) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/assets?page=' . ($pg + 1)) ?>">Next</a>
        <?php else : ?>
            <button type="button" class="btn btn--secondary btn--sm" disabled>Next</button>
        <?php endif; ?>
    </div>
</div>
