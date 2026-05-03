<?php
$nav ??= [
    ['id' => 'dashboard', 'label' => lang('App.nav_dashboard'), 'path' => 'mockup'],
    ['id' => 'assets', 'label' => lang('App.nav_assets'), 'path' => 'mockup/assets'],
    ['id' => 'licenses', 'label' => lang('App.nav_licenses'), 'path' => 'mockup/licenses'],
    ['id' => 'usage_reports', 'label' => lang('App.nav_usage_reports'), 'path' => 'usage-reports'],
    ['id' => 'cases', 'label' => lang('App.nav_cases'), 'path' => 'cases'],
    ['id' => 'reports', 'label' => lang('App.nav_reports'), 'path' => 'mockup/reports'],
    ['id' => 'settings', 'label' => lang('App.nav_settings_roles'), 'path' => 'mockup/settings'],
];
$currentUser ??= ['name' => 'Guest', 'role' => '—'];
$useAuthLogout ??= false;
?>
<aside class="app-sidebar" aria-label="<?= esc(lang('App.nav_primary'), 'attr') ?>">
    <div class="app-brand">
        <span class="app-brand__mark">CM</span>
        <div>
            <div class="app-brand__name"><?= esc(lang('App.brand_name')) ?></div>
            <div class="app-brand__tag"><?= esc(lang('App.brand_tag_enterprise')) ?></div>
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
                <button type="submit" class="btn btn--ghost btn--sm app-sidebar__logout"><?= esc(lang('App.auth_logout')) ?></button>
            <?= form_close() ?>
        <?php else : ?>
            <button type="button" class="btn btn--ghost btn--sm app-sidebar__logout" id="btn-logout-mock"><?= esc(lang('App.action_logout')) ?></button>
        <?php endif; ?>
    </div>
</aside>
