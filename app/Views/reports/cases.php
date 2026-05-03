<?php
$ready = $ready ?? false;
$openResolved = $openResolved ?? ['open' => 0, 'resolved' => 0];
$byPri = $byPri ?? [];
$exportQuery = $exportQuery ?? '';
?>

<?= view('reports/_subnav') ?>
<?= view('reports/_filters', [
    'filters' => $filters,
    'workTypes' => $workTypes ?? [],
    'caseStatuses' => $caseStatuses ?? \App\Models\InfringementCaseModel::ALL_STATUSES,
]) ?>
<?= view('reports/_export_bar', ['exportQuery' => $exportQuery]) ?>

<?php if (! $ready) : ?>
    <p class="muted">Infringement cases schema is not available.</p>
<?php else : ?>

<div class="grid grid--stats" style="margin-bottom:1rem;">
    <?= view('components/cards', ['kpi_label' => 'Cases opened in range', 'kpi_value' => (string) ($totalInRange ?? 0), 'kpi_hint' => 'created_at window', 'kpi_key' => 'default', 'kpi_href' => site_url('cases')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Avg resolution (days)', 'kpi_value' => $avgDays !== null ? (string) $avgDays : '—', 'kpi_hint' => 'Resolved cases in window', 'kpi_key' => 'default', 'kpi_href' => null]) ?>
</div>

<div class="chart-grid" style="margin-bottom:1.25rem;">
    <div class="card chart-card">
        <h2 class="card__title">Open vs resolved</h2>
        <div class="chart-canvas-wrap"><canvas id="chartCasePie" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Cases opened per day</h2>
        <div class="chart-canvas-wrap"><canvas id="chartCaseLine" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">By priority</h2>
        <div class="chart-canvas-wrap"><canvas id="chartCaseBar" height="240"></canvas></div>
    </div>
</div>

<div class="card">
    <h2 class="card__title">Priority breakdown</h2>
    <div class="table-wrap table-wrap--flush">
        <table class="data-table">
            <thead><tr><th>Priority</th><th>Count</th></tr></thead>
            <tbody>
                <?php foreach (\App\Models\InfringementCaseModel::PRIORITIES as $pr) : ?>
                    <tr>
                        <td><?= esc(\App\Models\InfringementCaseModel::priorityLabel($pr)) ?></td>
                        <td><?= esc((string) (int) ($byPri[$pr] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
    var pie = document.getElementById('chartCasePie');
    if (pie) new Chart(pie, { type: 'pie', data: { labels: p.pieLabels || [], datasets: [{ data: p.pieData || [], backgroundColor: ['#f59e0b','#22c55e'] }] }, options: { ...common } });
    var line = document.getElementById('chartCaseLine');
    if (line) new Chart(line, { type: 'line', data: { labels: p.lineLabels || [], datasets: [{ label: 'Opened', data: p.lineData || [], borderColor: '#fb7185', backgroundColor: 'rgba(251,113,133,0.12)', fill: true, tension: 0.25 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 45 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
    var bar = document.getElementById('chartCaseBar');
    if (bar) new Chart(bar, { type: 'bar', data: { labels: p.barLabels || [], datasets: [{ label: 'Cases', data: p.barData || [], backgroundColor: 'rgba(244,63,94,0.55)', borderRadius: 6 }] }, options: { ...common, scales: { x: { ticks: { color: tick }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
});
</script>
<?php endif; ?>

<?php endif; ?>
