<?php
$licenseStatusPill = static function (array $lic): string {
    $st = (string) ($lic['status'] ?? '');
    if (strcasecmp($st, 'Active') === 0) {
        $exp   = (string) ($lic['expires'] ?? '');
        $expTs = $exp !== '' ? strtotime($exp) : false;
        if ($expTs !== false && $expTs < strtotime('+90 days') && $expTs >= strtotime('today')) {
            return 'pill-warning';
        }

        return 'pill-success';
    }
    if (preg_match('/pending|expir/i', $st) === 1) {
        return 'pill-warning';
    }
    if (preg_match('/expired|terminated|revoked|cancel|archiv|draft/i', $st) === 1) {
        return 'pill-neutral';
    }

    return 'pill-neutral';
};
?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/licenses') ?>">← All licenses</a>
    </div>
    <div class="toolbar__right">
        <button type="button" class="btn btn--secondary" disabled>Download PDF</button>
        <button type="button" class="btn btn--primary" disabled>Renew</button>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <h2 class="card__title">License terms</h2>
        <dl class="dl-grid">
            <dt>License ID</dt>
            <dd><strong><?= esc($license['id']) ?></strong></dd>
            <dt>Work</dt>
            <dd><a href="<?= site_url('mockup/work/' . $license['work_id']) ?>"><?= esc($license['work_title']) ?></a></dd>
            <dt>Licensee</dt>
            <dd><?= esc($license['licensee']) ?></dd>
            <dt>Type</dt>
            <dd><?= esc($license['type']) ?></dd>
            <dt>Status</dt>
            <dd>
                <span class="app-pill <?= esc($licenseStatusPill($license)) ?>"><?= esc($license['status']) ?></span>
            </dd>
            <dt>Expiry</dt>
            <dd><?= esc($license['expires']) ?></dd>
            <dt>Royalty</dt>
            <dd><?= esc($license['royalty']) ?></dd>
            <dt>Territory</dt>
            <dd><?= esc($license['territory']) ?></dd>
            <dt>Channels</dt>
            <dd><?= esc($license['channels']) ?></dd>
        </dl>
        <p class="muted" style="margin-top: 1rem;"><?= esc($license['notes']) ?></p>
    </div>
    <div class="card">
        <h2 class="card__title">Compliance checklist (mock)</h2>
        <ul class="stack">
            <li><label><input type="checkbox" disabled> Credit line approved</label></li>
            <li><label><input type="checkbox" disabled> Usage caps configured</label></li>
            <li><label><input type="checkbox" disabled> Territory restrictions acknowledged</label></li>
        </ul>
        <button type="button" class="btn btn--secondary" disabled style="margin-top: 1rem;">Save checklist</button>
    </div>
</div>
