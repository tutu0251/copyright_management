<?php
$users = $users ?? [];
$msg = session()->getFlashdata('message');
$err = session()->getFlashdata('errors');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc((string) $msg) ?></p>
<?php endif; ?>
<?php if (is_array($err) && $err !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($err as $e) : ?>
                <li><?= esc(is_array($e) ? json_encode($e) : (string) $e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<p class="page-intro"><?= esc(lang('App.users_intro')) ?></p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow"></div>
    <div class="toolbar__right">
        <?php if (user_can('users.manage')) : ?>
            <a class="btn btn--primary" href="<?= site_url('users/create') ?>"><?= esc(lang('App.users_action_create')) ?></a>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th><?= esc(lang('App.users_col_name')) ?></th>
            <th><?= esc(lang('App.users_col_email')) ?></th>
            <th><?= esc(lang('App.users_col_status')) ?></th>
            <th><?= esc(lang('App.users_col_roles')) ?></th>
            <th><?= esc(lang('App.users_col_created')) ?></th>
            <th><?= esc(lang('App.users_col_last_login')) ?></th>
            <th><?= esc(lang('App.users_col_actions')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($users === []) : ?>
            <tr>
                <td colspan="7" class="muted"><?= esc(lang('App.users_empty')) ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ($users as $u) : ?>
                <?php
                $uid = (int) ($u['id'] ?? 0);
                $active = (int) ($u['is_active'] ?? 0) === 1;
                $roleBits = [];
                foreach ($u['roles'] ?? [] as $r) {
                    $roleBits[] = (string) ($r['name'] ?? $r['slug'] ?? '');
                }
                $roleLabel = $roleBits === [] ? lang('App.users_value_none') : implode(', ', $roleBits);
                $last = $u['last_login_at'] ?? null;
                $lastOut = ($last !== null && $last !== '') ? (string) $last : lang('App.users_value_none');
                ?>
                <tr>
                    <td><strong><?= esc((string) ($u['display_name'] ?? '')) ?></strong></td>
                    <td><?= esc((string) ($u['email'] ?? '')) ?></td>
                    <td><?= $active ? esc(lang('App.users_status_active')) : esc(lang('App.users_status_inactive')) ?></td>
                    <td><?= esc($roleLabel) ?></td>
                    <td><?= esc($u['created_at'] !== null && $u['created_at'] !== '' ? (string) $u['created_at'] : lang('App.users_value_none')) ?></td>
                    <td><?= esc($lastOut) ?></td>
                    <td class="table-actions">
                        <a class="btn btn--ghost btn--sm" href="<?= site_url('users/' . $uid) ?>"><?= esc(lang('App.users_action_view')) ?></a>
                        <?php if (user_can('users.manage')) : ?>
                            <a class="btn btn--ghost btn--sm" href="<?= site_url('users/' . $uid . '/edit') ?>"><?= esc(lang('App.users_action_edit')) ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>
