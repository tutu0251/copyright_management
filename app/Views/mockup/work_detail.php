<?php
$wid = (string) ($work['work_id'] ?? $work['id'] ?? '');
$workLicenses = $workLicenses ?? [];
$workUsageRows = $workUsageRows ?? [];
$ownershipRows = $ownershipRows ?? [];
?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/assets') ?>">← Assets</a>
    </div>
    <div class="toolbar__right">
        <button type="button" class="btn btn--secondary" disabled>Edit asset</button>
        <button type="button" class="btn btn--primary" disabled>Attach evidence</button>
    </div>
</div>

<div class="ui-tabs" data-tabs-root>
    <div class="ui-tabs__list" role="tablist" aria-label="Asset sections">
        <button type="button" class="ui-tabs__tab is-active" role="tab" aria-selected="true" data-tab="overview">Overview</button>
        <button type="button" class="ui-tabs__tab" role="tab" aria-selected="false" data-tab="ownership">Ownership</button>
        <button type="button" class="ui-tabs__tab" role="tab" aria-selected="false" data-tab="licenses">Licenses</button>
        <button type="button" class="ui-tabs__tab" role="tab" aria-selected="false" data-tab="usage">Usage</button>
    </div>

    <div class="ui-tabs__panel is-active" data-tab-panel="overview" role="tabpanel">
        <div class="grid grid--2">
            <div class="card">
                <h2 class="card__title">Summary</h2>
                <dl class="dl-grid">
                    <dt>Work ID</dt>
                    <dd><?= esc($wid) ?></dd>
                    <dt>Title</dt>
                    <dd><strong><?= esc($work['title']) ?></strong></dd>
                    <dt>Type</dt>
                    <dd><?= esc($work['type']) ?></dd>
                    <dt>Creator</dt>
                    <dd><?= esc($work['creator']) ?></dd>
                    <dt>Owner</dt>
                    <dd><?= esc($work['owner']) ?></dd>
                    <dt>Status</dt>
                    <dd>
                        <?php
                        $st = (string) $work['copyright_status'];
                        $tone = preg_match('/pending|audit/i', $st) ? 'warning' : (preg_match('/registered/i', $st) ? 'success' : 'neutral');
                        echo view('components/badges', ['label' => $st, 'tone' => $tone]);
                        ?>
                    </dd>
                    <dt>Registered</dt>
                    <dd><?= esc($work['registration_date']) ?></dd>
                    <dt>Territory</dt>
                    <dd><?= esc($work['territory']) ?></dd>
                    <dt>License count</dt>
                    <dd><?= esc((string) $work['license_count']) ?></dd>
                    <dt>Risk</dt>
                    <dd>
                        <?php
                        $risk = $work['risk_level'];
                        $rt = $risk === 'High' ? 'danger' : ($risk === 'Medium' ? 'warning' : 'neutral');
                        echo view('components/badges', ['label' => $risk, 'tone' => $rt]);
                        ?>
                    </dd>
                    <dt>Last updated</dt>
                    <dd><?= esc($work['last_updated']) ?></dd>
                </dl>
                <p class="muted" style="margin-top: 1rem;"><?= esc($work['description']) ?></p>
            </div>
            <div class="card">
                <h2 class="card__title">Creators & identifiers</h2>
                <p class="muted" style="margin-top: 0;">Creators</p>
                <ul>
                    <?php foreach ($work['creators'] as $c) : ?>
                        <li><?= esc($c) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="muted">Identifiers</p>
                <ul>
                    <?php foreach ($work['identifiers'] as $id) : ?>
                        <li><?= esc($id) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="ownership" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Ownership splits</h2>
            <?php if ($ownershipRows === []) : ?>
                <p class="muted">No fractional ownership rows for this asset in the mock fixture.</p>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/ownership') ?>">Open global ownership table</a>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Owner</th>
                            <th>Share</th>
                            <th>Since</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ownershipRows as $r) : ?>
                            <tr>
                                <td><?= esc($r['owner']) ?></td>
                                <td><?= esc($r['share']) ?></td>
                                <td><?= esc($r['since']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="licenses" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Licenses for this asset</h2>
            <?php if ($workLicenses === []) : ?>
                <p class="muted">No licenses in the mock dataset reference this work.</p>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>License</th>
                            <th>Licensee</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Expires</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workLicenses as $lic) : ?>
                            <tr>
                                <td><?= esc($lic['id']) ?></td>
                                <td><?= esc($lic['licensee']) ?></td>
                                <td><?= esc($lic['type']) ?></td>
                                <td>
                                    <?php
                                    $ls = (string) ($lic['status'] ?? '');
                                    $lt = strcasecmp($ls, 'Active') === 0 ? 'success' : (preg_match('/pending|expir/i', $ls) ? 'warning' : 'neutral');
                                    echo view('components/badges', ['label' => $ls, 'tone' => $lt]);
                                    ?>
                                </td>
                                <td><?= esc($lic['expires']) ?></td>
                                <td class="table-actions">
                                    <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/license/' . $lic['id']) ?>">Open</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
            <div style="margin-top: 0.75rem;">
                <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/licenses') ?>">All licenses</a>
            </div>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="usage" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Usage snapshots</h2>
            <?php if ($workUsageRows === []) : ?>
                <p class="muted">No usage rows reference this title in the mock rollup.</p>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Channel</th>
                            <th>Impressions</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workUsageRows as $r) : ?>
                            <tr>
                                <td><?= esc($r['period']) ?></td>
                                <td><?= esc($r['channel']) ?></td>
                                <td><?= esc($r['impressions']) ?></td>
                                <td><?= esc($r['revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
            <div style="margin-top: 0.75rem;">
                <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/usage-reports') ?>">Usage reports</a>
            </div>
        </div>
    </div>
</div>
