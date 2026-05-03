<?php
$exportQuery = $exportQuery ?? '';
$byType = $byType ?? [];
$byOwner = $byOwner ?? [];
?>

<?= view('reports/_subnav') ?>
<?= view('reports/_filters', ['filters' => $filters, 'workTypes' => $workTypes ?? []]) ?>
<?= view('reports/_export_bar', ['exportQuery' => $exportQuery]) ?>

<div class="grid grid--stats" style="margin-bottom:1rem;">
    <?= view('components/cards', ['kpi_label' => 'Total works', 'kpi_value' => (string) ($total ?? 0), 'kpi_hint' => 'Catalog', 'kpi_key' => 'works', 'kpi_href' => site_url('works')]) ?>
    <?= view('components/cards', ['kpi_label' => 'Created in range', 'kpi_value' => (string) ($createdIn ?? 0), 'kpi_hint' => 'created_at', 'kpi_key' => 'default', 'kpi_href' => null]) ?>
</div>

<div class="chart-grid" style="margin-bottom:1.25rem;">
    <div class="card chart-card">
        <h2 class="card__title">Works by type</h2>
        <div class="chart-canvas-wrap"><canvas id="chartWorksPie" height="240" aria-label="Types pie"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Works created over time</h2>
        <div class="chart-canvas-wrap"><canvas id="chartWorksLine" height="240" aria-label="Works timeline"></canvas></div>
    </div>
    <div class="card chart-card" style="grid-column: 1 / -1;">
        <h2 class="card__title">Top owners by linked works</h2>
        <div class="chart-canvas-wrap"><canvas id="chartWorksBar" height="260" aria-label="Owners bar"></canvas></div>
    </div>
</div>

<div class="card">
    <h2 class="card__title">Works by type (table)</h2>
    <div class="table-wrap table-wrap--flush">
        <table class="data-table">
            <thead><tr><th>Type</th><th>Count</th></tr></thead>
            <tbody>
                <?php if ($byType === []) : ?>
                    <tr><td colspan="2" class="muted">No data for this range.</td></tr>
                <?php else : ?>
                    <?php foreach ($byType as $r) : ?>
                        <tr>
                            <td><?= esc((string) ($r['work_type'] ?? '')) ?></td>
                            <td><?= esc((string) (int) ($r['c'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <h2 class="card__title">Owners (top 25)</h2>
    <div class="table-wrap table-wrap--flush">
        <table class="data-table">
            <thead><tr><th>Owner</th><th>Works</th><th></th></tr></thead>
            <tbody>
                <?php if ($byOwner === []) : ?>
                    <tr><td colspan="3" class="muted">No owner links or no matches.</td></tr>
                <?php else : ?>
                    <?php foreach ($byOwner as $o) : ?>
                        <?php $oid = (int) ($o['owner_id'] ?? 0); ?>
                        <tr>
                            <td><?= esc((string) ($o['name'] ?? '')) ?></td>
                            <td><?= esc((string) (int) ($o['work_count'] ?? 0)) ?></td>
                            <td class="table-actions"><?php if ($oid > 0) : ?><a class="btn btn--ghost btn--sm" href="<?= site_url('owners/' . $oid) ?>">View</a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    var pie = document.getElementById('chartWorksPie');
    if (pie) new Chart(pie, { type: 'pie', data: { labels: p.pieLabels || [], datasets: [{ data: p.pieData || [], backgroundColor: ['#6366f1','#22c55e','#f59e0b','#ec4899','#06b6d4','#a855f7'] }] }, options: { ...common } });
    var line = document.getElementById('chartWorksLine');
    if (line) new Chart(line, { type: 'line', data: { labels: p.lineLabels || [], datasets: [{ label: 'Created', data: p.lineData || [], borderColor: '#38bdf8', backgroundColor: 'rgba(56,189,248,0.12)', fill: true, tension: 0.25 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 45 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
    var bar = document.getElementById('chartWorksBar');
    if (bar) new Chart(bar, { type: 'bar', data: { labels: p.barLabels || [], datasets: [{ label: 'Works', data: p.barData || [], backgroundColor: 'rgba(99,102,241,0.55)', borderRadius: 6 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 60 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
});
</script>
<?php endif; ?>
