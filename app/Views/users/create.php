<?php
$assignableRoles = $assignableRoles ?? [];
$errors = $errors ?? [];
?>

<?php if ($errors !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <h2 class="card__title"><?= esc(lang('App.users_form_errors_title')) ?></h2>
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($errors as $key => $err) : ?>
                <li><?= esc(is_array($err) ? json_encode($err) : (string) $err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('users') ?>"><?= esc(lang('App.users_back_list')) ?></a>
    </div>
</div>

<div class="card" style="max-width: 880px;">
    <h2 class="card__title"><?= esc(lang('App.users_create_title')) ?></h2>
    <?= form_open(site_url('users'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field" style="grid-column: 1 / -1;">
                <label for="display_name"><?= esc(lang('App.users_form_display_name')) ?> <span aria-hidden="true">*</span></label>
                <input class="input" id="display_name" name="display_name" type="text" required value="<?= esc(old('display_name', ''), 'attr') ?>" autocomplete="name">
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="email"><?= esc(lang('App.users_form_email')) ?> <span aria-hidden="true">*</span></label>
                <input class="input" id="email" name="email" type="email" required value="<?= esc(old('email', ''), 'attr') ?>" autocomplete="email">
            </div>
            <div class="field">
                <label for="password"><?= esc(lang('App.users_form_password')) ?> <span aria-hidden="true">*</span></label>
                <input class="input" id="password" name="password" type="password" required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirm"><?= esc(lang('App.users_form_password_confirm')) ?> <span aria-hidden="true">*</span></label>
                <input class="input" id="password_confirm" name="password_confirm" type="password" required autocomplete="new-password">
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <input type="hidden" name="is_active" value="0">
                <label class="checkbox-label" style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" name="is_active" value="1" <?= ((string) old('is_active', '1')) === '1' ? 'checked' : '' ?>>
                    <?= esc(lang('App.users_form_active')) ?>
                </label>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <span><?= esc(lang('App.users_form_roles')) ?> <span aria-hidden="true">*</span></span>
                <p class="muted" style="margin:0.35rem 0 0.5rem;font-size:0.9rem;"><?= esc(lang('App.users_form_roles_hint')) ?></p>
                <div class="stack" style="gap:0.35rem;">
                    <?php foreach ($assignableRoles as $role) : ?>
                        <?php
                        $slug = (string) ($role['slug'] ?? '');
                        $name = (string) ($role['name'] ?? $slug);
                        $oldRoles = old('roles');
                        $checked = is_array($oldRoles) && in_array($slug, $oldRoles, true);
                        ?>
                        <label class="checkbox-label" style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="checkbox" name="roles[]" value="<?= esc($slug, 'attr') ?>" <?= $checked ? 'checked' : '' ?>>
                            <?= esc($name) ?> (<code><?= esc($slug) ?></code>)
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="toolbar" style="margin: 0; padding-top: 0.5rem;">
            <div class="toolbar__left"></div>
            <div class="toolbar__right">
                <a class="btn btn--secondary" href="<?= site_url('users') ?>"><?= esc(lang('App.action_cancel')) ?></a>
                <button type="submit" class="btn btn--primary"><?= esc(lang('App.action_save')) ?></button>
            </div>
        </div>
    <?= form_close() ?>
</div>
