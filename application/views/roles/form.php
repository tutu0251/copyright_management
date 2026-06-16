<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Edit role — <?php echo html_escape($role['name']); ?></h1><p>Toggle the permissions this role grants.</p></div>
	<a class="btn btn--ghost" href="<?php echo site_url('roles'); ?>">Back</a>
</div>

<div class="card">
	<?php echo form_open('roles/edit/'.$role['id']); ?>
		<div class="grid grid--2">
		<?php foreach ($groups as $group => $perms): ?>
			<div>
				<h2 style="text-transform:capitalize"><?php echo html_escape(str_replace('_', ' ', $group)); ?></h2>
				<?php foreach ($perms as $p): ?>
					<label style="display:flex; gap:8px; align-items:flex-start; margin-bottom:8px; font-weight:400">
						<input type="checkbox" name="permissions[]" value="<?php echo html_escape($p['slug']); ?>"
							<?php echo in_array($p['slug'], $granted, TRUE) ? 'checked' : ''; ?>>
						<span>
							<strong><?php echo html_escape($p['name']); ?></strong><br>
							<span class="muted" style="font-size:12px"><?php echo html_escape($p['description']); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		</div>
		<div class="form-actions mt">
			<button type="submit" class="btn btn--primary">Save permissions</button>
			<a class="btn btn--ghost" href="<?php echo site_url('roles'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
