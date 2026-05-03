<?php
$rows = $rows ?? [];
$searchQuery = $searchQuery ?? '';
$statusFilter = $statusFilter ?? '';
$priorityFilter = $priorityFilter ?? '';
$migrationRequired = $migrationRequired ?? false;
$msg = session()->getFlashdata('message');
$err = session()->getFlashdata('errors');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>
<?php if (is_array($err) && $err !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($err as $e) : ?>
                <li><?= esc(is_array($e) ? json_encode($e) : (string) $e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($migrationRequired) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-warning, #a73);">
        <h2 class="card__title"><?= esc(lang('App.cases_migration_title')) ?></h2>
        <p class="muted" style="margin:0;"><?= lang('App.cases_migration_body') ?></p>
    </div>
<?php endif; ?>

<p class="page-intro"><?= esc(lang('App.cases_intro')) ?></p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <form class="toolbar__grow" method="get" action="<?= esc(site_url('cases'), 'attr') ?>" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
            <input class="input toolbar__search" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" placeholder="<?= esc(lang('App.cases_search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('App.cases_search_aria'), 'attr') ?>">
            <select class="select" name="case_status" aria-label="<?= esc(lang('App.cases_filter_status_aria'), 'attr') ?>">
                <option value=""><?= esc(lang('App.cases_all_statuses')) ?></option>
                <?php foreach (\App\Models\InfringementCaseModel::ALL_STATUSES as $st) : ?>
                    <option value="<?= esc($st, 'attr') ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= esc(localized_case_status($st)) ?></option>
                <?php endforeach; ?>
            </select>
            <select class="select" name="priority" aria-label="<?= esc(lang('App.cases_filter_priority_aria'), 'attr') ?>">
                <option value=""><?= esc(lang('App.cases_all_priorities')) ?></option>
                <?php foreach (\App\Models\InfringementCaseModel::PRIORITIES as $pr) : ?>
                    <option value="<?= esc($pr, 'attr') ?>" <?= $priorityFilter === $pr ? 'selected' : '' ?>><?= esc(localized_case_priority($pr)) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn--secondary btn--sm"><?= esc(lang('App.action_apply')) ?></button>
            <?php if ($searchQuery !== '' || $statusFilter !== '' || $priorityFilter !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('cases') ?>"><?= esc(lang('App.action_clear')) ?></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('cases.create')) : ?>
            <a class="btn btn--primary" href="<?= site_url('cases/create') ?>"><?= esc(lang('App.cases_open_case')) ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="table-wrap" style="margin-top: 1rem;">
    <table class="data-table">
        <thead>
            <tr>
                <th><?= esc(lang('App.cases_col_title')) ?></th>
                <th><?= esc(lang('App.cases_col_work')) ?></th>
                <th><?= esc(lang('App.cases_col_status')) ?></th>
                <th><?= esc(lang('App.cases_col_priority')) ?></th>
                <th><?= esc(lang('App.cases_col_assignee')) ?></th>
                <th>Opened</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []) : ?>
                <tr>
                    <td colspan="7" class="muted">No cases yet. Open a case from a usage report or manually.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($rows as $r) : ?>
                    <?php $id = (int) ($r['id'] ?? 0); ?>
                    <tr>
                        <td><?= esc((string) ($r['case_title'] ?? '')) ?></td>
                        <td>
                            <a href="<?= site_url('works/' . (int) ($r['work_id'] ?? 0)) ?>"><?= esc((string) ($r['work_title'] ?? '')) ?></a>
                        </td>
                        <td>
                            <?= view('components/badges', [
                                'label' => (string) ($r['case_status_label'] ?? ''),
                                'tone'  => (string) ($r['status_tone'] ?? 'neutral'),
                            ]) ?>
                        </td>
                        <td>
                            <?= view('components/badges', [
                                'label' => (string) ($r['priority_label'] ?? ''),
                                'tone'  => (string) ($r['priority_tone'] ?? 'neutral'),
                            ]) ?>
                        </td>
                        <td><?= esc((string) ($r['assignee_name'] ?? '—')) ?></td>
                        <td><?= esc((string) ($r['opened_at'] ?? '')) ?></td>
                        <td class="table-actions">
                            <?php if ($id > 0) : ?>
                                <a class="btn btn--ghost btn--sm" href="<?= site_url('cases/' . $id) ?>">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
