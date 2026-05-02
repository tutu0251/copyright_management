<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> — Copyright Management</title>
    <script>
    (function () {
        try {
            var t = localStorage.getItem('cm_mock_theme');
            if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    })();
    </script>
    <link rel="stylesheet" href="<?= base_url('assets/css/mockup.css') ?>">
    <?php if (! empty($useCharts)) : ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>
</head>
<body class="app-body" data-page="<?= esc($currentPage ?? '', 'attr') ?>">
    <?php
    // TODO: Inject authenticated user + org context from session / API.
    ?>
    <div class="app-shell">
        <?= $this->include('layouts/sidebar') ?>
        <div class="app-main">
            <?= $this->include('layouts/topbar') ?>
            <div class="app-page-head">
                <h1 class="app-page-head__title"><?= esc($pageTitle) ?></h1>
                <p class="app-page-head__crumb muted">Copyright Management · UI mockup</p>
            </div>
            <main class="app-content" id="main-content">
                <?= $content ?>
            </main>
        </div>
    </div>

    <div class="ui-modal" id="ui-modal" hidden aria-hidden="true">
        <div class="ui-modal__backdrop" data-modal-close></div>
        <div class="ui-modal__panel" role="dialog" aria-modal="true" aria-labelledby="ui-modal-title">
            <div class="ui-modal__head">
                <h2 class="ui-modal__title" id="ui-modal-title">Quick action</h2>
                <button type="button" class="ui-icon-btn" data-modal-close aria-label="Close">&times;</button>
            </div>
            <div class="ui-modal__body" id="ui-modal-body">
                <p class="muted">Placeholder — backend will submit forms here.</p>
            </div>
            <div class="ui-modal__foot">
                <button type="button" class="btn btn--secondary" data-modal-close>Cancel</button>
                <button type="button" class="btn btn--primary" id="ui-modal-confirm" disabled>Confirm (mock)</button>
            </div>
        </div>
    </div>

    <div class="ui-toast-host" id="ui-toast-host" aria-live="polite"></div>

    <?php if (! empty($chartPayload)) : ?>
    <script type="application/json" id="chart-payload"><?= json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
    <?php endif; ?>
    <script src="<?= base_url('assets/js/mockup.js') ?>" defer></script>
</body>
</html>
