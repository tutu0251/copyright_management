<?php
$errors = $errors ?? [];
$works = $works ?? [];
$licensees = $licensees ?? [];
$prefillWorkId = $prefillWorkId ?? null;
$prefillLicenseeId = $prefillLicenseeId ?? null;

$licenseTypes = [
    'exclusive'        => 'Exclusive',
    'non_exclusive'    => 'Non-exclusive',
    'commercial'       => 'Commercial',
    'personal'         => 'Personal',
    'educational'      => 'Educational',
    'internal_use'     => 'Internal Use',
];
$payStatuses = [
    'unpaid'   => 'Unpaid',
    'paid'     => 'Paid',
    'partial'  => 'Partial',
    'waived'   => 'Waived',
];
$licStatuses = [
    'draft'           => 'Draft',
    'active'          => 'Active',
    'expiring_soon'   => 'Expiring Soon',
    'expired'         => 'Expired',
    'cancelled'       => 'Cancelled',
];

$oldWork = old('work_id', $prefillWorkId !== null ? (string) $prefillWorkId : '');
$oldLic = old('licensee_id', $prefillLicenseeId !== null ? (string) $prefillLicenseeId : '');
?>

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

<p class="page-intro">Link a work and a licensee with commercial terms.</p>

<div class="card" style="max-width: 920px;">
    <h2 class="card__title">License details</h2>
    <?= form_open(site_url('licenses'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field">
                <label for="work_id">Work <span aria-hidden="true">*</span></label>
                <select class="select" id="work_id" name="work_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($works as $w) : ?>
                        <?php $wid = (int) ($w['id'] ?? 0); ?>
                        <option value="<?= $wid ?>" <?= (string) $oldWork === (string) $wid ? 'selected' : '' ?>><?= esc((string) ($w['title'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="licensee_id">Licensee <span aria-hidden="true">*</span></label>
                <select class="select" id="licensee_id" name="licensee_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($licensees as $le) : ?>
                        <?php $lid = (int) ($le['id'] ?? 0); ?>
                        <option value="<?= $lid ?>" <?= (string) $oldLic === (string) $lid ? 'selected' : '' ?>><?= esc((string) ($le['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="license_type">License type <span aria-hidden="true">*</span></label>
                <select class="select" id="license_type" name="license_type" required>
                    <?php foreach ($licenseTypes as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('license_type', 'non_exclusive') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="territory">Territory</label>
                <input class="input" id="territory" name="territory" type="text" value="<?= esc(old('territory', ''), 'attr') ?>" placeholder="Worldwide, EU, …">
            </div>
            <div class="field">
                <label for="start_date">Start date</label>
                <input class="input" id="start_date" name="start_date" type="date" value="<?= esc(old('start_date', ''), 'attr') ?>">
            </div>
            <div class="field">
                <label for="end_date">End date</label>
                <input class="input" id="end_date" name="end_date" type="date" value="<?= esc(old('end_date', ''), 'attr') ?>">
            </div>
            <div class="field">
                <label for="fee_amount">Fee amount</label>
                <input class="input" id="fee_amount" name="fee_amount" type="number" step="0.01" min="0" value="<?= esc(old('fee_amount', '0'), 'attr') ?>">
            </div>
            <div class="field">
                <label for="currency">Currency <span aria-hidden="true">*</span></label>
                <input class="input" id="currency" name="currency" type="text" maxlength="3" value="<?= esc(old('currency', 'USD'), 'attr') ?>" placeholder="USD">
            </div>
            <div class="field">
                <label for="payment_status">Payment status <span aria-hidden="true">*</span></label>
                <select class="select" id="payment_status" name="payment_status" required>
                    <?php foreach ($payStatuses as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('payment_status', 'unpaid') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="license_status">License status <span aria-hidden="true">*</span></label>
                <select class="select" id="license_status" name="license_status" required>
                    <?php foreach ($licStatuses as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('license_status', 'draft') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="field">
            <label for="terms">Terms / notes</label>
            <textarea class="textarea" id="terms" name="terms" rows="4" placeholder="Scope, obligations, audit clauses…"><?= esc(old('terms', '')) ?></textarea>
        </div>
        <div class="toolbar" style="margin: 0; padding-top: 0.5rem;">
            <div class="toolbar__left"></div>
            <div class="toolbar__right">
                <a class="btn btn--secondary" href="<?= site_url('licenses') ?>">Cancel</a>
                <button type="submit" class="btn btn--primary">Save license</button>
            </div>
        </div>
    <?= form_close() ?>
</div>
