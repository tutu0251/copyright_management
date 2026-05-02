<p class="page-intro">Split tables showing fractional ownership by work. Actions are visual only.</p>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('mockup/assets') ?>">← Assets</a>
        <button type="button" class="btn btn--secondary" disabled>Filter by work</button>
        <button type="button" class="btn btn--secondary" disabled>Add owner</button>
    </div>
    <div class="toolbar__right">
        <button type="button" class="btn btn--ghost" disabled>History</button>
    </div>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Work</th>
                <th>Owner entity</th>
                <th>Share</th>
                <th>Effective from</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r) : ?>
                <tr>
                    <td>
                        <a href="<?= site_url('mockup/work/' . $r['work_id']) ?>"><?= esc($r['work_title']) ?></a>
                        <div class="muted"><?= esc($r['work_id']) ?></div>
                    </td>
                    <td><?= esc($r['owner']) ?></td>
                    <td><span class="app-pill pill-neutral"><?= esc($r['share']) ?></span></td>
                    <td><?= esc($r['since']) ?></td>
                    <td class="table-actions">
                        <button type="button" class="btn btn--ghost btn--sm" disabled>Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
