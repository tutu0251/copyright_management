<p class="page-intro">Web and marketplace monitoring jobs — static scan summaries. Connectors will call your infringement vendor APIs later.</p>

<div class="toolbar">
    <div class="toolbar__left">
        <button type="button" class="btn btn--primary" disabled>Run scan now</button>
        <button type="button" class="btn btn--secondary" disabled>Configure sources</button>
    </div>
    <div class="toolbar__right">
        <select class="select" aria-label="Scan window (mock)">
            <option>Last 30 days</option>
            <option>Last 90 days</option>
            <option>Year to date</option>
        </select>
    </div>
</div>

<div class="grid grid--stats" style="margin-bottom: 1rem;">
    <div class="ui-kpi" data-kpi="scan">
        <div class="ui-kpi__top"><span class="ui-kpi__label">Last scan</span></div>
        <div class="ui-kpi__value">14</div>
        <p class="ui-kpi__hint muted">Potential matches (latest completed job)</p>
    </div>
    <div class="ui-kpi" data-kpi="coverage">
        <div class="ui-kpi__top"><span class="ui-kpi__label">Coverage</span></div>
        <div class="ui-kpi__value">38</div>
        <p class="ui-kpi__hint muted">Domains &amp; channels in rotation (mock)</p>
    </div>
    <div class="ui-kpi" data-kpi="sla">
        <div class="ui-kpi__top"><span class="ui-kpi__label">SLA</span></div>
        <div class="ui-kpi__value">99.1%</div>
        <p class="ui-kpi__hint muted">Successful crawl completion (sample)</p>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Scan ID</th>
            <th>Started</th>
            <th>Status</th>
            <th>Matches</th>
            <th>Risk</th>
            <th>Top hit</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($scans as $s) : ?>
            <tr>
                <td><strong><?= esc($s['scan_id']) ?></strong></td>
                <td><?= esc($s['started']) ?></td>
                <td>
                    <?php
                    $st = (string) $s['status'];
                    $tone = strcasecmp($st, 'Complete') === 0 ? 'success' : (strcasecmp($st, 'Running') === 0 ? 'warning' : 'neutral');
                    echo view('components/badges', ['label' => $st, 'tone' => $tone]);
                    ?>
                </td>
                <td><?= esc($s['matches']) ?></td>
                <td>
                    <?php
                    $sev = (string) $s['severity'];
                    $tone = $sev === 'High' ? 'danger' : ($sev === 'Medium' ? 'warning' : ($sev === 'Low' ? 'success' : 'neutral'));
                    echo view('components/badges', ['label' => $sev !== '' ? $sev : '—', 'tone' => $tone]);
                    ?>
                </td>
                <td><?= esc($s['top_hit']) ?></td>
                <td class="table-actions">
                    <button type="button" class="btn btn--ghost btn--sm" disabled>Review queue</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Queue preview (skeleton)</h2>
    <p class="muted" style="margin: 0 0 0.75rem;">Loading state mock — swap with live rows from the scan worker.</p>
    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
        <div class="ui-skeleton" style="height: 14px; width: 70%;"></div>
        <div class="ui-skeleton" style="height: 14px; width: 55%;"></div>
        <div class="ui-skeleton" style="height: 14px; width: 80%;"></div>
    </div>
</div>
