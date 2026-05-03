<?php
$owners = $owners ?? [];
$searchQuery = $searchQuery ?? '';
$msg = session()->getFlashdata('message');
$err = session()->getFlashdata('errors');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>
<?php if (is_array($err) && $err !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($err as $e) : ?>
                <li><?= esc(is_array($e) ? json_encode($e) : (string) $e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<p class="page-intro">Manage legal parties (people, companies, or organizations) that you attach to catalog works with roles and percentages.</p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <?= form_open(site_url('owners'), ['method' => 'get', 'class' => 'toolbar__grow', 'style' => 'display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;']) ?>
            <input class="input toolbar__search" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" placeholder="Search name, email, country…" aria-label="Search owners">
            <button type="submit" class="btn btn--secondary btn--sm">Search</button>
            <?php if ($searchQuery !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('owners') ?>">Clear</a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('owners.create')) : ?>
            <a class="btn btn--primary" href="<?= site_url('owners/create') ?>">Create owner</a>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Email</th>
            <th>Country</th>
            <th>Updated</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($owners === []) : ?>
            <tr>
                <td colspan="6" class="muted">No owners yet. Create one to start linking works.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($owners as $o) : ?>
                <?php
                $oid = (int) ($o['id'] ?? 0);
                $typeKey = (string) ($o['owner_type'] ?? 'individual');
                $typeLabel = \App\Models\OwnerModel::ownerTypeLabel($typeKey);
                ?>
                <tr>
                    <td><strong><?= esc((string) ($o['name'] ?? '')) ?></strong></td>
                    <td><?= esc($typeLabel) ?></td>
                    <td><?= esc((string) ($o['email'] ?? '—')) ?></td>
                    <td><?= esc((string) ($o['country'] ?? '—')) ?></td>
                    <td><?= esc($o['updated_at'] !== null && $o['updated_at'] !== '' ? (string) $o['updated_at'] : '—') ?></td>
                    <td class="table-actions">
                        <a class="btn btn--ghost btn--sm" href="<?= site_url('owners/' . $oid) ?>">View</a>
                        <?php if (user_can('owners.update')) : ?>
                            <a class="btn btn--ghost btn--sm" href="<?= site_url('owners/' . $oid . '/edit') ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>
