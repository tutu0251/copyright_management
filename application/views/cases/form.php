<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$is_edit = ($mode === 'edit');
$v = function ($k, $d = '') use ($item) { return set_value($k, $item ? $item[$k] : $d); };
$action = $is_edit ? 'cases/edit/'.$item['id'] : 'cases/create';
$statuses = array('open', 'investigating', 'resolved', 'rejected', 'closed');
$priorities = array('low', 'medium', 'high', 'urgent');
?>
<div class="page-head">
	<div><h1><?php echo $is_edit ? 'Edit case' : 'Open case'; ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo site_url('cases'); ?>">Back</a>
</div>
<div class="card" style="max-width:720px">
	<?php echo form_open($action); ?>
		<div class="form-row">
			<label for="title">Title</label>
			<input type="text" id="title" name="title" value="<?php echo html_escape($v('title')); ?>" required autofocus>
		</div>
		<div class="form-grid">
			<div class="form-row">
				<label for="work_id">Work</label>
				<select id="work_id" name="work_id">
					<option value="">— Select work —</option>
					<?php $cur = (int) $v('work_id'); foreach ($works as $id => $title): ?>
						<option value="<?php echo $id; ?>"<?php echo ($cur === $id) ? ' selected' : ''; ?>><?php echo html_escape($title); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="case_status">Status</label>
				<select id="case_status" name="case_status">
					<?php $cur = $v('case_status', 'open'); foreach ($statuses as $s): ?>
						<option value="<?php echo $s; ?>"<?php echo ($cur === $s) ? ' selected' : ''; ?>><?php echo ucfirst($s); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="priority">Priority</label>
				<select id="priority" name="priority">
					<?php $cur = $v('priority', 'medium'); foreach ($priorities as $p): ?>
						<option value="<?php echo $p; ?>"<?php echo ($cur === $p) ? ' selected' : ''; ?>><?php echo ucfirst($p); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="form-row">
			<label for="description">Description</label>
			<textarea id="description" name="description"><?php echo html_escape($v('description')); ?></textarea>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn--primary"><?php echo $is_edit ? 'Save changes' : 'Open case'; ?></button>
			<a class="btn btn--ghost" href="<?php echo site_url('cases'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
