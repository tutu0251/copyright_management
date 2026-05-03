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

<p class="page-intro"><?= esc(lang('App.works_intro')) ?></p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <?= form_open(site_url('works'), ['method' => 'get', 'class' => 'toolbar__grow', 'style' => 'display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;']) ?>
            <input class="input toolbar__search" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" placeholder="<?= esc(lang('App.works_search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('App.works_search_aria'), 'attr') ?>">
            <button type="submit" class="btn btn--secondary btn--sm"><?= esc(lang('App.action_search')) ?></button>
            <?php if ($searchQuery !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('works') ?>"><?= esc(lang('App.action_clear')) ?></a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('works.create')) : ?>
            <a class="btn btn--primary" href="<?= site_url('works/create') ?>"><?= esc(lang('App.action_register_work')) ?></a>
        <?php endif; ?>
        <button type="button" class="btn btn--secondary" disabled title="<?= esc(lang('App.action_export_csv_disabled'), 'attr') ?>"><?= esc(lang('App.action_export_csv')) ?></button>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th><?= esc(lang('App.works_col_id')) ?></th>
            <th><?= esc(lang('App.works_col_title')) ?></th>
            <th><?= esc(lang('App.works_col_type')) ?></th>
            <th><?= esc(lang('App.works_col_creator')) ?></th>
            <th><?= esc(lang('App.works_col_owner')) ?></th>
            <th><?= esc(lang('App.works_col_registered')) ?></th>
            <th><?= esc(lang('App.works_col_status')) ?></th>
            <th><?= esc(lang('App.works_col_licenses')) ?></th>
            <th><?= esc(lang('App.works_col_risk')) ?></th>
            <th><?= esc(lang('App.works_col_updated')) ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (($works ?? []) === []) : ?>
            <tr>
                <td colspan="11" class="muted"><?= esc(lang('App.works_empty')) ?></td>
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
                        <a class="btn btn--ghost btn--sm" href="<?= site_url('works/' . $w['work_id']) ?>"><?= esc(lang('App.action_view')) ?></a>
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
<div class="pager" role="navigation" aria-label="<?= esc(lang('App.works_pagination_aria'), 'attr') ?>">
    <span class="pager__meta"><?= esc(lang('App.works_page', ['page' => (string) $pg, 'pages' => (string) $totalPages, 'count' => (string) $total])) ?></span>
    <div class="pager__buttons">
        <?php if ($pg > 1) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= esc($pageHref($pg - 1), 'url') ?>"><?= esc(lang('App.table_pagination_prev')) ?></a>
        <?php else : ?>
            <button type="button" class="btn btn--secondary btn--sm" disabled><?= esc(lang('App.table_pagination_prev')) ?></button>
        <?php endif; ?>
        <?php if ($pg < $totalPages) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= esc($pageHref($pg + 1), 'url') ?>"><?= esc(lang('App.table_pagination_next')) ?></a>
        <?php else : ?>
            <button type="button" class="btn btn--secondary btn--sm" disabled><?= esc(lang('App.table_pagination_next')) ?></button>
        <?php endif; ?>
    </div>
</div>
