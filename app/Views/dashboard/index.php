<?php
$dashboardUrl       = $dashboardUrl ?? site_url('dashboard');
$dashboardRangeDays = (int) ($dashboardRangeDays ?? 30);
$dashboardWorkType  = (string) ($dashboardWorkType ?? '');
$dashboardWorkTypes = $dashboardWorkTypes ?? [];
$extraStats         = $extraStats ?? [];
$topLicensedWorks   = $topLicensedWorks ?? [];
$topLicensees       = $topLicensees ?? [];
$topReportedWorks   = $topReportedWorks ?? [];
$chartMonthCount    = (int) (count(($chartPayload ?? [])['labels'] ?? []) ?: 12);
?>

<p class="page-intro"><?= esc(lang('App.dashboard_intro', ['chart_months' => (string) $chartMonthCount])) ?></p>

<form class="dashboard-filters" method="get" action="<?= esc($dashboardUrl, 'attr') ?>" style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;margin-bottom:1.25rem;">
    <div>
        <label for="dash-range" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.date_range')) ?></label>
        <select id="dash-range" name="range" class="input-like" style="min-width:11rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
            <option value="7" <?= $dashboardRangeDays === 7 ? 'selected' : '' ?>><?= esc(lang('App.date_range_7')) ?></option>
            <option value="30" <?= $dashboardRangeDays === 30 ? 'selected' : '' ?>><?= esc(lang('App.date_range_30')) ?></option>
            <option value="90" <?= $dashboardRangeDays === 90 ? 'selected' : '' ?>><?= esc(lang('App.date_range_90')) ?></option>
        </select>
    </div>
    <?php if ($dashboardWorkTypes !== []) : ?>
        <div>
            <label for="dash-work-type" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.work_type')) ?></label>
            <select id="dash-work-type" name="work_type" class="input-like" style="min-width:12rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
                <option value=""><?= esc(lang('App.all_types')) ?></option>
                <?php foreach ($dashboardWorkTypes as $wt) : ?>
                    <option value="<?= esc($wt, 'attr') ?>" <?= $dashboardWorkType === $wt ? 'selected' : '' ?>><?= esc($wt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <button type="submit" class="btn btn--secondary btn--sm"><?= esc(lang('App.action_apply')) ?></button>
</form>

<h2 class="card__title" style="margin:0 0 0.5rem;font-size:1rem;"><?= esc(lang('App.dashboard_key_metrics')) ?></h2>
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
    <h2 class="card__title" style="margin:1.25rem 0 0.5rem;font-size:1rem;"><?= esc(lang('App.dashboard_more_signals')) ?></h2>
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
        <h2 class="card__title"><?= esc(lang('App.dashboard_works_growth')) ?></h2>
        <p class="chart-card__hint"><?= esc(lang('App.dashboard_works_growth_hint', ['filter' => $dashboardWorkType !== '' ? lang('App.dashboard_filtered_type') : ''])) ?></p>
        <div class="chart-canvas-wrap">
            <canvas id="chartWorksGrowth" height="220" aria-label="<?= esc(lang('App.dashboard_chart_works'), 'attr') ?>" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title"><?= esc(lang('App.dashboard_license_activity')) ?></h2>
        <p class="chart-card__hint"><?= esc(lang('App.dashboard_license_activity_hint')) ?></p>
        <div class="chart-canvas-wrap">
            <canvas id="chartLicenseActivity" height="220" aria-label="<?= esc(lang('App.dashboard_chart_license'), 'attr') ?>" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title"><?= esc(lang('App.dashboard_infringement_trend')) ?></h2>
        <p class="chart-card__hint"><?= esc(lang('App.dashboard_infringement_hint')) ?></p>
        <div class="chart-canvas-wrap">
            <canvas id="chartInfringement" height="220" aria-label="<?= esc(lang('App.dashboard_chart_infringement'), 'attr') ?>" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title"><?= esc(lang('App.dashboard_revenue_trend')) ?></h2>
        <p class="chart-card__hint"><?= esc(lang('App.dashboard_revenue_hint', ['filter' => $dashboardWorkType !== '' ? lang('App.dashboard_filtered_work_type') : ''])) ?></p>
        <div class="chart-canvas-wrap">
            <canvas id="chartRevenue" height="220" aria-label="<?= esc(lang('App.dashboard_chart_revenue'), 'attr') ?>" role="img"></canvas>
        </div>
    </div>
</div>

<div class="grid grid--dashboard-mid" style="margin-top: 1.25rem;">
    <div class="card">
        <h2 class="card__title"><?= esc(lang('App.dashboard_activity_feed')) ?></h2>
        <?php $auditFeedLive = $auditFeedLive ?? false; ?>
        <?php if ($auditFeedLive) : ?>
            <p class="muted" style="margin: 0 0 0.75rem; font-size: 0.9rem;"><?= esc(lang('App.dashboard_activity_intro')) ?> <a href="<?= site_url('activities') ?>"><?= esc(lang('App.dashboard_view_all')) ?></a></p>
            <?php if (($activity ?? []) !== []) : ?>
                <div class="table-wrap table-wrap--flush">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= esc(lang('App.dashboard_table_when')) ?></th>
                                <th><?= esc(lang('App.dashboard_table_user')) ?></th>
                                <th><?= esc(lang('App.dashboard_table_action')) ?></th>
                                <th><?= esc(lang('App.dashboard_table_entity')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activity as $row) : ?>
                                <tr>
                                    <td class="muted" style="white-space:nowrap;"><?= esc(localized_date((string) ($row['time'] ?? ''), true)) ?></td>
                                    <td><?= esc((string) ($row['actor'] ?? ($row['text'] ?? ''))) ?></td>
                                    <td><?= esc((string) ($row['action'] ?? '')) ?></td>
                                    <td><?= esc((string) ($row['entity'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="muted" style="margin: 0;"><?= esc(lang('App.dashboard_audit_no_activity')) ?></p>
            <?php endif; ?>
        <?php else : ?>
            <p class="muted" style="margin: 0 0 0.75rem; font-size: 0.9rem;"><?= esc(lang('App.dashboard_audit_migration_needed')) ?></p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2 class="card__title"><?= esc(lang('App.dashboard_quick_actions')) ?></h2>
        <p class="muted" style="margin: 0 0 0.75rem;"><?= esc(lang('App.dashboard_quick_intro')) ?></p>
        <div class="quick-actions">
            <?php if (user_can('works.create')) : ?>
                <a class="btn btn--primary" href="<?= site_url('works/create') ?>"><?= esc(lang('App.action_register_work')) ?></a>
            <?php endif; ?>
            <?php if (user_can('licenses.create')) : ?>
                <a class="btn btn--secondary" href="<?= site_url('licenses/create') ?>"><?= esc(lang('App.action_create_license')) ?></a>
            <?php endif; ?>
            <?php if (user_can('usage_reports.create')) : ?>
                <a class="btn btn--secondary" href="<?= site_url('usage-reports/create') ?>"><?= esc(lang('App.action_report_usage')) ?></a>
            <?php endif; ?>
        </div>
        <h3 class="card__title" style="margin-top: 1.25rem;"><?= esc(lang('App.dashboard_pinned_assets')) ?></h3>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('App.works_col_title')) ?></th>
                        <th><?= esc(lang('App.works_col_status')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pinnedWorks === []) : ?>
                        <tr>
                            <td colspan="2" class="muted"><?= esc(lang('App.dashboard_pinned_empty')) ?></td>
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
            <a class="btn btn--ghost btn--sm" href="<?= site_url('works') ?>"><?= esc(lang('App.dashboard_open_assets')) ?></a>
        </div>
    </div>
</div>

<div class="grid grid--dashboard-mid" style="margin-top: 1.25rem;">
    <div class="card">
        <h2 class="card__title"><?= esc(lang('App.dashboard_top_licensed')) ?></h2>
        <p class="muted" style="margin:0 0 0.75rem;font-size:0.9rem;"><?= esc(lang('App.dashboard_top_licensed_hint')) ?></p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('App.licenses_col_work')) ?></th>
                        <th><?= esc(lang('App.works_col_licenses')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topLicensedWorks === []) : ?>
                        <tr><td colspan="2" class="muted"><?= esc(lang('App.dashboard_no_data_filter')) ?></td></tr>
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
        <h2 class="card__title"><?= esc(lang('App.dashboard_top_licensees')) ?></h2>
        <p class="muted" style="margin:0 0 0.75rem;font-size:0.9rem;"><?= esc(lang('App.dashboard_top_licensees_hint')) ?></p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('App.licenses_col_licensee')) ?></th>
                        <th><?= esc(lang('App.dashboard_col_new_licenses')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topLicensees === []) : ?>
                        <tr><td colspan="2" class="muted"><?= esc(lang('App.dashboard_no_data_filter')) ?></td></tr>
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
        <h2 class="card__title"><?= esc(lang('App.dashboard_top_reported')) ?></h2>
        <p class="muted" style="margin:0 0 0.75rem;font-size:0.9rem;"><?= esc(lang('App.dashboard_top_reported_hint')) ?></p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('App.licenses_col_work')) ?></th>
                        <th><?= esc(lang('App.dashboard_col_reports')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topReportedWorks === []) : ?>
                        <tr><td colspan="2" class="muted"><?= esc(lang('App.dashboard_no_usage_filter')) ?></td></tr>
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
        <h2 class="card__title"><?= esc(lang('App.dashboard_cases_by_status')) ?></h2>
        <p class="muted" style="margin-top: 0;"><?= esc(lang('App.dashboard_cases_live')) ?></p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('App.cases_col_status')) ?></th>
                        <th><?= esc(lang('App.reports_col_count')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (\App\Models\InfringementCaseModel::ALL_STATUSES as $st) : ?>
                        <?php $cnt = (int) ($caseStatusBreakdown[$st] ?? 0); ?>
                        <tr>
                            <td><?= esc(localized_case_status($st)) ?></td>
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
            <a class="btn btn--ghost btn--sm" href="<?= site_url('cases') ?>"><?= esc(lang('App.dashboard_open_cases')) ?></a>
        </div>
    </div>
<?php endif; ?>

<?php if ($recentUsageDetections !== []) : ?>
    <div class="card" style="margin-top: 1.25rem;">
        <h2 class="card__title"><?= esc(lang('App.dashboard_recent_usage')) ?></h2>
        <p class="muted" style="margin-top: 0;"><?= esc(lang('App.dashboard_recent_usage_hint')) ?></p>
        <div class="table-wrap table-wrap--flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('App.licenses_col_work')) ?></th>
                        <th><?= esc(lang('App.dashboard_col_source')) ?></th>
                        <th><?= esc(lang('App.dashboard_col_detected')) ?></th>
                        <th><?= esc(lang('App.works_col_status')) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsageDetections as $u) : ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <tr>
                            <td><?= esc((string) ($u['work_title'] ?? '')) ?></td>
                            <td><?= esc((string) ($u['source'] ?? '')) ?></td>
                            <td><?= esc(localized_date((string) ($u['detected_at'] ?? ''), true)) ?></td>
                            <td>
                                <?= view('components/badges', [
                                    'label' => (string) ($u['usage_label'] ?? ''),
                                    'tone'  => (string) ($u['usage_tone'] ?? 'neutral'),
                                ]) ?>
                            </td>
                            <td class="table-actions">
                                <?php if ($uid > 0) : ?>
                                    <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports/' . $uid) ?>"><?= esc(lang('App.action_view')) ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 0.85rem;">
            <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports') ?>"><?= esc(lang('App.dashboard_open_usage_reports')) ?></a>
        </div>
    </div>
<?php endif; ?>
