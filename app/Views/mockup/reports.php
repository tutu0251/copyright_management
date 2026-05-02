<p class="page-intro">Executive rollups and export-ready summaries. Charts read the same static monthly fixtures as the dashboard.</p>

<div class="grid grid--2">
    <div class="card chart-card">
        <h2 class="card__title">Registrations by month</h2>
        <p class="chart-card__hint">Mock counts aligned to the dashboard series.</p>
        <div class="chart-canvas-wrap">
            <canvas id="repChartWorks" height="200" aria-label="Registrations chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Revenue curve</h2>
        <p class="chart-card__hint">Area-style line — same totals as KPI revenue fixtures.</p>
        <div class="chart-canvas-wrap">
            <canvas id="repChartRevenue" height="200" aria-label="Revenue chart" role="img"></canvas>
        </div>
    </div>
</div>

<div class="toolbar" style="margin-top: 1rem;">
    <div class="toolbar__left">
        <button type="button" class="btn btn--secondary" disabled>Export PDF</button>
        <button type="button" class="btn btn--secondary" disabled>Schedule digest</button>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/usage-reports') ?>">Open usage detail</a>
        <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup') ?>">Back to dashboard</a>
    </div>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Usage &amp; channel mix</h2>
    <p class="muted" style="margin: 0 0 0.75rem;">Detailed line items live on the usage report screen.</p>
    <?= $this->include('components/table') ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Period</th>
                <th>Work</th>
                <th>Channel</th>
                <th>Impressions</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usageRows as $r) : ?>
                <tr>
                    <td><?= esc($r['period']) ?></td>
                    <td><?= esc($r['work']) ?></td>
                    <td><?= esc($r['channel']) ?></td>
                    <td><?= esc($r['impressions']) ?></td>
                    <td><?= esc($r['revenue']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->include('components/table_end') ?>
</div>
