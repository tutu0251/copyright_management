<?php
$work = $work ?? [];
$links = $links ?? [];
$pickOwners = $pickOwners ?? [];
$totalPercent = $totalPercent ?? 0.0;
$errors = $errors ?? [];
$message = $message ?? null;
$wid = (int) ($work['id'] ?? 0);
$roles = [
    'creator'           => 'Creator',
    'copyright_owner'   => 'Copyright Owner',
    'publisher'         => 'Publisher',
    'agency'            => 'Agency',
    'distributor'       => 'Distributor',
];
$statuses = [
    'active'   => 'Active',
    'inactive' => 'Inactive',
    'ended'    => 'Ended',
];
?>

<?php if ($message) : ?>
    <p class="muted" role="status"><?= esc($message) ?></p>
<?php endif; ?>

<?php if ($errors !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <h2 class="card__title">Please fix the following</h2>
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($errors as $err) : ?>
                <li><?= esc(is_array($err) ? json_encode($err) : (string) $err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('works/' . $wid) ?>">← <?= esc((string) ($work['title'] ?? 'Work')) ?></a>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('owners.create')) : ?>
            <a class="btn btn--secondary btn--sm" href="<?= site_url('owners/create') ?>">New owner record</a>
        <?php endif; ?>
    </div>
</div>

<p class="page-intro">Link parties to this work with a role, percentage, and optional dates. Only rows with status <strong>Active</strong> count toward the 100% cap (inactive or ended rows do not).</p>

<p class="muted"><strong>Active allocation on this work:</strong> <?= esc(number_format((float) $totalPercent, 2)) ?>% · <strong>Remaining for new active links:</strong> <?= esc(number_format(max(0, 100 - (float) $totalPercent), 2)) ?>%</p>

<div class="grid grid--2" style="align-items: start;">
    <div class="card">
        <h2 class="card__title">Current links</h2>
        <?php if ($links === []) : ?>
            <p class="muted">No owners linked yet.</p>
        <?php else : ?>
            <?= $this->include('components/table') ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>%</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $L) : ?>
                        <?php
                        $pivId = (int) ($L['id'] ?? 0);
                        $oid = (int) ($L['owner_id'] ?? 0);
                        $rn = \App\Models\WorkOwnerModel::roleLabel((string) ($L['ownership_role'] ?? ''));
                        $sn = \App\Models\WorkOwnerModel::statusLabel((string) ($L['status'] ?? ''));
                        ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('owners/' . $oid) ?>"><?= esc((string) ($L['owner_name'] ?? '')) ?></a>
                            </td>
                            <td><?= esc(number_format((float) ($L['ownership_percentage'] ?? 0), 2)) ?></td>
                            <td><?= esc($rn) ?></td>
                            <td><?= esc($sn) ?></td>
                            <td class="table-actions">
                                <?php if (user_can('owners.update')) : ?>
                                <?= form_open(site_url('works/' . $wid . '/owners/' . $pivId . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Unlink this owner from the work?');"]) ?>
                                    <button type="submit" class="btn btn--ghost btn--sm">Unlink</button>
                                <?= form_close() ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?= $this->include('components/table_end') ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 class="card__title">Add link</h2>
        <?php if ($pickOwners === []) : ?>
            <p class="muted">Every owner in the registry is already linked, or there are no owner records yet.</p>
            <?php if (user_can('owners.create')) : ?>
                <a class="btn btn--primary btn--sm" href="<?= site_url('owners/create') ?>">Create owner</a>
            <?php endif; ?>
        <?php elseif (user_can('owners.update')) : ?>
            <?= form_open(site_url('works/' . $wid . '/owners'), ['class' => 'stack']) ?>
                <div class="field">
                    <label for="owner_id">Owner <span aria-hidden="true">*</span></label>
                    <select class="select" id="owner_id" name="owner_id" required>
                        <option value="">— Select —</option>
                        <?php foreach ($pickOwners as $po) : ?>
                            <option value="<?= esc((string) ($po['id'] ?? ''), 'attr') ?>"><?= esc((string) ($po['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="ownership_percentage">Ownership % <span aria-hidden="true">*</span></label>
                    <input class="input" id="ownership_percentage" name="ownership_percentage" type="number" step="0.01" min="0" max="100" required value="<?= esc(old('ownership_percentage', ''), 'attr') ?>" placeholder="e.g. 50">
                </div>
                <div class="field">
                    <label for="ownership_role">Role <span aria-hidden="true">*</span></label>
                    <select class="select" id="ownership_role" name="ownership_role" required>
                        <?php foreach ($roles as $val => $lab) : ?>
                            <option value="<?= esc($val, 'attr') ?>" <?= old('ownership_role', 'copyright_owner') === $val ? 'selected' : '' ?>><?= esc($lab) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="start_date">Start date</label>
                        <input class="input" id="start_date" name="start_date" type="date" value="<?= esc(old('start_date', ''), 'attr') ?>">
                    </div>
                    <div class="field">
                        <label for="end_date">End date</label>
                        <input class="input" id="end_date" name="end_date" type="date" value="<?= esc(old('end_date', ''), 'attr') ?>">
                    </div>
                    <div class="field">
                        <label for="status">Status <span aria-hidden="true">*</span></label>
                        <select class="select" id="status" name="status" required>
                            <?php foreach ($statuses as $val => $lab) : ?>
                                <option value="<?= esc($val, 'attr') ?>" <?= old('status', 'active') === $val ? 'selected' : '' ?>><?= esc($lab) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="toolbar" style="margin:0;padding-top:0.5rem;">
                    <button type="submit" class="btn btn--primary">Link owner</button>
                </div>
            <?= form_close() ?>
        <?php else : ?>
            <p class="muted">You do not have permission to add or change ownership links for this work.</p>
        <?php endif; ?>
    </div>
</div>
