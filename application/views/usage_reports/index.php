<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Usage reports</h1><p>Detected and suspected uses of your works.</p></div>
	<?php if (has_perm('usage_reports.create')): ?>
		<a class="btn btn--primary" href="<?php echo site_url('usage_reports/create'); ?>">New report</a>
	<?php endif; ?>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr><th>Work</th><th>Type</th><th>Source</th><th>Detected</th><th class="right">Actions</th></tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="5" class="empty">No usage reports yet.</td></tr>
		<?php else: foreach ($items as $u): ?>
			<tr>
				<td><?php echo html_escape($u['work_title'] ? $u['work_title'] : '—'); ?></td>
				<td><?php echo badge($u['usage_type']); ?></td>
				<td class="muted"><?php echo html_escape($u['detected_source']); ?></td>
				<td class="muted nowrap"><?php echo $u['detected_at'] ? html_escape($u['detected_at']) : '—'; ?></td>
				<td class="right nowrap">
					<div class="actions">
						<?php if (has_perm('usage_reports.update')): ?>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('usage_reports/edit/'.$u['id']); ?>">Edit</a>
						<?php endif; ?>
						<?php if (has_perm('usage_reports.delete')): ?>
							<?php echo form_open('usage_reports/delete/'.$u['id'], array('onsubmit' => "return confirm('Remove this report?')", 'style' => 'display:inline')); ?>
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
