<?php helper('url'); ?>
<nav class="muted" style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;font-size:0.9rem;">
    <a href="<?= site_url('reports') ?>">Overview</a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/works') ?>">Works</a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/licenses') ?>">Licenses</a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/usage') ?>">Usage</a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/cases') ?>">Cases</a>
    <span aria-hidden="true">·</span>
    <a href="<?= site_url('reports/activity') ?>">Activity</a>
</nav>
