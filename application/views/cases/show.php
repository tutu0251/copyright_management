<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$statuses = array('open', 'investigating', 'resolved', 'rejected', 'closed');
?>
<div class="page-head">
	<div>
		<h1><?php echo html_escape($item['title']); ?></h1>
		<p><?php echo badge($item['case_status']); ?> <?php echo badge($item['priority']); ?>
		<span class="muted">· Work: <?php echo html_escape($item['work_title'] ? $item['work_title'] : '—'); ?></span></p>
	</div>
	<div class="actions">
		<?php if (has_perm('cases.update')): ?>
			<a class="btn btn--ghost" href="<?php echo site_url('cases/edit/'.$item['id']); ?>">Edit</a>
		<?php endif; ?>
		<a class="btn btn--ghost" href="<?php echo site_url('cases'); ?>">Back</a>
	</div>
</div>

<div class="grid grid--2">
	<div class="card">
		<h2>Description</h2>
		<p><?php echo $item['description'] !== '' ? nl2br(html_escape($item['description'])) : '<span class="muted">No description.</span>'; ?></p>

		<?php if (has_perm('cases.status_update')): ?>
			<h2 class="mt">Change status</h2>
			<?php echo form_open('cases/status/'.$item['id']); ?>
				<div class="flex">
					<select name="case_status">
						<?php foreach ($statuses as $s): ?>
							<option value="<?php echo $s; ?>"<?php echo ($item['case_status'] === $s) ? ' selected' : ''; ?>><?php echo ucfirst($s); ?></option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="btn btn--primary btn--sm">Update status</button>
				</div>
			<?php echo form_close(); ?>
		<?php endif; ?>
	</div>

	<div class="card">
		<h2>Notes</h2>
		<?php if (has_perm('cases.update')): ?>
			<?php echo form_open('cases/note/'.$item['id']); ?>
				<div class="form-row">
					<textarea name="body" placeholder="Add a note…" required></textarea>
				</div>
				<button type="submit" class="btn btn--primary btn--sm">Add note</button>
			<?php echo form_close(); ?>
		<?php endif; ?>

		<div class="mt">
		<?php if (empty($notes)): ?>
			<p class="muted">No notes yet.</p>
		<?php else: foreach ($notes as $n): ?>
			<div style="border-top:1px solid var(--line); padding:10px 0">
				<div class="muted" style="font-size:12px">
					<?php echo html_escape($n['author_name'] ? $n['author_name'] : 'Unknown'); ?>
					· <?php echo html_escape($n['created_at']); ?>
				</div>
				<div><?php echo nl2br(html_escape($n['body'])); ?></div>
			</div>
		<?php endforeach; endif; ?>
		</div>
	</div>
</div>
