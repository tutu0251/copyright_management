<?php
$ready = $ready ?? false;
$topUsers = $topUsers ?? [];
$actions = $actions ?? [];
$exportQuery = $exportQuery ?? '';
?>

<?= view('reports/_subnav') ?>
<?= view('reports/_filters', ['filters' => $filters, 'workTypes' => $workTypes ?? []]) ?>
<?= view('reports/_export_bar', ['exportQuery' => $exportQuery]) ?>

<?php if (! $ready) : ?>
    <p class="muted">Audit log is not available.</p>
<?php else : ?>

<div class="chart-grid" style="margin-bottom:1.25rem;">
    <div class="card chart-card">
        <h2 class="card__title">Activity trend</h2>
        <div class="chart-canvas-wrap"><canvas id="chartActLine" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Most active users</h2>
        <div class="chart-canvas-wrap"><canvas id="chartActBar" height="240"></canvas></div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Top action types</h2>
        <div class="chart-canvas-wrap"><canvas id="chartActPie" height="240"></canvas></div>
    </div>
</div>

<div class="grid grid--dashboard-mid">
    <div class="card">
        <h2 class="card__title">Users</h2>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead><tr><th>User</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if ($topUsers === []) : ?>
                        <tr><td colspan="2" class="muted">No audit rows in this range.</td></tr>
                    <?php else : ?>
                        <?php foreach ($topUsers as $u) : ?>
                            <?php $nm = (string) ($u['display_name'] ?? ''); if ($nm === '') { $nm = (string) ($u['email'] ?? '—'); } ?>
                            <tr>
                                <td><?= esc($nm) ?></td>
                                <td><?= esc((string) (int) ($u['action_count'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <h2 class="card__title">Action types</h2>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead><tr><th>Action</th><th>Count</th></tr></thead>
                <tbody>
                    <?php if ($actions === []) : ?>
                        <tr><td colspan="2" class="muted">No data.</td></tr>
                    <?php else : ?>
                        <?php foreach ($actions as $a) : ?>
                            <tr>
                                <td><?= esc((string) ($a['action_type'] ?? '')) ?></td>
                                <td><?= esc((string) (int) ($a['c'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
    var line = document.getElementById('chartActLine');
    if (line) new Chart(line, { type: 'line', data: { labels: p.lineLabels || [], datasets: [{ label: 'Actions', data: p.lineData || [], borderColor: '#a78bfa', backgroundColor: 'rgba(167,139,250,0.15)', fill: true, tension: 0.25 }] }, options: { ...common, scales: { x: { ticks: { color: tick, maxRotation: 45 }, grid: { color: grid } }, y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } } } } });
    var bar = document.getElementById('chartActBar');
    if (bar) new Chart(bar, { type: 'bar', data: { labels: p.barLabels || [], datasets: [{ label: 'Actions', data: p.barData || [], backgroundColor: 'rgba(167,139,250,0.55)', borderRadius: 6 }] }, options: { ...common, indexAxis: 'y', scales: { x: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } }, y: { ticks: { color: tick }, grid: { color: grid } } } } });
    var pie = document.getElementById('chartActPie');
    if (pie) new Chart(pie, { type: 'pie', data: { labels: p.pieLabels || [], datasets: [{ data: p.pieData || [], backgroundColor: ['#6366f1','#22c55e','#f59e0b','#ec4899','#06b6d4','#eab308','#94a3b8','#64748b'] }] }, options: { ...common } });
});
</script>
<?php endif; ?>

<?php endif; ?>
