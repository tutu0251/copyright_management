<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr') ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> — <?= esc(lang('App.meta_title_suffix')) ?></title>
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
                            <div class="app-brand__name"><?= esc(lang('App.brand_name')) ?></div>
                            <div class="app-brand__tag muted"><?= esc(lang('App.brand_tag_signin')) ?></div>
                        </div>
                    </div>
                    <p class="muted" style="margin: 0;"><?= esc(lang('App.auth_intro')) ?></p>
                </div>

                <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <label for="login-lang" class="muted" style="font-size: 0.85rem;"><?= esc(lang('App.topbar_language')) ?></label>
                    <select id="login-lang" class="input-like" style="padding:0.35rem 0.5rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;font-size:0.85rem;"
                            onchange="if(this.value) window.location.href=this.value;">
                        <?php $loc = service('request')->getLocale(); ?>
                        <option value="<?= esc(current_lang_url('en'), 'attr') ?>" <?= $loc === 'en' ? 'selected' : '' ?>><?= esc(lang('App.lang_english')) ?></option>
                        <option value="<?= esc(current_lang_url('ja'), 'attr') ?>" <?= $loc === 'ja' ? 'selected' : '' ?>><?= esc(lang('App.lang_japanese')) ?></option>
                    </select>
                </div>

                <?php if (! empty($error)) : ?>
                    <p class="muted" style="color: var(--danger, #f87171); margin: 0 0 1rem;"><?= esc($error) ?></p>
                <?php endif; ?>
                <?php if (! empty($message)) : ?>
                    <p class="muted" style="margin: 0 0 1rem;"><?= esc($message) ?></p>
                <?php endif; ?>

                <?= form_open(site_url('login'), ['class' => 'stack', 'style' => 'gap: 0.85rem;']) ?>
                    <div>
                        <label for="email" class="muted" style="display: block; margin-bottom: 0.35rem;"><?= esc(lang('App.auth_email')) ?></label>
                        <input type="email" name="email" id="email" class="app-topbar__search-input" style="width: 100%;"
                               value="<?= esc(old('email') ?? '') ?>" autocomplete="username" required>
                    </div>
                    <div>
                        <label for="password" class="muted" style="display: block; margin-bottom: 0.35rem;"><?= esc(lang('App.auth_password')) ?></label>
                        <input type="password" name="password" id="password" class="app-topbar__search-input" style="width: 100%;"
                               autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn--primary" style="width: 100%;"><?= esc(lang('App.auth_sign_in')) ?></button>
                <?= form_close() ?>

                <p class="muted" style="margin: 1rem 0 0; font-size: 0.85rem;">
                    <?= esc(lang('App.auth_mockup_prefix')) ?>
                    <a href="<?= site_url('mockup') ?>"><?= esc(site_url('mockup')) ?></a>.
                </p>
            </div>
        </main>
    </div>
    <script src="<?= base_url('assets/js/mockup.js') ?>" defer></script>
</body>
</html>
