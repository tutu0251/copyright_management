<?php helper('url'); ?>
<nav class="muted" style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;font-size:0.9rem;">
    <a href="<?= site_url('reports') ?>"><?= esc(lang('App.reports_nav_overview')) ?></a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/works') ?>"><?= esc(lang('App.reports_nav_works')) ?></a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/licenses') ?>"><?= esc(lang('App.reports_nav_licenses')) ?></a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/usage') ?>"><?= esc(lang('App.reports_nav_usage')) ?></a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/cases') ?>"><?= esc(lang('App.reports_nav_cases')) ?></a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/activity') ?>"><?= esc(lang('App.reports_nav_activity')) ?></a>
</nav>
