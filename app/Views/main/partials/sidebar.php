<?php
$nav = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'path' => 'main/dashboard'],
];
?>
<aside class="app-sidebar" aria-label="Primary navigation">
    <div class="app-brand">
        <span class="app-brand__mark">CM</span>
        <div>
            <div class="app-brand__name">Copyright Manager</div>
            <div class="app-brand__tag">Main</div>
        </div>
    </div>
    <nav class="app-nav">
        <?php foreach ($nav as $item) : ?>
            <?php
            $active = (($currentPage ?? '') === $item['id']);
            $href   = site_url($item['path']);
            ?>
            <a class="app-nav__link<?= $active ? ' is-active' : '' ?>"
               href="<?= $href ?>"
               <?= $active ? 'aria-current="page"' : '' ?>>
                <?= esc($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="app-sidebar__footer">
        <div class="app-sidebar__user">
            <?php $initial = strtoupper(substr($currentUser['name'] ?? '?', 0, 1)); ?>
            <span class="app-user__avatar app-user__avatar--sm" aria-hidden="true"><?= esc($initial) ?></span>
            <div class="app-sidebar__user-meta">
                <span class="app-sidebar__user-name"><?= esc($currentUser['name'] ?? '') ?></span>
                <span class="app-sidebar__user-role"><?= esc($currentUser['role'] ?? '') ?></span>
            </div>
        </div>
        <a class="btn btn--ghost btn--sm app-sidebar__logout" href="<?= site_url('main/logout') ?>">Log out</a>
    </div>
</aside>
