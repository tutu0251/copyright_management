<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Licensees</h1><p>Organizations and individuals you license works to.</p></div>
	<?php if (has_perm('licensees.create')): ?>
		<a class="btn btn--primary" href="<?php echo site_url('licensees/create'); ?>">New licensee</a>
	<?php endif; ?>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr><th>Name</th><th>Organization</th><th>Contact email</th><th class="right">Actions</th></tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="4" class="empty">No licensees yet.</td></tr>
		<?php else: foreach ($items as $l): ?>
			<tr>
				<td><?php echo html_escape($l['name']); ?></td>
				<td class="muted"><?php echo html_escape($l['organization']); ?></td>
				<td class="muted"><?php echo html_escape($l['contact_email']); ?></td>
				<td class="right nowrap">
					<div class="actions">
						<?php if (has_perm('licensees.update')): ?>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('licensees/edit/'.$l['id']); ?>">Edit</a>
						<?php endif; ?>
						<?php if (has_perm('licensees.delete')): ?>
							<?php echo form_open('licensees/delete/'.$l['id'], array('onsubmit' => "return confirm('Remove this licensee?')", 'style' => 'display:inline')); ?>
								<button class="btn btn--danger btn--sm" type="submit">Delete</button>
							<?php echo form_close(); ?>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
