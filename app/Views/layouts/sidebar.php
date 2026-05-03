<?php
$nav ??= [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'path' => 'mockup'],
    ['id' => 'assets', 'label' => 'Assets', 'path' => 'mockup/assets'],
    ['id' => 'licenses', 'label' => 'Licenses', 'path' => 'mockup/licenses'],
    ['id' => 'monitoring', 'label' => 'Monitoring', 'path' => 'mockup/monitoring'],
    ['id' => 'cases', 'label' => 'Cases', 'path' => 'mockup/cases'],
    ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports'],
    ['id' => 'settings', 'label' => 'Settings', 'path' => 'mockup/settings'],
];
$currentUser ??= ['name' => 'Guest', 'role' => '—'];
$useAuthLogout ??= false;
?>
<aside class="app-sidebar" aria-label="Primary navigation">
    <div class="app-brand">
        <span class="app-brand__mark">CM</span>
        <div>
            <div class="app-brand__name">Copyright Manager</div>
            <div class="app-brand__tag">Enterprise</div>
        </div>
    </div>
    <nav class="app-nav">
        <?php foreach ($nav as $item) : ?>
            <?php
            $active = ($currentPage === $item['id']);
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
            <span class="app-user__avatar app-user__avatar--sm" aria-hidden="true"><?= esc(strtoupper(substr((string) $currentUser['name'], 0, 1))) ?></span>
            <div class="app-sidebar__user-meta">
                <span class="app-sidebar__user-name"><?= esc($currentUser['name']) ?></span>
                <span class="app-sidebar__user-role"><?= esc($currentUser['role']) ?></span>
            </div>
        </div>
        <?php if ($useAuthLogout) : ?>
            <?= form_open(site_url('logout'), ['class' => 'app-sidebar__logout-form', 'style' => 'margin: 0;']) ?>
                <button type="submit" class="btn btn--ghost btn--sm app-sidebar__logout">Log out</button>
            <?= form_close() ?>
        <?php else : ?>
            <button type="button" class="btn btn--ghost btn--sm app-sidebar__logout" id="btn-logout-mock">Log out</button>
        <?php endif; ?>
    </div>
</aside>
