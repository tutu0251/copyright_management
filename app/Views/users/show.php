<?php
$user = $user ?? [];
$roles = $roles ?? [];
$message = $message ?? session()->getFlashdata('message');
$uid = (int) ($user['id'] ?? 0);
$active = (int) ($user['is_active'] ?? 0) === 1;
$roleNames = [];
foreach ($roles as $r) {
    $roleNames[] = (string) ($r['name'] ?? $r['slug'] ?? '');
}
$rolesOut = $roleNames === [] ? lang('App.users_value_none') : implode(', ', $roleNames);
$last = $user['last_login_at'] ?? null;
$lastOut = ($last !== null && $last !== '') ? (string) $last : lang('App.users_value_none');
$me = auth_user();
$isSelf = $me !== null && $me['id'] === $uid;
?>

<?php if ($message) : ?>
    <p class="muted" role="status"><?= esc((string) $message) ?></p>
<?php endif; ?>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('users') ?>"><?= esc(lang('App.users_back_list')) ?></a>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('users.manage')) : ?>
            <a class="btn btn--secondary" href="<?= site_url('users/' . $uid . '/edit') ?>"><?= esc(lang('App.users_action_edit')) ?></a>
            <?php if ($active && ! $isSelf) : ?>
                <?= form_open(site_url('users/' . $uid . '/deactivate'), ['style' => 'display:inline;', 'onsubmit' => htmlspecialchars('return confirm(' . json_encode(lang('App.users_confirm_deactivate'), JSON_UNESCAPED_UNICODE) . ');', ENT_QUOTES, 'UTF-8')]) ?>
                    <button type="submit" class="btn btn--ghost"><?= esc(lang('App.users_action_deactivate')) ?></button>
                <?= form_close() ?>
            <?php elseif (! $active) : ?>
                <?= form_open(site_url('users/' . $uid . '/activate'), ['style' => 'display:inline;']) ?>
                    <button type="submit" class="btn btn--primary"><?= esc(lang('App.users_action_activate')) ?></button>
                <?= form_close() ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="max-width: 720px;">
    <h2 class="card__title"><?= esc(lang('App.users_show_account')) ?></h2>
    <dl class="dl-grid">
        <dt><?= esc(lang('App.users_form_display_name')) ?></dt>
        <dd><strong><?= esc((string) ($user['display_name'] ?? '')) ?></strong></dd>
        <dt><?= esc(lang('App.users_form_email')) ?></dt>
        <dd><?= esc((string) ($user['email'] ?? '')) ?></dd>
        <dt><?= esc(lang('App.users_col_status')) ?></dt>
        <dd><?= $active ? esc(lang('App.users_status_active')) : esc(lang('App.users_status_inactive')) ?></dd>
        <dt><?= esc(lang('App.users_show_roles')) ?></dt>
        <dd><?= esc($rolesOut) ?></dd>
        <dt><?= esc(lang('App.users_col_created')) ?></dt>
        <dd><?= esc($user['created_at'] !== null && $user['created_at'] !== '' ? (string) $user['created_at'] : lang('App.users_value_none')) ?></dd>
        <dt><?= esc(lang('App.users_col_last_login')) ?></dt>
        <dd><?= esc($lastOut) ?></dd>
    </dl>
</div>
