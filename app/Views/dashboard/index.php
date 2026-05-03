<?php
$dashboardUrl       = $dashboardUrl ?? site_url('dashboard');
$dashboardRangeDays = (int) ($dashboardRangeDays ?? 30);
$dashboardWorkType  = (string) ($dashboardWorkType ?? '');
$dashboardWorkTypes = $dashboardWorkTypes ?? [];
$extraStats         = $extraStats ?? [];
$topLicensedWorks   = $topLicensedWorks ?? [];
$topLicensees       = $topLicensees ?? [];
$topReportedWorks   = $topReportedWorks ?? [];
?>

<p class="page-intro">Analytics from your live catalog: KPIs reflect portfolio totals; charts use the last <?= (int) (count(($chartPayload ?? [])['labels'] ?? []) ?: 12) ?> calendar months. Filters narrow lists, recent activity, usage highlights, and chart series tied to works.</p>

<form class="dashboard-filters" method="get" action="<?= esc($dashboardUrl, 'attr') ?>" style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;margin-bottom:1.25rem;">
    <div>
        <label for="dash-range" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;">Date range</label>
        <select id="dash-range" name="range" class="input-like" style="min-width:11rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
            <option value="7" <?= $dashboardRangeDays === 7 ? 'selected' : '' ?>>Last 7 days</option>
            <option value="30" <?= $dashboardRangeDays === 30 ? 'selected' : '' ?>>Last 30 days</option>
            <option value="90" <?= $dashboardRangeDays === 90 ? 'selected' : '' ?>>Last 90 days</option>
        </select>
    </div>
    <?php if ($dashboardWorkTypes !== []) : ?>
        <div>
            <label for="dash-work-type" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;">Work type</label>
            <select id="dash-work-type" name="work_type" class="input-like" style="min-width:12rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
                <option value="">All types</option>
                <?php foreach ($dashboardWorkTypes as $wt) : ?>
                    <option value="<?= esc($wt, 'attr') ?>" <?= $dashboardWorkType === $wt ? 'selected' : '' ?>><?= esc($wt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <button type="submit" class="btn btn--secondary btn--sm">Apply</button>
</form>

<h2 class="card__title" style="margin:0 0 0.5rem;font-size:1rem;">Key metrics</h2>
<div class="grid grid--stats">
    <?php foreach ($stats as $s) : ?>
        <?php
        echo view('components/cards', [
            'kpi_label' => $s['label'],
            'kpi_value' => $s['value'],
            'kpi_hint'  => $s['hint'],
            'kpi_key'   => $s['kpi'] ?? 'default',
            'kpi_href'  => $s['kpi_href'] ?? null,
        ]);
        ?>
    <?php endforeach; ?>
</div>

<?php if ($extraStats !== []) : ?>
    <h2 class="card__title" style="margin:1.25rem 0 0.5rem;font-size:1rem;">More signals</h2>
    <div class="grid grid--stats">
        <?php foreach ($extraStats as $s) : ?>
            <?php
            echo view('components/cards', [
                'kpi_label' => $s['label'],
                'kpi_value' => $s['value'],
                'kpi_hint'  => $s['hint'],
                'kpi_key'   => $s['kpi'] ?? 'default',
                'kpi_href'  => $s['kpi_href'] ?? null,
            ]);
            ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="chart-grid" style="margin-top:1.25rem;">
    <div class="card chart-card">
        <h2 class="card__title">Works growth</h2>
        <p class="chart-card__hint">New works per month (by created_at<?= $dashboardWorkType !== '' ? ', filtered type' : '' ?>).</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartWorksGrowth" height="220" aria-label="Works growth chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">License activity</h2>
        <p class="chart-card__hint">Active in force on month-end vs licenses whose end date falls in that month.</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartLicenseActivity" height="220" aria-label="License activity chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Infringement trend</h2>
        <p class="chart-card__hint">Cases opened per month vs resolved per month.</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartInfringement" height="220" aria-label="Infringement trend chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Revenue trend</h2>
        <p class="chart-card__hint">Paid license fees summed by month of license creation<?= $dashboardWorkType !== '' ? ' (filtered work type)' : '' ?>.</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartRevenue" height="220" aria-label="Revenue trend chart" role="img"></canvas>
        </div>
    </div>
</div>

<div class="grid grid--dashboard-mid" style="margin-top: 1.25rem;">
    <div class="card">
        <h2 class="card__title">Activity feed</h2>
        <?php $auditFeedLive = $auditFeedLive ?? false; ?>
        <?php if ($auditFeedLive) : ?>
            <p class="muted" style="margin: 0 0 0.75rem; font-size: 0.9rem;">Latest audit events in the selected range. <a href="<?= site_url('activities') ?>">View all</a></p>
            <?php if (($activity ?? []) !== []) : ?>
                <div class="table-wrap table-wrap--flush">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activity as $row) : ?>
                                <tr>
                                    <td class="muted" style="white-space:nowrap;"><?= esc((string) ($row['time'] ?? '')) ?></td>
                                    <td><?= esc((string) ($row['actor'] ?? ($row['text'] ?? ''))) ?></td>
                                    <td><?= esc((string) ($row['action'] ?? '')) ?></td>
                                    <td><?= esc((string) ($row['entity'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="muted" style="margin: 0;">No audit activity in this range.</p>
            <?php endif; ?>
        <?php else : ?>
            <p class="muted" style="margin: 0 0 0.75rem; font-size: 0.9rem;">Audit log is not available until the migration is applied.</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2 class="card__title">Quick actions</h2>
        <p class="muted" style="margin: 0 0 0.75rem;">Shortcuts for common tasks.</p>
        <div class="quick-actions">
            <a class="btn btn--primary" href="<?= site_url('works/create') ?>">Register work</a>
            <a class="btn btn--secondary" href="<?= site_url('licenses/create') ?>">Create license</a>
            <a class="btn btn--secondary" href="<?= site_url('usage-reports/create') ?>">Report usage</a>
        </div>
        <h3 class="card__title" style="margin-top: 1.25rem;">Pinned assets</h3>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pinnedWorks === []) : ?>
                        <tr>
                            <td colspan="2" class="muted">No works in the catalog yet. Register an asset or seed sample data.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($pinnedWorks as $w) : ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('works/' . $w['work_id']) ?>"><?= esc($w['title']) ?></a>
                                </td>
                                <td>
                                    <?php
                                    $st = (string) $w['copyright_status'];
                                    $tone = preg_match('/pending|audit/i', $st) ? 'warning' : (preg_match('/registered/i', $st) ? 'success' : 'neutral');
                                    echo view('components/badges', ['label' => $st, 'tone' => $tone]);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 0.85rem;">
            <a class="btn btn--ghost btn--sm" href="<?= site_url('works') ?>">Open assets</a>
        </div>
    </div>
</div>

<div class="grid grid--dashboard-mid" style="margin-top: 1.25rem;">
    <div class="card">
        <h2 class="card__title">Top works by licenses</h2>
        <p class="muted" style="margin:0 0 0.75rem;font-size:0.9rem;">Licenses created in the selected range, grouped by work.</p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Work</th>
                        <th>Licenses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topLicensedWorks === []) : ?>
                        <tr><td colspan="2" class="muted">No data for this filter.</td></tr>
                    <?php else : ?>
                        <?php foreach ($topLicensedWorks as $tw) : ?>
                            <?php $wid = (int) ($tw['work_id'] ?? 0); ?>
                            <tr>
                                <td><?php if ($wid > 0) : ?><a href="<?= site_url('works/' . $wid) ?>"><?= esc((string) ($tw['title'] ?? '')) ?></a><?php else : ?><?= esc((string) ($tw['title'] ?? '')) ?><?php endif; ?></td>
                                <td><?= esc((string) (int) ($tw['license_count'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <h2 class="card__title">Most active licensees</h2>
        <p class="muted" style="margin:0 0 0.75rem;font-size:0.9rem;">New licenses recorded in the selected range.</p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Licensee</th>
                        <th>New licenses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topLicensees === []) : ?>
                        <tr><td colspan="2" class="muted">No data for this filter.</td></tr>
                    <?php else : ?>
                        <?php foreach ($topLicensees as $tl) : ?>
                            <?php $lid = (int) ($tl['licensee_id'] ?? 0); ?>
                            <tr>
                                <td><?php if ($lid > 0) : ?><a href="<?= site_url('licensees/' . $lid) ?>"><?= esc((string) ($tl['name'] ?? '')) ?></a><?php else : ?><?= esc((string) ($tl['name'] ?? '')) ?><?php endif; ?></td>
                                <td><?= esc((string) (int) ($tl['license_count'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <h2 class="card__title">Most reported works</h2>
        <p class="muted" style="margin:0 0 0.75rem;font-size:0.9rem;">Usage reports by detected date in the selected range.</p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Work</th>
                        <th>Reports</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topReportedWorks === []) : ?>
                        <tr><td colspan="2" class="muted">No usage reports or no matches for this filter.</td></tr>
                    <?php else : ?>
                        <?php foreach ($topReportedWorks as $tr) : ?>
                            <?php $rid = (int) ($tr['work_id'] ?? 0); ?>
                            <tr>
                                <td><?php if ($rid > 0) : ?><a href="<?= site_url('works/' . $rid) ?>"><?= esc((string) ($tr['title'] ?? '')) ?></a><?php else : ?><?= esc((string) ($tr['title'] ?? '')) ?><?php endif; ?></td>
                                <td><?= esc((string) (int) ($tr['report_count'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$recentUsageDetections = $recentUsageDetections ?? [];
?>
<?php
$caseStatusBreakdown = $caseStatusBreakdown ?? [];
$casesSchemaOk = $casesSchemaOk ?? false;
?>
<?php if ($casesSchemaOk && $caseStatusBreakdown !== []) : ?>
    <div class="card" style="margin-top: 1.25rem;">
        <h2 class="card__title">Cases by status</h2>
        <p class="muted" style="margin-top: 0;">Live counts from your infringement case registry.</p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (\App\Models\InfringementCaseModel::ALL_STATUSES as $st) : ?>
                        <?php $cnt = (int) ($caseStatusBreakdown[$st] ?? 0); ?>
                        <tr>
                            <td><?= esc(\App\Models\InfringementCaseModel::statusLabel($st)) ?></td>
                            <td>
                                <?php if ($cnt > 0) : ?>
                                    <a href="<?= site_url('cases') ?>?case_status=<?= esc($st, 'url') ?>"><?= esc((string) $cnt) ?></a>
                                <?php else : ?>
                                    <?= esc((string) $cnt) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 0.85rem;">
            <a class="btn btn--ghost btn--sm" href="<?= site_url('cases') ?>">Open cases</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($recentUsageDetections !== []) : ?>
    <div class="card" style="margin-top: 1.25rem;">
        <h2 class="card__title">Recent usage detections</h2>
        <p class="muted" style="margin-top: 0;">Latest monitoring entries in the selected date range.</p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Work</th>
                        <th>Source</th>
                        <th>Detected</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsageDetections as $u) : ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <tr>
                            <td><?= esc((string) ($u['work_title'] ?? '')) ?></td>
                            <td><?= esc((string) ($u['source'] ?? '')) ?></td>
                            <td><?= esc((string) ($u['detected_at'] ?? '')) ?></td>
                            <td>
                                <?= view('components/badges', [
                                    'label' => (string) ($u['usage_label'] ?? ''),
                                    'tone'  => (string) ($u['usage_tone'] ?? 'neutral'),
                                ]) ?>
                            </td>
                            <td class="table-actions">
                                <?php if ($uid > 0) : ?>
                                    <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports/' . $uid) ?>">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 0.85rem;">
            <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports') ?>">Open usage reports</a>
        </div>
    </div>
<?php endif; ?>
