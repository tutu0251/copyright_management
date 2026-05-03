<?php
$licensee = $licensee ?? [];
$licenses = $licenses ?? [];
$message = $message ?? null;
$lid = (int) ($licensee['id'] ?? 0);
$typeLabel = \App\Models\LicenseeModel::typeLabel((string) ($licensee['licensee_type'] ?? 'individual'));
?>

<?php if ($message) : ?>
    <p class="muted" role="status"><?= esc($message) ?></p>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('licensees') ?>">← Licensees</a>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('licensees.update')) : ?>
            <a class="btn btn--secondary" href="<?= site_url('licensees/' . $lid . '/edit') ?>">Edit</a>
        <?php endif; ?>
        <?php if (user_can('licensees.delete')) : ?>
            <?= form_open(site_url('licensees/' . $lid . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Archive this licensee and all licenses assigned to them?');"]) ?>
                <button type="submit" class="btn btn--ghost">Archive</button>
            <?= form_close() ?>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <h2 class="card__title">Licensee</h2>
        <dl class="dl-grid">
            <dt>Name</dt>
            <dd><strong><?= esc((string) ($licensee['name'] ?? '')) ?></strong></dd>
            <dt>Type</dt>
            <dd><?= esc($typeLabel) ?></dd>
            <dt>Email</dt>
            <dd><?= esc((string) ($licensee['email'] ?? '—')) ?></dd>
            <dt>Phone</dt>
            <dd><?= esc((string) ($licensee['phone'] ?? '—')) ?></dd>
            <dt>Country</dt>
            <dd><?= esc((string) ($licensee['country'] ?? '—')) ?></dd>
            <dt>Address</dt>
            <dd><?= esc((string) ($licensee['address'] ?? '—')) ?></dd>
            <dt>Updated</dt>
            <dd><?= esc((string) ($licensee['updated_at'] ?? '—')) ?></dd>
        </dl>
        <?php if (($licensee['notes'] ?? '') !== '') : ?>
            <p class="muted" style="margin-top: 1rem;"><strong>Notes</strong><br><?= esc((string) $licensee['notes']) ?></p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2 class="card__title">Licenses</h2>
        <p class="muted" style="margin-top: 0;">Agreements referencing this licensee.</p>
        <?php if (user_can('licenses.create')) : ?>
        <div style="margin-bottom: 0.75rem;">
            <a class="btn btn--primary btn--sm" href="<?= site_url('licenses/create?licensee_id=' . $lid) ?>">Create license</a>
        </div>
        <?php endif; ?>
        <?php if ($licenses === []) : ?>
            <p class="muted">No licenses yet.</p>
        <?php else : ?>
            <?= $this->include('components/table') ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Work</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>End</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licenses as $lic) : ?>
                        <?php
                        $licId = (int) ($lic['id'] ?? 0);
                        $wid = (int) ($lic['work_id'] ?? 0);
                        $eff = \App\Models\LicenseModel::effectiveStatus($lic);
                        $effLabel = \App\Models\LicenseModel::statusLabel($eff);
                        ?>
                        <tr>
                            <td><?= esc((string) ($lic['work_title'] ?? '—')) ?></td>
                            <td><?= esc(\App\Models\LicenseModel::licenseTypeLabel((string) ($lic['license_type'] ?? ''))) ?></td>
                            <td><?= esc($effLabel) ?></td>
                            <td><?= esc($lic['end_date'] !== null && $lic['end_date'] !== '' ? (string) $lic['end_date'] : '—') ?></td>
                            <td class="table-actions">
                                <a class="btn btn--ghost btn--sm" href="<?= site_url('licenses/' . $licId) ?>">View</a>
                                <?php if ($wid > 0) : ?>
                                    <a class="btn btn--ghost btn--sm" href="<?= site_url('works/' . $wid) ?>">Work</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?= $this->include('components/table_end') ?>
        <?php endif; ?>
    </div>
</div>
