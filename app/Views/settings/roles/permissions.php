<?php
/** @var array<string, mixed> $role */
/** @var list<array<string, mixed>> $permissions */
/** @var array<int, true> $selectedIds */
$rid = (int) ($role['id'] ?? 0);
$msg = session()->getFlashdata('message');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('settings/roles') ?>">← Roles</a>
    </div>
</div>

<p class="page-intro">Toggle permissions for <strong><?= esc((string) ($role['name'] ?? '')) ?></strong> (<code><?= esc((string) ($role['slug'] ?? '')) ?></code>). Changes apply on the next request for users with this role.</p>

<?php
$groups = [];
foreach ($permissions as $p) {
    $slug = (string) ($p['slug'] ?? '');
    $prefix = explode('.', $slug, 2)[0] ?? 'other';
    $groups[$prefix][] = $p;
}
ksort($groups);
?>

<?= form_open(site_url('settings/roles/' . $rid . '/permissions'), ['class' => 'card', 'style' => 'margin-top:1rem;padding:1.25rem;']) ?>
    <?= csrf_field() ?>

    <?php foreach ($groups as $prefix => $items) : ?>
        <fieldset style="border:1px solid var(--border, #334155);border-radius:8px;padding:1rem;margin-bottom:1rem;">
            <legend class="card__title" style="padding:0 0.35rem;"><?= esc(ucfirst(str_replace('_', ' ', $prefix))) ?></legend>
            <div class="grid" style="gap:0.5rem;">
                <?php foreach ($items as $p) : ?>
                    <?php
                    $pid = (int) ($p['id'] ?? 0);
                    $slug = (string) ($p['slug'] ?? '');
                    $checked = isset($selectedIds[$pid]);
                    ?>
                    <label style="display:flex;align-items:flex-start;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="permission_ids[]" value="<?= $pid ?>" <?= $checked ? 'checked' : '' ?>>
                        <span>
                            <code><?= esc($slug) ?></code>
                            <span class="muted" style="display:block;font-size:0.85rem;"><?= esc((string) ($p['name'] ?? '')) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    <?php endforeach; ?>

    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem;">
        <button type="submit" class="btn btn--primary">Save permissions</button>
        <a class="btn btn--ghost" href="<?= site_url('settings/roles') ?>">Cancel</a>
    </div>
<?= form_close() ?>
