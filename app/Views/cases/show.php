<?php
/** @var array<string, mixed> $caseRow */
$usageReport = $usageReport ?? null;
$evidence = $evidence ?? [];
$timeline = $timeline ?? [];
$users = $users ?? [];
$statuses = $statuses ?? [];
$errors = $errors ?? [];
$msg = session()->getFlashdata('message');
$cid = (int) ($caseRow['id'] ?? 0);
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
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

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('cases') ?>">← Cases</a>
    </div>
    <div class="toolbar__right" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('cases/' . $cid . '/edit') ?>">Edit</a>
        <?= form_open(site_url('cases/' . $cid . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Delete this case permanently?');"]) ?>
            <button type="submit" class="btn btn--ghost btn--sm">Delete</button>
        <?= form_close() ?>
    </div>
</div>

<div class="grid grid--2" style="margin-top: 1rem;">
    <div class="card">
        <h2 class="card__title">Case</h2>
        <dl class="dl-grid">
            <dt>Title</dt>
            <dd><strong><?= esc((string) ($caseRow['case_title'] ?? '')) ?></strong></dd>
            <dt>Status</dt>
            <dd>
                <?= view('components/badges', [
                    'label' => (string) ($caseRow['case_status_label'] ?? ''),
                    'tone'  => (string) ($caseRow['status_tone'] ?? 'neutral'),
                ]) ?>
            </dd>
            <dt>Priority</dt>
            <dd>
                <?= view('components/badges', [
                    'label' => (string) ($caseRow['priority_label'] ?? ''),
                    'tone'  => (string) ($caseRow['priority_tone'] ?? 'neutral'),
                ]) ?>
            </dd>
            <dt>Opened</dt>
            <dd><?= esc((string) ($caseRow['opened_at'] ?? '—')) ?></dd>
            <dt>Closed</dt>
            <dd><?= esc((string) ($caseRow['closed_at'] ?? '') !== '' ? (string) $caseRow['closed_at'] : '—') ?></dd>
            <dt>Assigned to</dt>
            <dd><?= esc((string) ($caseRow['assignee_name'] ?? '') !== '' ? (string) $caseRow['assignee_name'] : '—') ?></dd>
        </dl>
    </div>
    <div class="card">
        <h2 class="card__title">Work</h2>
        <dl class="dl-grid">
            <dt>Title</dt>
            <dd><a href="<?= site_url('works/' . (int) ($caseRow['work_id'] ?? 0)) ?>"><?= esc((string) ($caseRow['work_title'] ?? '')) ?></a></dd>
        </dl>
        <?php if (($caseRow['usage_report_id'] ?? null) !== null && (int) $caseRow['usage_report_id'] > 0) : ?>
            <h3 class="card__title" style="margin-top:1rem;">Linked usage report</h3>
            <?php if (is_array($usageReport) && $usageReport !== []) : ?>
                <p style="margin:0;"><a href="<?= site_url('usage-reports/' . (int) ($usageReport['id'] ?? 0)) ?>">Report #<?= (int) ($usageReport['id'] ?? 0) ?></a></p>
                <p class="muted" style="margin:0.35rem 0 0;font-size:0.9rem;"><?= esc((string) ($usageReport['detected_source'] ?? '')) ?></p>
                <?php
                $urMime = (string) ($usageReport['evidence_mime_type'] ?? '');
                $urPath = (string) ($usageReport['evidence_path'] ?? '');
                $urIsImg = $urPath !== '' && str_starts_with(strtolower($urMime), 'image/');
                $urEvidUrl = $urPath !== '' ? site_url('usage-reports/' . (int) ($usageReport['id'] ?? 0) . '/evidence') : '';
                ?>
                <?php if ($urPath !== '') : ?>
                    <p class="muted" style="margin:0.75rem 0 0;">Report evidence</p>
                    <?php if ($urIsImg && $urEvidUrl !== '') : ?>
                        <div class="table-wrap" style="max-width:100%;margin-top:0.35rem;">
                            <img src="<?= esc($urEvidUrl, 'attr') ?>" alt="Usage report evidence" style="max-width:100%;height:auto;border-radius:6px;">
                        </div>
                    <?php else : ?>
                        <p style="margin:0.35rem 0 0;"><a class="btn btn--ghost btn--sm" href="<?= esc($urEvidUrl, 'attr') ?>">Open report evidence</a></p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else : ?>
                <p class="muted" style="margin:0;">Report #<?= (int) $caseRow['usage_report_id'] ?> (record unavailable)</p>
            <?php endif; ?>
        <?php else : ?>
            <p class="muted" style="margin-top:0.75rem;">No usage report linked.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Description</h2>
    <?php if (($caseRow['description'] ?? '') !== '') : ?>
        <p style="margin:0;white-space:pre-wrap;"><?= esc((string) $caseRow['description']) ?></p>
    <?php else : ?>
        <p class="muted" style="margin:0;">—</p>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Resolution notes</h2>
    <?php if (($caseRow['resolution_notes'] ?? '') !== '') : ?>
        <p style="margin:0;white-space:pre-wrap;"><?= esc((string) $caseRow['resolution_notes']) ?></p>
    <?php else : ?>
        <p class="muted" style="margin:0;">—</p>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Case evidence files</h2>
    <?php if ($evidence === []) : ?>
        <p class="muted" style="margin:0;">No additional files yet.</p>
    <?php else : ?>
        <ul style="margin:0;padding-left:1.25rem;">
            <?php foreach ($evidence as $ev) : ?>
                <li style="margin-bottom:0.75rem;">
                    <?php if (! empty($ev['is_image']) && ($ev['url'] ?? '') !== '') : ?>
                        <div class="table-wrap" style="max-width:420px;">
                            <img src="<?= esc((string) $ev['url'], 'attr') ?>" alt="" style="max-width:100%;height:auto;border-radius:6px;">
                        </div>
                    <?php endif; ?>
                    <a href="<?= esc((string) ($ev['url'] ?? ''), 'attr') ?>"><?= esc((string) ($ev['original_name'] ?? 'File')) ?></a>
                    <span class="muted" style="font-size:0.85rem;"> · <?= esc((string) ($ev['created_at'] ?? '')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?= form_open_multipart(site_url('cases/' . $cid . '/evidence'), ['class' => 'stack', 'style' => 'margin-top:1rem;']) ?>
        <div class="field">
            <label for="evidence_file">Upload file</label>
            <input class="input" id="evidence_file" name="evidence_file" type="file" required>
        </div>
        <button type="submit" class="btn btn--secondary btn--sm">Upload evidence</button>
    <?= form_close() ?>
</div>

<div class="grid grid--2" style="margin-top: 1rem;">
    <div class="card">
        <h2 class="card__title">Change status</h2>
        <?= form_open(site_url('cases/' . $cid . '/status'), ['class' => 'stack']) ?>
            <div class="field">
                <label for="case_status">New status</label>
                <select class="select" id="case_status" name="case_status" required>
                    <?php foreach ($statuses as $st) : ?>
                        <option value="<?= esc($st, 'attr') ?>" <?= ($caseRow['case_status'] ?? '') === $st ? 'selected' : '' ?>><?= esc(\App\Models\InfringementCaseModel::statusLabel($st)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="transition_note">Note (optional)</label>
                <textarea class="input" id="transition_note" name="transition_note" rows="2" placeholder="Context for this transition…"></textarea>
            </div>
            <button type="submit" class="btn btn--primary btn--sm">Update status</button>
        <?= form_close() ?>
    </div>
    <div class="card">
        <h2 class="card__title">Add note</h2>
        <?= form_open(site_url('cases/' . $cid . '/note'), ['class' => 'stack']) ?>
            <div class="field">
                <label for="note_body">Note</label>
                <textarea class="input" id="note_body" name="note_body" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn--secondary btn--sm">Save note</button>
        <?= form_close() ?>
    </div>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Activity timeline</h2>
    <p class="muted" style="margin-top:0;">Status changes and notes in chronological order.</p>
    <?php if ($timeline === []) : ?>
        <p class="muted" style="margin:0;">No activity yet.</p>
    <?php else : ?>
        <ul class="timeline" style="list-style:none;margin:1rem 0 0;padding:0;border-left:2px solid rgba(255,255,255,0.12);">
            <?php foreach ($timeline as $item) : ?>
                <li style="position:relative;padding:0 0 1rem 1.25rem;">
                    <span style="position:absolute;left:-6px;top:0.35rem;width:10px;height:10px;border-radius:50%;background:var(--cm-accent, #6cf);" aria-hidden="true"></span>
                    <div class="muted" style="font-size:0.8rem;"><?= esc((string) ($item['at'] ?? '')) ?><?php if (($item['actor'] ?? '') !== '') : ?> · <?= esc((string) $item['actor']) ?><?php endif; ?></div>
                    <div><strong><?= esc((string) ($item['label'] ?? '')) ?></strong> — <?= esc((string) ($item['body'] ?? '')) ?></div>
                    <?php if (($item['sub'] ?? '') !== '') : ?>
                        <p class="muted" style="margin:0.25rem 0 0;font-size:0.9rem;white-space:pre-wrap;"><?= esc((string) $item['sub']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
$auditHistory = $auditHistory ?? [];
$auditHistoryMoreUrl = $auditHistoryMoreUrl ?? null;
?>
<?= view('components/entity_audit_history', [
    'auditHistory' => $auditHistory,
    'moreUrl'      => $auditHistoryMoreUrl,
    'sectionTitle' => 'System audit log',
]) ?>
