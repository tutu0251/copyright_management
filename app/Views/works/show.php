<?php
/** @var array<string, mixed> $work */
$wid = (string) ($work['work_id'] ?? $work['id'] ?? '');
$workLicenses = $workLicenses ?? [];
$workUsageRows = $workUsageRows ?? [];
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
                                    <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/license/' . $lic['id']) ?>">Open (mock)</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->include('components/table_end') ?>
            <?php endif; ?>
            <div style="margin-top: 0.75rem;">
                <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/licenses') ?>">All licenses (mock)</a>
            </div>
        </div>
    </div>

    <div class="ui-tabs__panel" data-tab-panel="usage" role="tabpanel">
        <div class="card">
            <h2 class="card__title">Usage snapshots</h2>
            <?php if ($workUsageRows === []) : ?>
                <p class="muted">No usage rows for licenses tied to this work.</p>
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
                <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/usage-reports') ?>">Usage reports (mock)</a>
            </div>
        </div>
    </div>
</div>
