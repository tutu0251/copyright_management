<?php
$migrationRequired = $migrationRequired ?? false;
$rows = $rows ?? [];
$pager = $pager ?? ['page' => 1, 'perPage' => 50, 'total' => 0, 'totalPages' => 1];
$filterEntityType = $filterEntityType ?? '';
$filterEntityId = $filterEntityId ?? '';
?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('dashboard') ?>">← Dashboard</a>
    </div>
</div>

<h1 class="page-title" style="margin-top:0.5rem;">Activity &amp; audit log</h1>
<p class="muted">System-wide trace of authenticated actions for compliance and accountability.</p>

<?php if ($migrationRequired) : ?>
    <div class="card" style="margin-top:1rem;border-color:var(--cm-warning, #a80);">
        <p style="margin:0;">Run <code>php spark migrate</code> to apply the audit log schema update (<code>action_type</code>, <code>old_values</code>, <code>new_values</code>).</p>
    </div>
<?php else : ?>
    <div class="card" style="margin-top:1rem;">
        <h2 class="card__title">Filters</h2>
        <?= form_open(site_url('activities'), ['method' => 'get', 'class' => 'stack', 'style' => 'max-width:32rem;']) ?>
            <div class="field">
                <label for="entity_type">Entity type</label>
                <select class="select" id="entity_type" name="entity_type">
                    <option value="">Any</option>
                    <option value="work" <?= $filterEntityType === 'work' ? 'selected' : '' ?>>work</option>
                    <option value="owner" <?= $filterEntityType === 'owner' ? 'selected' : '' ?>>owner</option>
                    <option value="license" <?= $filterEntityType === 'license' ? 'selected' : '' ?>>license</option>
                    <option value="usage_report" <?= $filterEntityType === 'usage_report' ? 'selected' : '' ?>>usage_report</option>
                    <option value="case" <?= $filterEntityType === 'case' ? 'selected' : '' ?>>case</option>
                    <option value="user" <?= $filterEntityType === 'user' ? 'selected' : '' ?>>user</option>
                </select>
            </div>
            <div class="field">
                <label for="entity_id">Entity ID</label>
                <input class="input" type="number" min="1" id="entity_id" name="entity_id" value="<?= esc($filterEntityId, 'attr') ?>" placeholder="Optional">
            </div>
            <button type="submit" class="btn btn--secondary btn--sm">Apply</button>
            <?php if ($filterEntityType !== '' || $filterEntityId !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('activities') ?>">Clear</a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>

    <div class="card" style="margin-top:1rem;">
        <h2 class="card__title">Recent actions</h2>
        <?php if ($rows === []) : ?>
            <p class="muted" style="margin:0;">No audit entries yet.</p>
        <?php else : ?>
            <div class="table-wrap table-wrap--flush" style="margin-top:0.75rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td><?= esc((string) ($row['created_at'] ?? '')) ?></td>
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
                    Page <?= esc((string) $pg) ?> of <?= esc((string) $tp) ?>
                    <?php if ($pg > 1) : ?>
                        · <a href="<?= site_url('activities?' . http_build_query(array_filter([
                            'page' => $pg - 1,
                            'entity_type' => $filterEntityType !== '' ? $filterEntityType : null,
                            'entity_id' => $filterEntityId !== '' ? $filterEntityId : null,
                        ]))) ?>">Previous</a>
                    <?php endif; ?>
                    <?php if ($pg < $tp) : ?>
                        · <a href="<?= site_url('activities?' . http_build_query(array_filter([
                            'page' => $pg + 1,
                            'entity_type' => $filterEntityType !== '' ? $filterEntityType : null,
                            'entity_id' => $filterEntityId !== '' ? $filterEntityId : null,
                        ]))) ?>">Next</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
