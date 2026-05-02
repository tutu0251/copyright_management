<?php
$permissions = $permissions ?? [];
?>

<p class="page-intro">Roles, permissions, and a disabled sign-in shell. Replace with your identity provider and policy engine.</p>

<div class="grid grid--2">
    <div class="card">
        <h2 class="card__title">User roles</h2>
        <?= $this->include('components/table') ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Users</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role) : ?>
                    <tr>
                        <td><strong><?= esc($role['name']) ?></strong></td>
                        <td><?= esc($role['users']) ?></td>
                        <td><?= esc($role['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= $this->include('components/table_end') ?>
    </div>
    <div class="card">
        <h2 class="card__title">Mock sign-in</h2>
        <form class="stack" action="#" method="post" onsubmit="return false;">
            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" type="email" value="alex.morgan@example.com" disabled>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input class="input" id="password" type="password" value="not-used" disabled>
            </div>
            <button type="submit" class="btn btn--primary" disabled>Sign in</button>
            <p class="muted">Authentication is intentionally disabled for this prototype.</p>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Permission matrix (mock)</h2>
    <p class="muted" style="margin: 0 0 0.75rem;">Boolean grid for stakeholder review — not enforced server-side.</p>
    <?= $this->include('components/table') ?>
    <table class="data-table perm-table">
        <thead>
            <tr>
                <th>Module</th>
                <th>Admin</th>
                <th>Manager</th>
                <th>Legal</th>
                <th>Viewer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permissions as $row) : ?>
                <tr>
                    <td><strong><?= esc($row['module']) ?></strong></td>
                    <td><span class="<?= ! empty($row['admin']) ? 'ok' : 'no' ?>"><?= ! empty($row['admin']) ? '✓' : '—' ?></span></td>
                    <td><span class="<?= ! empty($row['manager']) ? 'ok' : 'no' ?>"><?= ! empty($row['manager']) ? '✓' : '—' ?></span></td>
                    <td><span class="<?= ! empty($row['legal']) ? 'ok' : 'no' ?>"><?= ! empty($row['legal']) ? '✓' : '—' ?></span></td>
                    <td><span class="<?= ! empty($row['viewer']) ? 'ok' : 'no' ?>"><?= ! empty($row['viewer']) ? '✓' : '—' ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->include('components/table_end') ?>
</div>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title">Danger zone (visual)</h2>
    <p class="muted">Destructive controls are disabled.</p>
    <div class="table-actions" style="margin-top: 0.75rem;">
        <button type="button" class="btn btn--secondary" disabled>Reset mock data</button>
        <button type="button" class="btn btn--secondary" disabled>Revoke all sessions</button>
    </div>
</div>
