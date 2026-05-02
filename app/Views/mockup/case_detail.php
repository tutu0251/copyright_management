<?php
$caseStatusTone = static function (string $status): string {
    if (strcasecmp($status, 'Resolved') === 0) {
        return 'neutral';
    }
    if (preg_match('/mediation|investigation|monitoring/i', $status) === 1) {
        return 'warning';
    }

    return 'danger';
};
?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/cases') ?>">← All cases</a>
    </div>
    <div class="toolbar__right">
        <button type="button" class="btn btn--secondary" disabled>Add note</button>
        <button type="button" class="btn btn--primary" disabled>Escalate</button>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <h2 class="card__title">Case overview</h2>
        <dl class="dl-grid">
            <dt>Case ID</dt>
            <dd><strong><?= esc($case['id']) ?></strong></dd>
            <dt>Title</dt>
            <dd><?= esc($case['title']) ?></dd>
            <dt>Linked work</dt>
            <dd><a href="<?= site_url('mockup/work/' . $case['work_id']) ?>"><?= esc($case['work_id']) ?></a></dd>
            <dt>Status</dt>
            <dd><?php echo view('components/badges', ['label' => (string) $case['status'], 'tone' => $caseStatusTone((string) $case['status'])]); ?></dd>
            <dt>Severity</dt>
            <dd>
                <?php if ($case['severity'] === 'High') : ?>
                    <span class="app-pill pill-danger"><?= esc($case['severity']) ?></span>
                <?php elseif ($case['severity'] === 'Medium') : ?>
                    <span class="app-pill pill-warning"><?= esc($case['severity']) ?></span>
                <?php else : ?>
                    <span class="app-pill pill-neutral"><?= esc($case['severity']) ?></span>
                <?php endif; ?>
            </dd>
            <dt>Opened</dt>
            <dd><?= esc($case['opened']) ?></dd>
            <dt>Assignee</dt>
            <dd><?= esc($case['assignee']) ?></dd>
            <dt>Jurisdiction</dt>
            <dd><?= esc($case['jurisdiction']) ?></dd>
        </dl>
        <p class="muted" style="margin-top: 1rem;"><?= esc($case['summary']) ?></p>
    </div>
    <div class="card">
        <h2 class="card__title">Timeline</h2>
        <div class="timeline">
            <?php foreach ($case['milestones'] as $m) : ?>
                <div class="timeline__item">
                    <div class="timeline__date"><?= esc($m['date']) ?></div>
                    <div class="timeline__label"><?= esc($m['label']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
