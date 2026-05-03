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
<body class="app-body">
    <div class="app-shell" style="grid-template-columns: 1fr; min-height: 100vh; align-items: center; justify-items: center;">
        <main class="app-content" style="max-width: 26rem; width: 100%; padding: 2rem 1rem;">
            <div class="card" style="padding: 1.5rem;">
                <div style="margin-bottom: 1.25rem;">
                    <div class="app-brand" style="margin-bottom: 0.5rem;">
                        <span class="app-brand__mark">CM</span>
                        <div>
                            <div class="app-brand__name">Copyright Manager</div>
                            <div class="app-brand__tag muted">Sign in</div>
                        </div>
                    </div>
                    <p class="muted" style="margin: 0;">Use your account to open the live dashboard.</p>
                </div>

                <?php if (! empty($error)) : ?>
                    <p class="muted" style="color: var(--danger, #f87171); margin: 0 0 1rem;"><?= esc($error) ?></p>
                <?php endif; ?>
                <?php if (! empty($message)) : ?>
                    <p class="muted" style="margin: 0 0 1rem;"><?= esc($message) ?></p>
                <?php endif; ?>

                <?= form_open(site_url('login'), ['class' => 'stack', 'style' => 'gap: 0.85rem;']) ?>
                    <div>
                        <label for="email" class="muted" style="display: block; margin-bottom: 0.35rem;">Email</label>
                        <input type="email" name="email" id="email" class="app-topbar__search-input" style="width: 100%;"
                               value="<?= esc(old('email') ?? '') ?>" autocomplete="username" required>
                    </div>
                    <div>
                        <label for="password" class="muted" style="display: block; margin-bottom: 0.35rem;">Password</label>
                        <input type="password" name="password" id="password" class="app-topbar__search-input" style="width: 100%;"
                               autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn--primary" style="width: 100%;">Sign in</button>
                <?= form_close() ?>

                <p class="muted" style="margin: 1rem 0 0; font-size: 0.85rem;">
                    UI mockup remains available at <a href="<?= site_url('mockup') ?>"><?= site_url('mockup') ?></a>.
                </p>
            </div>
        </main>
    </div>
    <script src="<?= base_url('assets/js/mockup.js') ?>" defer></script>
</body>
</html>
