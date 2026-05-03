<?php
$errors = $errors ?? [];
$works = $works ?? [];
$prefillWorkId = $prefillWorkId ?? null;
$migrationRequired = $migrationRequired ?? false;

$detectedTypes = [
    'website'        => 'Website',
    'social_media'   => 'Social Media',
    'video_platform' => 'Video Platform',
    'marketplace'    => 'Marketplace',
    'internal'       => 'Internal',
];
$usageTypes = [
    'authorized'   => 'Authorized',
    'suspected'    => 'Suspected',
    'infringement' => 'Infringement',
];
$methods = [
    'manual' => 'Manual',
    'ai'     => 'AI (placeholder)',
];

$oldWork = old('work_id', $prefillWorkId !== null ? (string) $prefillWorkId : '');
?>

<?php if ($migrationRequired) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-warning, #a73);">
        <h2 class="card__title">Database migration needed</h2>
        <p class="muted" style="margin:0;">Run <code>php spark migrate</code> from the project root before creating usage reports.</p>
    </div>
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

<p class="page-intro">Record where a work appears; attach an optional screenshot or file under <code>writable/uploads/evidence/</code>.</p>

<div class="card" style="max-width: 920px;">
    <h2 class="card__title">Detection</h2>
    <?= form_open_multipart(site_url('usage-reports'), ['class' => 'stack']) ?>
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
            <div class="field" style="grid-column: 1 / -1;">
                <label for="detected_source">Detected source <span aria-hidden="true">*</span></label>
                <input class="input" id="detected_source" name="detected_source" type="text" required value="<?= esc(old('detected_source', ''), 'attr') ?>" placeholder="https://… or platform / listing name">
                <p class="muted" style="margin:0.35rem 0 0;font-size:0.85rem;">If this starts with http:// or https:// it must be a valid URL.</p>
            </div>
            <div class="field">
                <label for="detected_type">Channel type <span aria-hidden="true">*</span></label>
                <select class="select" id="detected_type" name="detected_type" required>
                    <?php foreach ($detectedTypes as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('detected_type', 'website') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="usage_type">Usage status <span aria-hidden="true">*</span></label>
                <select class="select" id="usage_type" name="usage_type" required>
                    <?php foreach ($usageTypes as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('usage_type', 'suspected') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="detection_method">Detection method <span aria-hidden="true">*</span></label>
                <select class="select" id="detection_method" name="detection_method" required>
                    <?php foreach ($methods as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('detection_method', 'manual') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="detected_at">Detected at</label>
                <input class="input" id="detected_at" name="detected_at" type="datetime-local" value="<?= esc(old('detected_at', date('Y-m-d\TH:i')), 'attr') ?>">
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="notes">Notes</label>
                <textarea class="input" id="notes" name="notes" rows="4" placeholder="Context, takedown status, internal ticket…"><?= esc(old('notes', ''), 'attr') ?></textarea>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="evidence_file">Evidence (file)</label>
                <input class="input" id="evidence_file" name="evidence_file" type="file" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.txt,.csv,.md,.zip">
                <p class="muted" style="margin:0.35rem 0 0;font-size:0.85rem;">Optional. Max 10&nbsp;MiB. Allowed: pdf, images, txt, csv, md, zip.</p>
            </div>
        </div>
        <div style="margin-top: 1rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
            <button type="submit" class="btn btn--primary"<?= $migrationRequired ? ' disabled' : '' ?>>Save report</button>
            <a class="btn btn--secondary" href="<?= site_url('usage-reports') ?>">Cancel</a>
        </div>
    <?= form_close() ?>
</div>
