<?php
$migrationRequired = $migrationRequired ?? false;
$rows = $rows ?? [];
$pager = $pager ?? ['page' => 1, 'perPage' => 50, 'total' => 0, 'totalPages' => 1];
$filterEntityType = $filterEntityType ?? '';
$filterEntityId = $filterEntityId ?? '';
?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('dashboard') ?>"><?= esc(lang('App.action_back_dashboard')) ?></a>
    </div>
</div>

<h1 class="page-title" style="margin-top:0.5rem;"><?= esc(lang('App.activities_title')) ?></h1>
<p class="muted"><?= esc(lang('App.activities_intro')) ?></p>

<?php if ($migrationRequired) : ?>
    <div class="card" style="margin-top:1rem;border-color:var(--cm-warning, #a80);">
        <p style="margin:0;"><?= lang('App.activities_migration') ?></p>
    </div>
<?php else : ?>
    <div class="card" style="margin-top:1rem;">
        <h2 class="card__title"><?= esc(lang('App.activities_filters')) ?></h2>
        <?= form_open(site_url('activities'), ['method' => 'get', 'class' => 'stack', 'style' => 'max-width:32rem;']) ?>
            <div class="field">
                <label for="entity_type"><?= esc(lang('App.activities_entity_type')) ?></label>
                <select class="select" id="entity_type" name="entity_type">
                    <option value=""><?= esc(lang('App.activities_entity_any')) ?></option>
                    <option value="work" <?= $filterEntityType === 'work' ? 'selected' : '' ?>>work</option>
                    <option value="owner" <?= $filterEntityType === 'owner' ? 'selected' : '' ?>>owner</option>
                    <option value="license" <?= $filterEntityType === 'license' ? 'selected' : '' ?>>license</option>
                    <option value="usage_report" <?= $filterEntityType === 'usage_report' ? 'selected' : '' ?>>usage_report</option>
                    <option value="case" <?= $filterEntityType === 'case' ? 'selected' : '' ?>>case</option>
                    <option value="user" <?= $filterEntityType === 'user' ? 'selected' : '' ?>>user</option>
                </select>
            </div>
            <div class="field">
                <label for="entity_id"><?= esc(lang('App.activities_entity_id')) ?></label>
                <input class="input" type="number" min="1" id="entity_id" name="entity_id" value="<?= esc($filterEntityId, 'attr') ?>" placeholder="<?= esc(lang('App.activities_optional'), 'attr') ?>">
            </div>
            <button type="submit" class="btn btn--secondary btn--sm"><?= esc(lang('App.action_apply')) ?></button>
            <?php if ($filterEntityType !== '' || $filterEntityId !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('activities') ?>"><?= esc(lang('App.action_clear')) ?></a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>

    <div class="card" style="margin-top:1rem;">
        <h2 class="card__title"><?= esc(lang('App.activities_recent')) ?></h2>
        <?php if ($rows === []) : ?>
            <p class="muted" style="margin:0;"><?= esc(lang('App.activities_empty')) ?></p>
        <?php else : ?>
            <div class="table-wrap table-wrap--flush" style="margin-top:0.75rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= esc(lang('App.dashboard_table_when')) ?></th>
                            <th><?= esc(lang('App.dashboard_table_user')) ?></th>
                            <th><?= esc(lang('App.dashboard_table_action')) ?></th>
                            <th><?= esc(lang('App.dashboard_table_entity')) ?></th>
                            <th><?= esc(lang('App.activities_col_ip')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td><?= esc(localized_date((string) ($row['created_at'] ?? ''), true)) ?></td>
                                <td><?= esc((string) ($row['actor'] ?? '—')) ?></td>
                                <td><code><?= esc((string) ($row['action_type'] ?? '')) ?></code></td>
                                <td><?= esc((string) ($row['entity_label'] ?? '')) ?></td>
                                <td class="muted"><?= esc((string) ($row['ip_address'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            $tp = (int) ($pager['totalPages'] ?? 1);
            $pg = (int) ($pager['page'] ?? 1);
            ?>
            <?php if ($tp > 1) : ?>
                <p class="muted" style="margin-top:1rem;">
                    <?= esc(lang('App.activities_page_of', ['page' => (string) $pg, 'pages' => (string) $tp])) ?>
                    <?php if ($pg > 1) : ?>
                        · <a href="<?= site_url('activities?' . http_build_query(array_filter([
                            'page' => $pg - 1,
                            'entity_type' => $filterEntityType !== '' ? $filterEntityType : null,
                            'entity_id' => $filterEntityId !== '' ? $filterEntityId : null,
                        ]))) ?>"><?= esc(lang('App.table_pagination_prev')) ?></a>
                    <?php endif; ?>
                    <?php if ($pg < $tp) : ?>
                        · <a href="<?= site_url('activities?' . http_build_query(array_filter([
                            'page' => $pg + 1,
                            'entity_type' => $filterEntityType !== '' ? $filterEntityType : null,
                            'entity_id' => $filterEntityId !== '' ? $filterEntityId : null,
                        ]))) ?>"><?= esc(lang('App.table_pagination_next')) ?></a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
