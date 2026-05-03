<?php
$owner = $owner ?? [];
$workLinks = $workLinks ?? [];
$message = $message ?? null;
$oid = (int) ($owner['id'] ?? 0);
$typeLabel = \App\Models\OwnerModel::ownerTypeLabel((string) ($owner['owner_type'] ?? 'individual'));
?>

<?php if ($message) : ?>
    <p class="muted" role="status"><?= esc($message) ?></p>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('owners') ?>">← Owners</a>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--secondary" href="<?= site_url('owners/' . $oid . '/edit') ?>">Edit</a>
        <?= form_open(site_url('owners/' . $oid . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Archive this owner and unlink from all works?');"]) ?>
            <button type="submit" class="btn btn--ghost">Archive</button>
        <?= form_close() ?>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <h2 class="card__title">Owner</h2>
        <dl class="dl-grid">
            <dt>Name</dt>
            <dd><strong><?= esc((string) ($owner['name'] ?? '')) ?></strong></dd>
            <dt>Type</dt>
            <dd><?= esc($typeLabel) ?></dd>
            <dt>Email</dt>
            <dd><?= esc((string) ($owner['email'] ?? '—')) ?></dd>
            <dt>Phone</dt>
            <dd><?= esc((string) ($owner['phone'] ?? '—')) ?></dd>
            <dt>Country</dt>
            <dd><?= esc((string) ($owner['country'] ?? '—')) ?></dd>
            <dt>Address</dt>
            <dd><?= esc((string) ($owner['address'] ?? '—')) ?></dd>
            <dt>Updated</dt>
            <dd><?= esc((string) ($owner['updated_at'] ?? '—')) ?></dd>
        </dl>
        <?php if (($owner['notes'] ?? '') !== '') : ?>
            <p class="muted" style="margin-top: 1rem;"><strong>Notes</strong><br><?= esc((string) $owner['notes']) ?></p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2 class="card__title">Linked works</h2>
        <?php if ($workLinks === []) : ?>
            <p class="muted">No works linked yet. Open an asset and use <strong>Ownership</strong> to attach this owner.</p>
        <?php else : ?>
            <?= $this->include('components/table') ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Work</th>
                        <th>Share</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workLinks as $wl) : ?>
                        <?php
                        $wid = (int) ($wl['work_id'] ?? 0);
                        $role = \App\Models\WorkOwnerModel::roleLabel((string) ($wl['ownership_role'] ?? ''));
                        $st = \App\Models\WorkOwnerModel::statusLabel((string) ($wl['status'] ?? ''));
                        ?>
                        <tr>
                            <td><?= esc((string) ($wl['work_title'] ?? '')) ?></td>
                            <td><?= esc(number_format((float) ($wl['ownership_percentage'] ?? 0), 2)) ?>%</td>
                            <td><?= esc($role) ?></td>
                            <td><?= esc($st) ?></td>
                            <td class="table-actions">
                                <a class="btn btn--ghost btn--sm" href="<?= site_url('works/' . $wid) ?>">View work</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?= $this->include('components/table_end') ?>
        <?php endif; ?>
    </div>
</div>
