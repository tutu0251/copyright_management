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

<p class="page-intro">Enforcement pipeline — table view with an optional board preview. Drag-and-drop will connect to workflow services later.</p>

<div class="toolbar">
    <div class="toolbar__left">
        <button type="button" class="btn btn--primary" disabled>Open new case</button>
        <button type="button" class="btn btn--secondary" disabled>Import evidence</button>
    </div>
    <div class="toolbar__right">
        <select class="select" aria-label="Severity filter (mock)">
            <option>All severities</option>
            <option>High</option>
            <option>Medium</option>
            <option>Low</option>
        </select>
        <select class="select" aria-label="Assignee filter (mock)">
            <option>Assignee: All</option>
            <option>Jordan Lee</option>
            <option>Unassigned</option>
        </select>
    </div>
</div>

<div class="ui-board" aria-label="Case pipeline preview (mock)">
    <div class="ui-board__col">
        <h3 class="ui-board__title">Investigation</h3>
        <?php foreach ($cases as $c) : ?>
            <?php if (strcasecmp((string) $c['status'], 'Investigation') !== 0) {
                continue;
            } ?>
            <div class="ui-board__card">
                <strong><?= esc($c['id']) ?></strong> — <?= esc($c['title']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="ui-board__col">
        <h3 class="ui-board__title">Mediation</h3>
        <?php foreach ($cases as $c) : ?>
            <?php if (strcasecmp((string) $c['status'], 'Mediation') !== 0) {
                continue;
            } ?>
            <div class="ui-board__card">
                <strong><?= esc($c['id']) ?></strong> — <?= esc($c['title']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="ui-board__col">
        <h3 class="ui-board__title">Monitoring</h3>
        <?php foreach ($cases as $c) : ?>
            <?php if (strcasecmp((string) $c['status'], 'Monitoring') !== 0) {
                continue;
            } ?>
            <div class="ui-board__card">
                <strong><?= esc($c['id']) ?></strong> — <?= esc($c['title']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="ui-board__col">
        <h3 class="ui-board__title">Resolved</h3>
        <?php foreach ($cases as $c) : ?>
            <?php if (strcasecmp((string) $c['status'], 'Resolved') !== 0) {
                continue;
            } ?>
            <div class="ui-board__card">
                <strong><?= esc($c['id']) ?></strong> — <?= esc($c['title']) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th class="sortable"><span class="th-sort">Case ID <span class="sort-ico">↕</span></span></th>
            <th>Title</th>
            <th>Work</th>
            <th>Severity</th>
            <th>Status</th>
            <th class="sortable"><span class="th-sort">Opened <span class="sort-ico">↕</span></span></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cases as $c) : ?>
            <tr>
                <td><?= esc($c['id']) ?></td>
                <td><strong><?= esc($c['title']) ?></strong></td>
                <td><a href="<?= site_url('mockup/work/' . $c['work_id']) ?>"><?= esc($c['work_id']) ?></a></td>
                <td>
                    <?php
                    $sev = (string) $c['severity'];
                    $stn = $sev === 'High' ? 'danger' : ($sev === 'Medium' ? 'warning' : 'neutral');
                    echo view('components/badges', ['label' => $sev, 'tone' => $stn]);
                    ?>
                </td>
                <td><?php echo view('components/badges', ['label' => (string) $c['status'], 'tone' => $caseStatusTone((string) $c['status'])]); ?></td>
                <td><?= esc($c['opened']) ?></td>
                <td class="table-actions">
                    <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/case/' . $c['id']) ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>
