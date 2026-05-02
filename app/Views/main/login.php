<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> — Copyright Management</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/mockup.css') ?>">
</head>
<body class="app-body">
    <div class="app-content" style="max-width: 420px; margin: 4rem auto; padding: 0 1rem;">
        <h1 class="app-page-head__title" style="margin-bottom: 0.25rem;"><?= esc($pageTitle) ?></h1>
        <p class="muted" style="margin-bottom: 1.5rem;">Copyright Management · Main application</p>

        <?php if ($msg = session()->getFlashdata('auth_message')) : ?>
            <p class="muted" style="margin-bottom: 1rem;"><?= esc($msg) ?></p>
        <?php endif; ?>

        <?php if ($err = session()->getFlashdata('error')) : ?>
            <p class="muted" style="margin-bottom: 1rem; color: var(--danger, #f87171);"><?= esc($err) ?></p>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (is_array($errors) && $errors !== []) : ?>
            <ul class="muted" style="margin-bottom: 1rem;">
                <?php foreach ($errors as $e) : ?>
                    <li><?= esc(is_array($e) ? implode(' ', $e) : $e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php helper('form'); ?>
        <?= form_open(site_url('main/login'), ['class' => 'stack']) ?>
            <?= csrf_field() ?>
            <div>
                <label for="email">Email</label>
                <input class="app-topbar__search-input" style="width:100%; margin-top:0.25rem;" type="email" name="email" id="email"
                       value="<?= esc(old('email')) ?>" required autocomplete="username">
            </div>
            <div style="margin-top: 1rem;">
                <label for="password">Password</label>
                <input class="app-topbar__search-input" style="width:100%; margin-top:0.25rem;" type="password" name="password" id="password"
                       required autocomplete="current-password">
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn--primary" style="width:100%;">Sign in</button>
            </div>
        <?= form_close() ?>

        <p class="muted" style="margin-top: 2rem; font-size: 0.875rem;">
            <a href="<?= site_url('mockup') ?>">Open UI mockup</a>
        </p>
    </div>
</body>
</html>
