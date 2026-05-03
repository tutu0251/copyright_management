<?php
/** @var array<string, mixed> $report */
$errors = $errors ?? [];
$msg = session()->getFlashdata('message');
$id = (int) ($report['id'] ?? 0);
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
        <a class="btn btn--secondary btn--sm" href="<?= site_url('usage-reports') ?>">← Usage reports</a>
    </div>
    <div class="toolbar__right" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('usage-reports/' . $id . '/edit') ?>">Edit</a>
        <?= form_open(site_url('usage-reports/' . $id . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Archive this usage report?');"]) ?>
            <button type="submit" class="btn btn--ghost btn--sm">Archive</button>
        <?= form_close() ?>
    </div>
</div>

<div class="grid grid--2" style="margin-top: 1rem;">
    <div class="card">
        <h2 class="card__title">Work</h2>
        <dl class="dl-grid">
            <dt>Title</dt>
            <dd>
                <a href="<?= site_url('works/' . (int) ($report['work_id'] ?? 0)) ?>"><?= esc((string) ($report['work_title'] ?? '')) ?></a>
            </dd>
            <dt>Usage status</dt>
            <dd>
                <?= view('components/badges', [
                    'label' => (string) ($report['usage_type_label'] ?? ''),
                    'tone'  => (string) ($report['usage_tone'] ?? 'neutral'),
                ]) ?>
            </dd>
            <dt>Channel type</dt>
            <dd><?= esc((string) ($report['detected_type_label'] ?? '')) ?></dd>
            <dt>Detected at</dt>
            <dd><?= esc((string) ($report['detected_at'] ?? '')) ?></dd>
            <dt>Detection method</dt>
            <dd><?= esc((string) ($report['detection_method_label'] ?? '')) ?></dd>
        </dl>
    </div>
    <div class="card">
        <h2 class="card__title">Source</h2>
        <?php if (! empty($report['is_url'])) : ?>
            <p style="margin:0;word-break:break-all;"><a href="<?= esc((string) $report['detected_source'], 'attr') ?>" rel="noopener noreferrer" target="_blank"><?= esc((string) $report['detected_source']) ?></a></p>
        <?php else : ?>
            <p style="margin:0;"><?= esc((string) $report['detected_source']) ?></p>
        <?php endif; ?>

        <?php if (! empty($report['evidence_path'])) : ?>
            <h3 class="card__title" style="margin-top:1rem;">Evidence</h3>
            <?php if (! empty($report['evidence_is_image']) && ($report['evidence_url'] ?? '') !== '') : ?>
                <p class="muted" style="margin-top:0;">Preview</p>
                <div class="table-wrap" style="max-width:100%;">
                    <img src="<?= esc((string) $report['evidence_url'], 'attr') ?>" alt="Evidence preview" style="max-width:100%;height:auto;border-radius:6px;">
                </div>
            <?php else : ?>
                <p class="muted" style="margin-top:0;">A file is stored for this report (non-image MIME). Use <strong>Edit</strong> to replace it.</p>
            <?php endif; ?>
        <?php else : ?>
            <p class="muted" style="margin-top:0.75rem;">No evidence file attached.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Notes</h2>
    <?php if (($report['notes'] ?? '') !== '') : ?>
        <p style="margin:0;white-space:pre-wrap;"><?= esc((string) $report['notes']) ?></p>
    <?php else : ?>
        <p class="muted" style="margin:0;">—</p>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Actions</h2>
    <p class="muted" style="margin-top:0;">Update classification or prepare follow-up.</p>
    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:0.75rem;">
        <?= form_open(site_url('usage-reports/' . $id . '/mark-authorized'), ['style' => 'display:inline;']) ?>
            <button type="submit" class="btn btn--secondary btn--sm" <?= ($report['usage_type'] ?? '') === 'authorized' ? 'disabled' : '' ?>>Mark authorized</button>
        <?= form_close() ?>
        <?= form_open(site_url('usage-reports/' . $id . '/mark-infringement'), ['style' => 'display:inline;']) ?>
            <button type="submit" class="btn btn--secondary btn--sm" <?= ($report['usage_type'] ?? '') === 'infringement' ? 'disabled' : '' ?>>Mark infringement</button>
        <?= form_close() ?>
        <?= form_open(site_url('usage-reports/' . $id . '/escalate-case'), ['style' => 'display:inline;']) ?>
            <button type="submit" class="btn btn--primary btn--sm">Escalate to case</button>
        <?= form_close() ?>
    </div>
    <p class="muted" style="margin:0.75rem 0 0;font-size:0.85rem;">Escalation workflow will connect to infringement cases in Step&nbsp;6.</p>
</div>
