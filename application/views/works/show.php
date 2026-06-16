<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div>
		<h1><?php echo html_escape($work['title']); ?></h1>
		<p><?php echo badge($work['work_type']); ?> <?php echo badge($work['copyright_status']); ?> <?php echo badge($work['risk_level']); ?></p>
	</div>
	<div class="actions">
		<?php if (has_perm('works.update')): ?>
			<a class="btn btn--ghost" href="<?php echo site_url('works/edit/'.$work['id']); ?>">Edit</a>
		<?php endif; ?>
		<a class="btn btn--ghost" href="<?php echo site_url('works'); ?>">Back</a>
	</div>
</div>

<div class="grid grid--2">
	<div class="card">
		<h2>Details</h2>
		<table class="data">
			<tr><th>Creator</th><td><?php echo html_escape($work['creator']) ?: '—'; ?></td></tr>
			<tr><th>Owner</th><td><?php echo html_escape($work['owner']) ?: '—'; ?></td></tr>
			<tr><th>Registered</th><td><?php echo $work['registered_at'] ? html_escape($work['registered_at']) : '—'; ?></td></tr>
			<tr><th>Description</th><td><?php echo nl2br(html_escape($work['description'])) ?: '—'; ?></td></tr>
		</table>
	</div>

	<div class="card">
		<h2>Assets</h2>
		<?php if (has_perm('works.update')): ?>
			<?php echo form_open_multipart('assets/upload/'.$work['id']); ?>
				<div class="flex">
					<input type="file" name="file" required>
					<button type="submit" class="btn btn--primary btn--sm">Upload</button>
				</div>
			<?php echo form_close(); ?>
		<?php endif; ?>

		<table class="data mt">
			<thead><tr><th>File</th><th>Type</th><th class="right">Size</th><th class="right">Actions</th></tr></thead>
			<tbody>
			<?php if (empty($assets)): ?>
				<tr><td colspan="4" class="empty">No assets uploaded.</td></tr>
			<?php else: foreach ($assets as $a): ?>
				<tr>
					<td><?php echo html_escape($a['filename']); ?></td>
					<td class="muted"><?php echo html_escape($a['content_type']); ?></td>
					<td class="right nowrap"><?php echo human_size($a['size']); ?></td>
					<td class="right nowrap">
						<div class="actions">
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('assets/download/'.$a['id']); ?>" target="_blank">Preview</a>
							<a class="btn btn--ghost btn--sm" href="<?php echo site_url('assets/download/'.$a['id'].'?download=1'); ?>">Download</a>
							<?php if (has_perm('works.update')): ?>
								<?php echo form_open('assets/delete/'.$a['id'], array('onsubmit' => "return confirm('Delete this file?')", 'style' => 'display:inline')); ?>
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
</div>
