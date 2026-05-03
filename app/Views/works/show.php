<?php
/** @var array<string, mixed> $work */
$wid = (string) ($work['work_id'] ?? $work['id'] ?? '');
$workLicenses = $workLicenses ?? [];
$licenseUsageSnapshots = $licenseUsageSnapshots ?? [];
$usageMonitoringRows = $usageMonitoringRows ?? [];
$ownershipRows = $ownershipRows ?? [];
$files = $files ?? [];
$flashMessage = $flashMessage ?? null;
$flashWarning = $flashWarning ?? null;
?>

<?php if ($flashMessage) : ?>
    <p class="muted" role="status"><?= esc($flashMessage) ?></p>
<?php endif; ?>
<?php if ($flashWarning) : ?>
    <p class="muted" role="status"><?= esc($flashWarning) ?></p>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('works') ?>">← Assets</a>
    </div>
    <div class="toolbar__right">
        <a class="btn btn--secondary" href="<?= site_url('works/' . $wid . '/edit') ?>">Edit asset</a>
    </div>
</div>

<div class="ui-tabs" data-tabs-root>
    <div class="ui-tabs__list" role="tablist" aria-label="Asset sections">
        <button type="button" class="ui-tabs__tab is-active" role="tab" aria-selected="true" data-tab="overview">Overview</button>
        <button type="button" class="ui-tabs__tab" role="tab" aria-selected="false" data-tab="files">Evidence files</button>
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
                        echo view('components/badges', ['label' => str_replace('_', ' ', $st), 'tone' => $tone]);
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
                <?php if (($work['description'] ?? '') !== '') : ?>
                    <p class="muted" style="margin-top: 1rem;"><?= esc($work['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="card">
                <h2 class="card__title">Creators & identifiers</h2>
                <p class="muted" style="margin-top: 0;">Creators</p>
                <ul>
                    <?php if (($work['creators'] ?? []) === []) : ?>
                        <li class="muted">—</li>
                    <?php else : ?>
                        <?php foreach ($work['creators'] as $c) : ?>
                            <li><?= esc($c) ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <p class="muted">Identifiers</p>
                <ul>
                    <?php foreach ($work['identifiers'] as $id) : ?>
                        <li><?= esc($id) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="muted" style="margin-top:1rem;">Evidence is listed under the Evidence files tab. Files are not served from a public URL.</p>
            </div>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="files" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Evidence files</h2>
            <?php if ($files === []) : ?>
                <p class="muted">No files attached yet. Use Edit asset to upload evidence.</p>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Original name</th>
                            <th>Stored name</th>
                            <th>Size</th>
                            <th>MIME</th>
                            <th>SHA-256</th>
                            <th>Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $f) : ?>
                            <tr>
                                <td><?= esc((string) ($f['original_filename'] ?? '')) ?></td>
                                <td><code><?= esc((string) ($f['stored_filename'] ?? '')) ?></code></td>
                                <td><?= esc(number_format((int) ($f['size_bytes'] ?? 0))) ?> B</td>
                                <td><?= esc((string) ($f['mime_type'] ?? '—')) ?></td>
                                <td><code style="font-size:0.75rem;"><?= esc((string) ($f['sha256'] ?? '')) ?></code></td>
                                <td><?= esc((string) ($f['created_at'] ?? '—')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
            <div style="margin-top: 0.75rem;">
                <a class="btn btn--primary btn--sm" href="<?= site_url('works/' . $wid . '/edit') ?>">Add files</a>
            </div>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="ownership" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Ownership</h2>
            <p class="muted" style="margin-top: 0;">Linked parties, roles, and percentages for this work.</p>
            <div style="margin-bottom: 0.75rem;">
                <a class="btn btn--primary btn--sm" href="<?= site_url('works/' . $wid . '/owners') ?>">Add / manage owners</a>
            </div>
            <?php if ($ownershipRows === []) : ?>
                <p class="muted">No owners linked yet. Use <strong>Add / manage owners</strong> to attach parties from the registry.</p>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Owner</th>
                            <th>Share</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Since</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ownershipRows as $r) : ?>
                            <?php
                            $oid = (int) ($r['owner_id'] ?? 0);
                            $piv = (int) ($r['work_owner_id'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <?php if ($oid > 0) : ?>
                                        <a href="<?= site_url('owners/' . $oid) ?>"><?= esc($r['owner']) ?></a>
                                    <?php else : ?>
                                        <?= esc($r['owner']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($r['share']) ?></td>
                                <td><?= esc($r['role'] ?? '—') ?></td>
                                <td><?= esc($r['status'] ?? '—') ?></td>
                                <td><?= esc($r['since']) ?></td>
                                <td class="table-actions">
                                    <?php if ($piv > 0) : ?>
                                        <?= form_open(site_url('works/' . $wid . '/owners/' . $piv . '/delete'), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Unlink this owner from the work?');"]) ?>
                                            <button type="submit" class="btn btn--ghost btn--sm">Unlink</button>
                                        <?= form_close() ?>
                                    <?php endif; ?>
                                </td>
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
                <p class="muted">No licenses reference this work yet.</p>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>License</th>
                            <th>Licensee</th>
                            <th>Type</th>
                            <th>Territory</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workLicenses as $lic) : ?>
                            <?php
                            $licId = (int) ($lic['id'] ?? 0);
                            $eff = (string) ($lic['eff'] ?? '');
                            $ls = (string) ($lic['status'] ?? '');
                            $tone = $eff === \App\Models\LicenseModel::STATUS_ACTIVE ? 'success' : ($eff === \App\Models\LicenseModel::STATUS_EXPIRING_SOON ? 'warning' : ($eff === \App\Models\LicenseModel::STATUS_EXPIRED ? 'danger' : 'neutral'));
                            ?>
                            <tr>
                                <td>#<?= esc((string) $licId) ?></td>
                                <td><?= esc($lic['licensee']) ?></td>
                                <td><?= esc($lic['type']) ?></td>
                                <td><?= esc($lic['territory'] ?? '—') ?></td>
                                <td><?= esc($lic['start_date'] ?? '—') ?></td>
                                <td><?= esc($lic['end_date'] ?? '—') ?></td>
                                <td><?= esc($lic['fee'] ?? '—') ?></td>
                                <td><?= view('components/badges', ['label' => $ls, 'tone' => $tone]) ?></td>
                                <td class="table-actions">
                                    <?php if ($licId > 0) : ?>
                                        <a class="btn btn--ghost btn--sm" href="<?= site_url('licenses/' . $licId) ?>">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
            <div style="margin-top: 0.75rem;">
                <a class="btn btn--primary btn--sm" href="<?= site_url('licenses/create?work_id=' . $wid) ?>">Create license</a>
                <a class="btn btn--secondary btn--sm" href="<?= site_url('licenses') ?>">All licenses</a>
            </div>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="usage" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Usage monitoring</h2>
            <p class="muted" style="margin-top: 0;">Detections filed for this work (manual monitoring).</p>
            <?php if ($usageMonitoringRows === []) : ?>
                <p class="muted">No usage reports for this work yet.</p>
            <?php else : ?>
                <?= $this->include('components/table') ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Usage</th>
                            <th>Detected</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usageMonitoringRows as $r) : ?>
                            <?php $urId = (int) ($r['id'] ?? 0); ?>
                            <tr>
                                <td><?= esc($r['source']) ?></td>
                                <td><?= view('components/badges', ['label' => (string) ($r['usage_type_label'] ?? ''), 'tone' => (string) ($r['usage_tone'] ?? 'neutral')]) ?></td>
                                <td><?= esc($r['detected_at']) ?></td>
                                <td class="table-actions">
                                    <?php if ($urId > 0) : ?>
                                        <a class="btn btn--ghost btn--sm" href="<?= site_url('usage-reports/' . $urId) ?>">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
            <div style="margin-top: 0.75rem;">
                <a class="btn btn--primary btn--sm" href="<?= site_url('usage-reports/create?work_id=' . $wid) ?>">Report usage</a>
                <a class="btn btn--secondary btn--sm" href="<?= site_url('usage-reports') ?>">All usage reports</a>
            </div>
        </div>

        <?php if ($licenseUsageSnapshots !== []) : ?>
            <div class="card" style="margin-top: 1rem;">
                <h2 class="card__title">License usage snapshots</h2>
                <p class="muted" style="margin-top: 0;">Self-reported licensee periods (legacy license reporting table).</p>
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
                        <?php foreach ($licenseUsageSnapshots as $r) : ?>
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
            </div>
        <?php endif; ?>
    </div>
</div>
