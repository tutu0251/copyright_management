<p class="page-intro">Static usage and revenue rollups by period. For chart rollups, open <a href="<?= site_url('mockup/reports') ?>">Reports</a>.</p>

<div class="toolbar">
    <div class="toolbar__left">
        <button type="button" class="btn btn--secondary" disabled>Last 90 days</button>
        <button type="button" class="btn btn--secondary" disabled>Export</button>
    </div>
    <div class="toolbar__right">
        <button type="button" class="btn btn--ghost" disabled>Schedule email</button>
    </div>
</div>

<div class="grid grid--stats" style="margin-bottom: 1rem;">
    <div class="card stat-card">
        <div class="stat-card__label">Total impressions (mock)</div>
        <div class="stat-card__value">1.29M</div>
        <div class="stat-card__hint">Across listed rows</div>
    </div>
    <div class="card stat-card">
        <div class="stat-card__label">Reported revenue</div>
        <div class="stat-card__value">$69.6k</div>
        <div class="stat-card__hint">Non-audited sample</div>
    </div>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Period</th>
                <th>Work</th>
                <th>Channel</th>
                <th>Impressions</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r) : ?>
                <tr>
                    <td><?= esc($r['period']) ?></td>
                    <td><?= esc($r['work']) ?></td>
                    <td><?= esc($r['channel']) ?></td>
                    <td><?= esc($r['impressions']) ?></td>
                    <td><?= esc($r['revenue']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
