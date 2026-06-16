<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$is_edit = ($mode === 'edit');
$v = function ($k, $d = '') use ($item) { return set_value($k, $item ? $item[$k] : $d); };
$action = $is_edit ? 'works/edit/'.$item['id'] : 'works/create';

$types = array('Text', 'Image', 'Audio', 'Video', 'Software', 'Other');
$statuses = array('draft', 'registered', 'pending', 'expired', 'cancelled');
$risks = array('Low', 'Medium', 'High');
?>
<div class="page-head">
	<div><h1><?php echo $is_edit ? 'Edit work' : 'Register work'; ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo site_url('works'); ?>">Back</a>
</div>
<div class="card" style="max-width:760px">
	<?php echo form_open($action); ?>
		<div class="form-row">
			<label for="title">Title</label>
			<input type="text" id="title" name="title" value="<?php echo html_escape($v('title')); ?>" required autofocus>
		</div>
		<div class="form-grid">
			<div class="form-row">
				<label for="work_type">Type</label>
				<select id="work_type" name="work_type">
					<?php $cur = $v('work_type', 'Text'); foreach ($types as $t): ?>
						<option value="<?php echo $t; ?>"<?php echo ($cur === $t) ? ' selected' : ''; ?>><?php echo $t; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="risk_level">Risk level</label>
				<select id="risk_level" name="risk_level">
					<?php $cur = $v('risk_level', 'Low'); foreach ($risks as $r): ?>
						<option value="<?php echo $r; ?>"<?php echo ($cur === $r) ? ' selected' : ''; ?>><?php echo $r; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="creator">Creator</label>
				<input type="text" id="creator" name="creator" value="<?php echo html_escape($v('creator')); ?>">
			</div>
			<div class="form-row">
				<label for="owner">Owner (display name)</label>
				<input type="text" id="owner" name="owner" value="<?php echo html_escape($v('owner')); ?>">
			</div>
			<div class="form-row">
				<label for="copyright_status">Copyright status</label>
				<select id="copyright_status" name="copyright_status">
					<?php $cur = $v('copyright_status', 'draft'); foreach ($statuses as $s): ?>
						<option value="<?php echo $s; ?>"<?php echo ($cur === $s) ? ' selected' : ''; ?>><?php echo ucfirst($s); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="registered_at">Registration date</label>
				<input type="date" id="registered_at" name="registered_at" value="<?php echo html_escape($v('registered_at')); ?>">
			</div>
		</div>
		<div class="form-row">
			<label for="description">Description</label>
			<textarea id="description" name="description"><?php echo html_escape($v('description')); ?></textarea>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn--primary"><?php echo $is_edit ? 'Save changes' : 'Register work'; ?></button>
			<a class="btn btn--ghost" href="<?php echo site_url('works'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
