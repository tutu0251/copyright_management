<?php
$errors = $errors ?? [];
$report = $report ?? [];
$works = $works ?? [];

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

$rid = (int) ($report['id'] ?? 0);
$hasEvidence = ! empty($report['evidence_path']);
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

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('usage-reports/' . $rid) ?>">← Report</a>
    </div>
</div>

<div class="card" style="max-width: 920px; margin-top: 1rem;">
    <h2 class="card__title">Edit usage report #<?= esc((string) $rid) ?></h2>
    <?= form_open_multipart(site_url('usage-reports/' . $rid . '/update'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field">
                <label for="work_id">Work <span aria-hidden="true">*</span></label>
                <select class="select" id="work_id" name="work_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($works as $w) : ?>
                        <?php $wid = (int) ($w['id'] ?? 0); ?>
                        <option value="<?= $wid ?>" <?= (string) old('work_id', (string) ($report['work_id'] ?? '')) === (string) $wid ? 'selected' : '' ?>><?= esc((string) ($w['title'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="detected_source">Detected source <span aria-hidden="true">*</span></label>
                <input class="input" id="detected_source" name="detected_source" type="text" required value="<?= esc(old('detected_source', (string) ($report['detected_source'] ?? '')), 'attr') ?>">
            </div>
            <div class="field">
                <label for="detected_type">Channel type <span aria-hidden="true">*</span></label>
                <select class="select" id="detected_type" name="detected_type" required>
                    <?php foreach ($detectedTypes as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('detected_type', (string) ($report['detected_type'] ?? '')) === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="usage_type">Usage status <span aria-hidden="true">*</span></label>
                <select class="select" id="usage_type" name="usage_type" required>
                    <?php foreach ($usageTypes as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('usage_type', (string) ($report['usage_type'] ?? '')) === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="detection_method">Detection method <span aria-hidden="true">*</span></label>
                <select class="select" id="detection_method" name="detection_method" required>
                    <?php foreach ($methods as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('detection_method', (string) ($report['detection_method'] ?? 'manual')) === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="detected_at">Detected at</label>
                <?php
                $da = old('detected_at', (string) ($report['detected_at'] ?? ''));
                $daLocal = $da !== '' ? str_replace(' ', 'T', substr($da, 0, 16)) : '';
                ?>
                <input class="input" id="detected_at" name="detected_at" type="datetime-local" value="<?= esc($daLocal, 'attr') ?>">
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="notes">Notes</label>
                <textarea class="input" id="notes" name="notes" rows="4"><?= esc(old('notes', (string) ($report['notes'] ?? '')), 'attr') ?></textarea>
            </div>
            <?php if ($hasEvidence) : ?>
                <div class="field" style="grid-column: 1 / -1;">
                    <label>
                        <input type="checkbox" name="remove_evidence" value="1">
                        Remove current evidence file
                    </label>
                </div>
            <?php endif; ?>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="evidence_file"><?= $hasEvidence ? 'Replace evidence file' : 'Evidence (file)' ?></label>
                <input class="input" id="evidence_file" name="evidence_file" type="file" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.txt,.csv,.md,.zip">
            </div>
        </div>
        <div style="margin-top: 1rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
            <button type="submit" class="btn btn--primary">Save changes</button>
            <a class="btn btn--secondary" href="<?= site_url('usage-reports/' . $rid) ?>">Cancel</a>
        </div>
    <?= form_close() ?>
</div>
