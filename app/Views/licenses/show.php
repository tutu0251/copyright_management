<?php
$license = $license ?? [];
$message = $message ?? null;
$id = (int) ($license['id'] ?? 0);
$wid = (int) ($license['work_id'] ?? 0);
$lid = (int) ($license['licensee_id'] ?? 0);
$eff = \App\Models\LicenseModel::effectiveStatus($license);
$effLabel = \App\Models\LicenseModel::statusLabel($eff);
$fee = (string) ($license['fee_amount'] ?? '0');
$cur = (string) ($license['currency'] ?? 'USD');
?>

<?php if ($message) : ?>
    <p class="muted" role="status"><?= esc($message) ?></p>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('licenses') ?>">← Licenses</a>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--secondary" href="<?= site_url('licenses/' . $id . '/edit') ?>">Edit</a>
        <?= form_open(site_url('licenses/' . $id . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Archive this license?');"]) ?>
            <button type="submit" class="btn btn--ghost">Archive</button>
        <?= form_close() ?>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <h2 class="card__title">Agreement</h2>
        <dl class="dl-grid">
            <dt>Work</dt>
            <dd>
                <?php if ($wid > 0) : ?>
                    <a href="<?= site_url('works/' . $wid) ?>"><?= esc((string) ($license['work_title'] ?? 'Work #' . $wid)) ?></a>
                <?php else : ?>
                    —
                <?php endif; ?>
            </dd>
            <dt>Licensee</dt>
            <dd>
                <?php if ($lid > 0) : ?>
                    <a href="<?= site_url('licensees/' . $lid) ?>"><?= esc((string) ($license['licensee_name'] ?? 'Licensee #' . $lid)) ?></a>
                <?php else : ?>
                    —
                <?php endif; ?>
            </dd>
            <dt>License type</dt>
            <dd><?= esc(\App\Models\LicenseModel::licenseTypeLabel((string) ($license['license_type'] ?? ''))) ?></dd>
            <dt>Territory</dt>
            <dd><?= esc((string) ($license['territory'] ?? '—')) ?></dd>
            <dt>Start</dt>
            <dd><?= esc($license['start_date'] !== null && $license['start_date'] !== '' ? (string) $license['start_date'] : '—') ?></dd>
            <dt>End</dt>
            <dd><?= esc($license['end_date'] !== null && $license['end_date'] !== '' ? (string) $license['end_date'] : '—') ?></dd>
            <dt>Fee</dt>
            <dd><?= esc($cur . ' ' . number_format((float) $fee, 2)) ?></dd>
            <dt>Payment</dt>
            <dd><?= esc(\App\Models\LicenseModel::paymentLabel((string) ($license['payment_status'] ?? ''))) ?></dd>
            <dt>Status</dt>
            <dd>
                <?php
                $tone = $eff === \App\Models\LicenseModel::STATUS_ACTIVE ? 'success' : ($eff === \App\Models\LicenseModel::STATUS_EXPIRING_SOON ? 'warning' : ($eff === \App\Models\LicenseModel::STATUS_EXPIRED ? 'danger' : 'neutral'));
                echo view('components/badges', ['label' => $effLabel, 'tone' => $tone]);
                ?>
            </dd>
            <dt>Updated</dt>
            <dd><?= esc((string) ($license['updated_at'] ?? '—')) ?></dd>
        </dl>
        <?php if (($license['terms'] ?? '') !== '') : ?>
            <p class="muted" style="margin-top: 1rem;"><strong>Terms / notes</strong><br><?= esc((string) $license['terms']) ?></p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2 class="card__title">Record</h2>
        <p class="muted" style="margin-top:0;">License ID <strong>#<?= esc((string) $id) ?></strong>. Stored status: <?= esc(\App\Models\LicenseModel::statusLabel((string) ($license['license_status'] ?? ''))) ?>; display uses dates where applicable.</p>
        <div style="margin-top: 1rem;">
            <a class="btn btn--primary btn--sm" href="<?= site_url('licenses/create?work_id=' . $wid) ?>">Another license for this work</a>
        </div>
    </div>
</div>
