<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Cases</h1><p>Infringement and enforcement cases.</p></div>
	<?php if (has_perm('cases.create')): ?>
		<a class="btn btn--primary" href="<?php echo site_url('cases/create'); ?>">Open case</a>
	<?php endif; ?>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr><th>Title</th><th>Work</th><th>Status</th><th>Priority</th><th class="right">Actions</th></tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="5" class="empty">No cases yet.</td></tr>
		<?php else: foreach ($items as $c): ?>
			<tr>
				<td><a href="<?php echo site_url('cases/show/'.$c['id']); ?>"><?php echo html_escape($c['title']); ?></a></td>
				<td class="muted"><?php echo html_escape($c['work_title'] ? $c['work_title'] : '—'); ?></td>
				<td><?php echo badge($c['case_status']); ?></td>
				<td><?php echo badge($c['priority']); ?></td>
				<td class="right nowrap">
					<div class="actions">
						<a class="btn btn--ghost btn--sm" href="<?php echo site_url('cases/show/'.$c['id']); ?>">View</a>
						<?php if (has_perm('cases.update')): ?>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('cases/edit/'.$c['id']); ?>">Edit</a>
						<?php endif; ?>
						<?php if (has_perm('cases.delete')): ?>
							<?php echo form_open('cases/delete/'.$c['id'], array('onsubmit' => "return confirm('Remove this case?')", 'style' => 'display:inline')); ?>
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
