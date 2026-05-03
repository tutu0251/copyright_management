<?php
/** @var array<string, mixed> $caseRow */
$caseRow = $caseRow ?? [];
$works = $works ?? [];
$users = $users ?? [];
$errors = $errors ?? [];
$cid = (int) ($caseRow['id'] ?? 0);
$priorities = \App\Models\InfringementCaseModel::PRIORITIES;
$lockWork = ! empty($caseRow['usage_report_id']);
?>

<?php if ($errors !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($errors as $err) : ?>
                <li><?= esc(is_array($err) ? json_encode($err) : (string) $err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('cases/' . $cid) ?>">← Case</a>
    </div>
</div>

<div class="card" style="margin-top: 1rem; max-width: 920px;">
    <h2 class="card__title">Edit case</h2>
    <p class="muted" style="margin-top:0;">Status changes are logged from the case detail page.</p>
    <?= form_open(site_url('cases/' . $cid . '/update'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field" style="grid-column: 1 / -1;">
                <label for="case_title">Case title <span aria-hidden="true">*</span></label>
                <input class="input" id="case_title" name="case_title" type="text" required value="<?= esc(old('case_title', (string) ($caseRow['case_title'] ?? '')), 'attr') ?>">
            </div>
            <div class="field">
                <label for="work_id">Work <span aria-hidden="true">*</span></label>
                <select class="select" id="work_id" name="work_id" required <?= $lockWork ? 'disabled' : '' ?>>
                    <?php foreach ($works as $w) : ?>
                        <?php $wid = (int) ($w['id'] ?? 0); ?>
                        <option value="<?= $wid ?>" <?= (string) old('work_id', (string) ($caseRow['work_id'] ?? '')) === (string) $wid ? 'selected' : '' ?>><?= esc((string) ($w['title'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($lockWork) : ?>
                    <input type="hidden" name="work_id" value="<?= esc((string) ($caseRow['work_id'] ?? ''), 'attr') ?>">
                    <p class="muted" style="margin:0.35rem 0 0;font-size:0.85rem;">Work is locked while a usage report is linked.</p>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="priority">Priority</label>
                <select class="select" id="priority" name="priority">
                    <?php foreach ($priorities as $pr) : ?>
                        <option value="<?= esc($pr, 'attr') ?>" <?= old('priority', (string) ($caseRow['priority'] ?? 'medium')) === $pr ? 'selected' : '' ?>><?= esc(\App\Models\InfringementCaseModel::priorityLabel($pr)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="assigned_to">Assigned to</label>
                <select class="select" id="assigned_to" name="assigned_to">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($users as $u) : ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <option value="<?= $uid ?>" <?= (string) old('assigned_to', (string) ($caseRow['assigned_to'] ?? '')) === (string) $uid ? 'selected' : '' ?>><?= esc((string) ($u['display_name'] ?? $u['email'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="opened_at">Opened at</label>
                <?php
                $oa = old('opened_at', (string) ($caseRow['opened_at'] ?? ''));
                $oaLocal = $oa !== '' ? str_replace(' ', 'T', substr($oa, 0, 16)) : '';
                ?>
                <input class="input" id="opened_at" name="opened_at" type="datetime-local" value="<?= esc($oaLocal, 'attr') ?>">
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="description">Description</label>
                <textarea class="input" id="description" name="description" rows="5"><?= esc(old('description', (string) ($caseRow['description'] ?? ''))) ?></textarea>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="resolution_notes">Resolution notes</label>
                <textarea class="input" id="resolution_notes" name="resolution_notes" rows="3"><?= esc(old('resolution_notes', (string) ($caseRow['resolution_notes'] ?? ''))) ?></textarea>
            </div>
        </div>
        <div style="margin-top: 1rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
            <button type="submit" class="btn btn--primary">Save</button>
            <a class="btn btn--ghost" href="<?= site_url('cases/' . $cid) ?>">Cancel</a>
        </div>
    <?= form_close() ?>
</div>
