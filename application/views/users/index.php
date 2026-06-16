<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Users</h1><p>Manage accounts and access.</p></div>
</div>

<?php if ( ! empty($error)): ?>
	<div class="alert alert--error"><?php echo html_escape($error); ?></div>
<?php endif; ?>

<div class="grid grid--2">
	<div class="card">
		<h2>Accounts</h2>
		<div class="table-wrap">
			<table class="data">
				<thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th class="right">Actions</th></tr></thead>
				<tbody>
				<?php foreach ($items as $u): ?>
					<tr>
						<td><?php echo html_escape($u['display_name']); ?></td>
						<td class="muted"><?php echo html_escape($u['email']); ?></td>
						<td class="muted"><?php echo html_escape($u['role_names'] ? $u['role_names'] : '—'); ?></td>
						<td><?php echo ((int) $u['is_active'] === 1)
							? '<span class="badge badge--green">Active</span>'
							: '<span class="badge badge--red">Inactive</span>'; ?></td>
						<td class="right nowrap">
							<?php echo form_open('users/toggle/'.$u['id'], array('style' => 'display:inline')); ?>
								<button class="btn btn--ghost btn--sm" type="submit"><?php echo ((int) $u['is_active'] === 1) ? 'Deactivate' : 'Activate'; ?></button>
							<?php echo form_close(); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="card">
		<h2>Create user</h2>
		<?php echo form_open('users/create'); ?>
			<div class="form-row">
				<label for="display_name">Name</label>
				<input type="text" id="display_name" name="display_name" value="<?php echo set_value('display_name'); ?>" required>
			</div>
			<div class="form-row">
				<label for="email">Email</label>
				<input type="email" id="email" name="email" value="<?php echo set_value('email'); ?>" required>
			</div>
			<div class="form-row">
				<label for="password">Password</label>
				<input type="password" id="password" name="password" required>
				<span class="help">At least 8 characters.</span>
			</div>
			<div class="form-row">
				<label for="role">Role</label>
				<select id="role" name="role">
					<?php foreach ($roles as $r): ?>
						<option value="<?php echo html_escape($r['slug']); ?>"<?php echo ($r['slug'] === 'viewer') ? ' selected' : ''; ?>><?php echo html_escape($r['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button type="submit" class="btn btn--primary">Create user</button>
		<?php echo form_close(); ?>
	</div>
</div>
