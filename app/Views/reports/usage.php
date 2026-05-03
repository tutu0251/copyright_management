<?php
$ready = $ready ?? false;
$exportQuery = $exportQuery ?? '';
$byUt = $byUt ?? [];
$byDt = $byDt ?? [];
?>

<?= view('reports/_subnav') ?>
<?= view('reports/_filters', ['filters' => $filters, 'workTypes' => $workTypes ?? []]) ?>
<?= view('reports/_export_bar', ['exportQuery' => $exportQuery]) ?>

<?php if (! $ready) : ?>
    <p class="muted">Usage monitoring schema is not available. Run migrations and seed usage data to enable this report.</p>
<?php else : ?>

<div class="grid grid--stats" style="margin-bottom:1rem;">
    <?= view('components/cards', ['kpi_label' => 'Detections in range', 'kpi_value' => (string) ($total ?? 0), 'kpi_hint' => 'By detected_at', 'kpi_key' => 'default', 'kpi_href' => site_url('usage-reports')]) ?>
</div>

<div class="chart-grid" style="margin-bottom:1.25rem;">
    <div class="card chart-card">
        <h2 class="card__title">Authorized vs suspected vs infringement</h2>
        <div class="chart-canvas-wrap"><canvas id="chartUsagePie" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Detections over time</h2>
        <div class="chart-canvas-wrap"><canvas id="chartUsageLine" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">By source type</h2>
        <div class="chart-canvas-wrap"><canvas id="chartUsageBar" height="240"></canvas></div>
    </div>
</div>

<div class="grid grid--dashboard-mid">
    <div class="card">
        <h2 class="card__title">Disposition</h2>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Count</th></tr></thead>
                <tbody>
                    <?php foreach (\App\Models\UsageReportModel::USAGE_TYPES as $u) : ?>
                        <tr>
                            <td><?= esc(\App\Models\UsageReportModel::usageTypeLabel($u)) ?></td>
                            <td><?= esc((string) (int) ($byUt[$u] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <h2 class="card__title">Source type</h2>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead><tr><th>Source</th><th>Count</th></tr></thead>
                <tbody>
                    <?php foreach (\App\Models\UsageReportModel::DETECTED_TYPES as $d) : ?>
                        <tr>
                            <td><?= esc(\App\Models\UsageReportModel::detectedTypeLabel($d)) ?></td>
                            <td><?= esc((string) (int) ($byDt[$d] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (! empty($chartPayload)) : ?>
<script type="application/json" id="chart-payload"><?= json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    var el = document.getElementById('chart-payload');
    if (!el) return;
    var p = {}; try { p = JSON.parse(el.textContent || '{}'); } catch (e) { return; }
    var tick = getComputedStyle(document.documentElement).getPropertyValue('--chart-tick').trim() || '#94a3b8';
    var grid = getComputedStyle(document.documentElement).getPropertyValue('--chart-grid').trim() || 'rgba(148,163,184,0.15)';
    var common = { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: tick } } } };
    var pie = document.getElementById('chartUsagePie');
    if (pie) new Chart(pie, { type: 'pie', data: { labels: p.pieLabels || [], datasets: [{ data: p.pieData || [], backgroundColor: ['#22c55e','#f59e0b','#ef4444'] }] }, options: { ...common } });
    var line = document.getElementById('chartUsageLine');
    if (line) new Chart(line, { type: 'line', data: { labels: p.lineLabels || [], datasets: [{ label: 'Detections', data: p.lineData || [], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.12)', fill: true, tension: 0.25 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 45 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
    var bar = document.getElementById('chartUsageBar');
    if (bar) new Chart(bar, { type: 'bar', data: { labels: p.barLabels || [], datasets: [{ label: 'Count', data: p.barData || [], backgroundColor: 'rgba(14,165,233,0.55)', borderRadius: 6 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 45 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
});
</script>
<?php endif; ?>

<?php endif; ?>
