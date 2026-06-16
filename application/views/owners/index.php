<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Owners</h1><p>Rights holders linked to your catalog.</p></div>
	<?php if (has_perm('owners.create')): ?>
		<a class="btn btn--primary" href="<?php echo site_url('owners/create'); ?>">New owner</a>
	<?php endif; ?>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr><th>Legal name</th><th>Type</th><th>Email</th><th class="right">Actions</th></tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="4" class="empty">No owners yet.</td></tr>
		<?php else: foreach ($items as $o): ?>
			<tr>
				<td><?php echo html_escape($o['legal_name']); ?></td>
				<td><span class="badge badge--gray"><?php echo html_escape($o['entity_type']); ?></span></td>
				<td class="muted"><?php echo html_escape($o['email']); ?></td>
				<td class="right nowrap">
					<div class="actions">
						<?php if (has_perm('owners.update')): ?>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('owners/edit/'.$o['id']); ?>">Edit</a>
						<?php endif; ?>
						<?php if (has_perm('owners.delete')): ?>
							<?php echo form_open('owners/delete/'.$o['id'], array('onsubmit' => "return confirm('Remove this owner?')", 'style' => 'display:inline')); ?>
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
