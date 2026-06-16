<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Works</h1><p>Your registered copyright works and their assets.</p></div>
	<?php if (has_perm('works.create')): ?>
		<a class="btn btn--primary" href="<?php echo site_url('works/create'); ?>">Register work</a>
	<?php endif; ?>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr>
			<th>Title</th><th>Type</th><th>Creator</th><th>Status</th><th>Risk</th>
			<th class="right">Assets</th><th>Registered</th><th class="right">Actions</th>
		</tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="8" class="empty">No works yet.</td></tr>
		<?php else: foreach ($items as $w): ?>
			<tr>
				<td><a href="<?php echo site_url('works/show/'.$w['id']); ?>"><?php echo html_escape($w['title']); ?></a></td>
				<td><span class="badge badge--blue"><?php echo html_escape($w['work_type']); ?></span></td>
				<td class="muted"><?php echo html_escape($w['creator']); ?></td>
				<td><?php echo badge($w['copyright_status']); ?></td>
				<td><?php echo badge($w['risk_level']); ?></td>
				<td class="right nowrap"><?php echo (int) $w['asset_count']; ?>
					<span class="muted">(<?php echo human_size($w['asset_size']); ?>)</span></td>
				<td class="muted nowrap"><?php echo $w['registered_at'] ? html_escape($w['registered_at']) : '—'; ?></td>
				<td class="right nowrap">
					<div class="actions">
						<a class="btn btn--ghost btn--sm" href="<?php echo site_url('works/show/'.$w['id']); ?>">View</a>
						<?php if (has_perm('works.update')): ?>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('works/edit/'.$w['id']); ?>">Edit</a>
						<?php endif; ?>
						<?php if (has_perm('works.delete')): ?>
							<?php echo form_open('works/delete/'.$w['id'], array('onsubmit' => "return confirm('Remove this work?')", 'style' => 'display:inline')); ?>
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
