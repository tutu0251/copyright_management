<?php
$errors = $errors ?? [];
$works = $works ?? [];
$users = $users ?? [];
$prefillWorkId = $prefillWorkId ?? null;
$prefillReportId = $prefillReportId ?? null;
$reportSummary = $reportSummary ?? null;
$migrationRequired = $migrationRequired ?? false;

$statuses = \App\Models\InfringementCaseModel::ALL_STATUSES;
$priorities = \App\Models\InfringementCaseModel::PRIORITIES;

$oldWork = old('work_id', $prefillWorkId !== null ? (string) $prefillWorkId : '');
$oldReport = old('usage_report_id', $prefillReportId !== null ? (string) $prefillReportId : '');
$lockWork = $reportSummary !== null;
?>

<?php if ($migrationRequired) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-warning, #a73);">
        <h2 class="card__title">Database migration needed</h2>
        <p class="muted" style="margin:0;">Run <code>php spark migrate</code> before opening cases.</p>
    </div>
<?php endif; ?>
<?php if ($errors !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($errors as $err) : ?>
                <li><?= esc(is_array($err) ? json_encode($err) : (string) $err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<p class="page-intro">Link a catalog work and optional usage report. Evidence uploads are stored under <code>writable/uploads/cases/</code>.</p>

<?php if ($reportSummary !== null) : ?>
    <div class="card" style="margin-bottom: 1rem;">
        <h2 class="card__title">Usage report context</h2>
        <dl class="dl-grid">
            <dt>Report</dt>
            <dd><a href="<?= site_url('usage-reports/' . (int) $reportSummary['id']) ?>">#<?= (int) $reportSummary['id'] ?></a></dd>
            <dt>Work</dt>
            <dd><?= esc((string) ($reportSummary['work_title'] ?? '')) ?></dd>
            <dt>Source</dt>
            <dd><?= esc((string) ($reportSummary['detected_source'] ?? '')) ?></dd>
            <dt>Usage type</dt>
            <dd><?= esc(\App\Models\UsageReportModel::usageTypeLabel((string) ($reportSummary['usage_type'] ?? ''))) ?></dd>
        </dl>
    </div>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('cases') ?>">← Cases</a>
    </div>
</div>

<div class="card" style="margin-top: 1rem; max-width: 920px;">
    <h2 class="card__title">Case details</h2>
    <?= form_open_multipart(site_url('cases/create'), ['class' => 'stack']) ?>
        <?php
        $hiddenReportId = $prefillReportId !== null ? (int) $prefillReportId : (int) old('usage_report_id', '0');
        ?>
        <?php if ($hiddenReportId > 0) : ?>
            <input type="hidden" name="usage_report_id" value="<?= $hiddenReportId ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="field" style="grid-column: 1 / -1;">
                <label for="case_title">Case title <span aria-hidden="true">*</span></label>
                <input class="input" id="case_title" name="case_title" type="text" required value="<?= esc(old('case_title', $reportSummary !== null ? 'Case: usage #' . (int) $reportSummary['id'] : ''), 'attr') ?>">
            </div>
            <div class="field">
                <label for="work_id">Work <span aria-hidden="true">*</span></label>
                <select class="select" id="work_id" name="work_id" required <?= $lockWork ? 'disabled' : '' ?>>
                    <option value="">— Select —</option>
                    <?php foreach ($works as $w) : ?>
                        <?php $wid = (int) ($w['id'] ?? 0); ?>
                        <option value="<?= $wid ?>" <?= (string) $oldWork === (string) $wid ? 'selected' : '' ?>><?= esc((string) ($w['title'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($lockWork) : ?>
                    <input type="hidden" name="work_id" value="<?= esc((string) $oldWork, 'attr') ?>">
                    <p class="muted" style="margin:0.35rem 0 0;font-size:0.85rem;">Work is fixed while creating from a usage report.</p>
                <?php endif; ?>
            </div>
            <?php if ($hiddenReportId < 1) : ?>
                <div class="field">
                    <label for="usage_report_id">Usage report ID</label>
                    <input class="input" id="usage_report_id" name="usage_report_id" type="number" min="1" step="1" value="<?= esc($oldReport, 'attr') ?>" placeholder="Optional — link existing report">
                </div>
            <?php endif; ?>
            <div class="field">
                <label for="case_status">Initial status</label>
                <select class="select" id="case_status" name="case_status">
                    <?php foreach ($statuses as $st) : ?>
                        <option value="<?= esc($st, 'attr') ?>" <?= old('case_status', 'detected') === $st ? 'selected' : '' ?>><?= esc(\App\Models\InfringementCaseModel::statusLabel($st)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="priority">Priority</label>
                <select class="select" id="priority" name="priority">
                    <?php foreach ($priorities as $pr) : ?>
                        <option value="<?= esc($pr, 'attr') ?>" <?= old('priority', 'medium') === $pr ? 'selected' : '' ?>><?= esc(\App\Models\InfringementCaseModel::priorityLabel($pr)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="assigned_to">Assigned to</label>
                <select class="select" id="assigned_to" name="assigned_to">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($users as $u) : ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <option value="<?= $uid ?>" <?= (string) old('assigned_to', '') === (string) $uid ? 'selected' : '' ?>><?= esc((string) ($u['display_name'] ?? $u['email'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="opened_at">Opened at</label>
                <input class="input" id="opened_at" name="opened_at" type="datetime-local" value="<?= esc(old('opened_at', ''), 'attr') ?>">
                <p class="muted" style="margin:0.35rem 0 0;font-size:0.85rem;">Leave blank for “now”.</p>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="description">Description</label>
                <textarea class="input" id="description" name="description" rows="5"><?= esc(old('description', '')) ?></textarea>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="resolution_notes">Resolution notes</label>
                <textarea class="input" id="resolution_notes" name="resolution_notes" rows="3"><?= esc(old('resolution_notes', '')) ?></textarea>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="evidence_file">Additional evidence (optional)</label>
                <input class="input" id="evidence_file" name="evidence_file" type="file">
            </div>
        </div>
        <div style="margin-top: 1rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
            <button type="submit" class="btn btn--primary" <?= $migrationRequired ? 'disabled' : '' ?>>Create case</button>
            <a class="btn btn--ghost" href="<?= site_url('cases') ?>">Cancel</a>
        </div>
    <?= form_close() ?>
</div>
