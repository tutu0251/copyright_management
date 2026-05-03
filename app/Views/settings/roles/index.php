<?php
/** @var list<array{role: array<string, mixed>, permission_count: int}> $roles */
$msg = session()->getFlashdata('message');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>

<p class="page-intro">Each user inherits permissions from their assigned roles. Use <strong>Edit permissions</strong> to attach or detach capabilities for a role.</p>

<div class="card">
    <h2 class="card__title">Roles</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Permissions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $row) : ?>
                    <?php
                    $r = $row['role'];
                    $rid = (int) ($r['id'] ?? 0);
                    ?>
                    <tr>
                        <td><strong><?= esc((string) ($r['name'] ?? '')) ?></strong></td>
                        <td><code><?= esc((string) ($r['slug'] ?? '')) ?></code></td>
                        <td><?= (int) $row['permission_count'] ?></td>
                        <td style="text-align:right;">
                            <a class="btn btn--secondary btn--sm" href="<?= site_url('settings/roles/' . $rid . '/permissions') ?>">Edit permissions</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
