<?php
/** @var array $filters */
/** @var array $chartPayload */
$exportQuery = $exportQuery ?? '';
?>

<?= view('reports/_subnav') ?>
<?= view('reports/_filters', [
    'filters' => $filters,
    'workTypes' => $workTypes ?? [],
]) ?>
<?= view('reports/_export_bar', ['exportQuery' => $exportQuery]) ?>

<p class="page-intro">Cross-module snapshot for the selected window. Open a dedicated report for deeper charts and tables.</p>

<div class="grid grid--stats" style="margin-bottom:1.25rem;">
    <?= view('components/cards', ['kpi_label' => 'Total works', 'kpi_value' => (string) ($worksTotal ?? 0), 'kpi_hint' => 'Catalog (respects work type filter)', 'kpi_key' => 'default', 'kpi_href' => site_url('works')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Works created in range', 'kpi_value' => (string) ($worksCreated ?? 0), 'kpi_hint' => 'By created_at', 'kpi_key' => 'default', 'kpi_href' => site_url('reports/works')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Active licenses', 'kpi_value' => (string) (($licSnap['active'] ?? 0)), 'kpi_hint' => 'In force today', 'kpi_key' => 'default', 'kpi_href' => site_url('reports/licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Paid fees (sum)', 'kpi_value' => '$' . number_format((float) (($licRev['paid_sum'] ?? 0)), 2), 'kpi_hint' => 'Portfolio slice', 'kpi_key' => 'default', 'kpi_href' => site_url('reports/licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Detections in range', 'kpi_value' => $usageReady ? (string) ($usageCount ?? 0) : '—', 'kpi_hint' => 'Usage monitoring', 'kpi_key' => 'default', 'kpi_href' => site_url('reports/usage')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Cases in range', 'kpi_value' => $casesReady ? (string) ($casesTotal ?? 0) : '—', 'kpi_hint' => 'By case created_at', 'kpi_key' => 'default', 'kpi_href' => site_url('reports/cases')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Audit actions in range', 'kpi_value' => $auditReady ? (string) ($auditCount ?? 0) : '—', 'kpi_hint' => 'User activity', 'kpi_key' => 'default', 'kpi_href' => site_url('reports/activity')]) ?>
</div>

<div class="card chart-card">
    <h2 class="card__title">Works created (recent months)</h2>
    <p class="chart-card__hint">New works per calendar month; span follows your filter window (capped at 12 months).</p>
    <div class="chart-canvas-wrap">
        <canvas id="chartRepOverview" height="220" aria-label="Overview chart" role="img"></canvas>
    </div>
</div>

<?php if (! empty($chartPayload)) : ?>
<script type="application/json" id="chart-payload"><?= json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    var el = document.getElementById('chart-payload');
    var node = document.getElementById('chartRepOverview');
    if (!el || !node) return;
    var payload = {};
    try { payload = JSON.parse(el.textContent || '{}'); } catch (e) { return; }
    var tick = getComputedStyle(document.documentElement).getPropertyValue('--chart-tick').trim() || '#94a3b8';
    var grid = getComputedStyle(document.documentElement).getPropertyValue('--chart-grid').trim() || 'rgba(148,163,184,0.15)';
    new Chart(node, {
        type: 'line',
        data: {
            labels: payload.labels || [],
            datasets: [{
                label: 'New works',
                data: payload.overview || [],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: tick } } },
            scales: {
                x: { ticks: { color: tick }, grid: { color: grid } },
                y: { ticks: { color: tick, precision: 0 }, grid: { color: grid }, beginAtZero: true }
            }
        }
    });
});
</script>
<?php endif; ?>
