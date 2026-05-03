<?php
$snap = $snap ?? ['active' => 0, 'expired' => 0, 'expiring_30' => 0];
$revenue = $revenue ?? ['paid_sum' => 0.0, 'unpaid_sum' => 0.0];
$byPay = $byPay ?? [];
$exportQuery = $exportQuery ?? '';
?>

<?= view('reports/_subnav') ?>
<?= view('reports/_filters', [
    'filters' => $filters,
    'workTypes' => $workTypes ?? [],
    'licenseStatuses' => $licenseStatuses ?? \App\Models\LicenseModel::LICENSE_STATUSES,
]) ?>
<?= view('reports/_export_bar', ['exportQuery' => $exportQuery]) ?>

<div class="grid grid--stats" style="margin-bottom:1rem;">
    <?= view('components/cards', ['kpi_label' => 'Active (in force)', 'kpi_value' => (string) ($snap['active'] ?? 0), 'kpi_hint' => 'Snapshot', 'kpi_key' => 'default', 'kpi_href' => site_url('licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Expired', 'kpi_value' => (string) ($snap['expired'] ?? 0), 'kpi_hint' => 'Past end date', 'kpi_key' => 'default', 'kpi_href' => site_url('licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Expiring ≤30 days', 'kpi_value' => (string) ($snap['expiring_30'] ?? 0), 'kpi_hint' => 'Upcoming renewals', 'kpi_key' => 'default', 'kpi_href' => site_url('licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Paid fees (sum)', 'kpi_value' => '$' . number_format((float) ($revenue['paid_sum'] ?? 0), 2), 'kpi_hint' => 'Matching filters', 'kpi_key' => 'default', 'kpi_href' => null]) ?>
    <?= view('components/cards', ['kpi_label' => 'Unpaid + partial (sum)', 'kpi_value' => '$' . number_format((float) ($revenue['unpaid_sum'] ?? 0), 2), 'kpi_hint' => 'Outstanding', 'kpi_key' => 'default', 'kpi_href' => null]) ?>
</div>

<div class="chart-grid" style="margin-bottom:1.25rem;">
    <div class="card chart-card">
        <h2 class="card__title">Status snapshot</h2>
        <div class="chart-canvas-wrap"><canvas id="chartLicPie" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Licenses created per day</h2>
        <div class="chart-canvas-wrap"><canvas id="chartLicLine" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Payment status counts</h2>
        <div class="chart-canvas-wrap"><canvas id="chartLicBar" height="240"></canvas></div>
    </div>
</div>

<div class="card">
    <h2 class="card__title">Payment status</h2>
    <div class="table-wrap table-wrap--flush">
        <table class="data-table">
            <thead><tr><th>Status</th><th>Licenses</th></tr></thead>
            <tbody>
                <?php foreach (\App\Models\LicenseModel::PAYMENT_STATUSES as $ps) : ?>
                    <tr>
                        <td><?= esc(\App\Models\LicenseModel::paymentLabel($ps)) ?></td>
                        <td><?= esc((string) (int) ($byPay[$ps] ?? 0)) ?></td>
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
    var pie = document.getElementById('chartLicPie');
    if (pie) new Chart(pie, { type: 'pie', data: { labels: p.pieLabels || [], datasets: [{ data: p.pieData || [], backgroundColor: ['#22c55e','#64748b','#f59e0b'] }] }, options: { ...common } });
    var line = document.getElementById('chartLicLine');
    if (line) new Chart(line, { type: 'line', data: { labels: p.lineLabels || [], datasets: [{ label: 'Created', data: p.lineData || [], borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.15)', fill: true, tension: 0.25 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 45 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
    var bar = document.getElementById('chartLicBar');
    if (bar) new Chart(bar, { type: 'bar', data: { labels: p.barLabels || [], datasets: [{ label: 'Licenses', data: p.barData || [], backgroundColor: 'rgba(34,197,94,0.55)', borderRadius: 6 }] }, options: { ...common, scales: { x: { ticks: { color: tick }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
});
</script>
<?php endif; ?>
