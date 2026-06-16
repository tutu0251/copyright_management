<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Licenses</h1><p>License agreements between works and licensees.</p></div>
	<?php if (has_perm('licenses.create')): ?>
		<a class="btn btn--primary" href="<?php echo site_url('licenses/create'); ?>">New license</a>
	<?php endif; ?>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr>
			<th>Work</th><th>Licensee</th><th>Type</th><th>Status</th><th>Payment</th>
			<th class="right">Fee</th><th>Ends</th><th class="right">Actions</th>
		</tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="8" class="empty">No licenses yet.</td></tr>
		<?php else: foreach ($items as $l): ?>
			<tr>
				<td><?php echo html_escape($l['work_title'] ? $l['work_title'] : '—'); ?></td>
				<td><?php echo html_escape($l['licensee_name'] ? $l['licensee_name'] : '—'); ?></td>
				<td class="muted"><?php echo html_escape(str_replace('_', ' ', $l['license_type'])); ?></td>
				<td><?php echo badge($l['license_status']); ?></td>
				<td><?php echo badge($l['payment_status']); ?></td>
				<td class="right nowrap">$<?php echo number_format((float) $l['fee_amount'], 2); ?></td>
				<td class="muted nowrap"><?php echo $l['end_date'] ? html_escape($l['end_date']) : '—'; ?></td>
				<td class="right nowrap">
					<div class="actions">
						<?php if (has_perm('licenses.update')): ?>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('licenses/edit/'.$l['id']); ?>">Edit</a>
						<?php endif; ?>
						<?php if (has_perm('licenses.delete')): ?>
							<?php echo form_open('licenses/delete/'.$l['id'], array('onsubmit' => "return confirm('Remove this license?')", 'style' => 'display:inline')); ?>
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
