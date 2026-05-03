<p class="page-intro">Portfolio health and licensing signals from your catalog; trend charts use illustrative series until reporting aggregates are wired.</p>

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

<div class="chart-grid">
    <div class="card chart-card">
        <h2 class="card__title">Works growth</h2>
        <p class="chart-card__hint">New registrations per month (sample series).</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartWorksGrowth" height="220" aria-label="Works growth chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">License activity</h2>
        <p class="chart-card__hint">End-of-month active license count (sample).</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartLicenseActivity" height="220" aria-label="License activity chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Infringement trend</h2>
        <p class="chart-card__hint">Detected vs resolved cases (sample).</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartInfringement" height="220" aria-label="Infringement trend chart" role="img"></canvas>
        </div>
    </div>
    <div class="card chart-card">
        <h2 class="card__title">Revenue trend</h2>
        <p class="chart-card__hint">Reported license revenue in USD (sample).</p>
        <div class="chart-canvas-wrap">
            <canvas id="chartRevenue" height="220" aria-label="Revenue trend chart" role="img"></canvas>
        </div>
    </div>
</div>

<div class="grid grid--dashboard-mid" style="margin-top: 1.25rem;">
    <div class="card">
        <h2 class="card__title">Activity feed</h2>
        <div class="activity-feed">
            <?php foreach ($activity as $row) : ?>
                <?php
                $type = preg_replace('/[^a-z]/i', '', (string) ($row['type'] ?? 'work'));
                $dotClass = 'activity-item__dot--' . ($type !== '' ? strtolower($type) : 'work');
                ?>
                <div class="activity-item">
                    <span class="activity-item__dot <?= esc($dotClass, 'attr') ?>" aria-hidden="true"></span>
                    <div class="activity-item__meta">
                        <div class="activity-item__time"><?= esc($row['time']) ?></div>
                        <div class="activity-item__text"><?= esc($row['text']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card">
        <h2 class="card__title">Quick actions</h2>
        <p class="muted" style="margin: 0 0 0.75rem;">Shortcuts open a modal shell — forms wire to the API later.</p>
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

<?php
$recentUsageDetections = $recentUsageDetections ?? [];
?>
<?php if ($recentUsageDetections !== []) : ?>
    <div class="card" style="margin-top: 1.25rem;">
        <h2 class="card__title">Recent usage detections (7 days)</h2>
        <p class="muted" style="margin-top: 0;">Latest manual monitoring entries across the catalog.</p>
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
