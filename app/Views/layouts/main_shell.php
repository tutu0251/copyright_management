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
</head>
<body class="app-body" data-page="<?= esc($currentPage ?? '', 'attr') ?>">
    <div class="app-shell">
        <?= $this->include('main/partials/sidebar') ?>
        <div class="app-main">
            <?= $this->include('main/partials/topbar') ?>
            <div class="app-page-head">
                <h1 class="app-page-head__title"><?= esc($pageTitle) ?></h1>
                <p class="app-page-head__crumb muted">Copyright Management · Main application</p>
            </div>
            <main class="app-content" id="main-content">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
    <div class="ui-toast-host" id="ui-toast-host" aria-live="polite"></div>
    <script src="<?= base_url('assets/js/mockup.js') ?>" defer></script>
</body>
</html>
