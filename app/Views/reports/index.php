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

<p class="page-intro"><?= esc(lang('App.reports_intro')) ?></p>

<div class="grid grid--stats" style="margin-bottom:1.25rem;">
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_works_total'), 'kpi_value' => (string) ($worksTotal ?? 0), 'kpi_hint' => lang('App.reports_kpi_works_total_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('works')]) ?>
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_works_created'), 'kpi_value' => (string) ($worksCreated ?? 0), 'kpi_hint' => lang('App.reports_kpi_works_created_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('reports/works')]) ?>
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_lic_active'), 'kpi_value' => (string) (($licSnap['active'] ?? 0)), 'kpi_hint' => lang('App.reports_kpi_lic_active_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('reports/licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_paid_fees'), 'kpi_value' => '$' . number_format((float) (($licRev['paid_sum'] ?? 0)), 2), 'kpi_hint' => lang('App.reports_kpi_paid_fees_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('reports/licenses')]) ?>
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_detections'), 'kpi_value' => $usageReady ? (string) ($usageCount ?? 0) : '—', 'kpi_hint' => lang('App.reports_kpi_detections_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('reports/usage')]) ?>
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_cases_range'), 'kpi_value' => $casesReady ? (string) ($casesTotal ?? 0) : '—', 'kpi_hint' => lang('App.reports_kpi_cases_range_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('reports/cases')]) ?>
    <?= view('components/cards', ['kpi_label' => lang('App.reports_kpi_audit_range'), 'kpi_value' => $auditReady ? (string) ($auditCount ?? 0) : '—', 'kpi_hint' => lang('App.reports_kpi_audit_range_hint'), 'kpi_key' => 'default', 'kpi_href' => site_url('reports/activity')]) ?>
</div>

<div class="card chart-card">
    <h2 class="card__title"><?= esc(lang('App.reports_chart_works_created')) ?></h2>
    <p class="chart-card__hint"><?= esc(lang('App.reports_chart_works_created_hint')) ?></p>
    <div class="chart-canvas-wrap">
        <canvas id="chartRepOverview" height="220" aria-label="<?= esc(lang('App.reports_chart_overview_aria'), 'attr') ?>" role="img"></canvas>
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
                label: <?= json_encode(lang('App.reports_chart_dataset_new_works')) ?>,
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
