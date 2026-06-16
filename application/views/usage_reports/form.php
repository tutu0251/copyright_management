<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$is_edit = ($mode === 'edit');
$v = function ($k, $d = '') use ($item) { return set_value($k, $item ? $item[$k] : $d); };
$action = $is_edit ? 'usage_reports/edit/'.$item['id'] : 'usage_reports/create';
$types = array('suspected', 'infringement', 'licensed', 'fair_use', 'cleared');
$detected = $v('detected_at');
$detected_input = $detected ? str_replace(' ', 'T', substr($detected, 0, 16)) : '';
?>
<div class="page-head">
	<div><h1><?php echo $is_edit ? 'Edit usage report' : 'New usage report'; ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo site_url('usage_reports'); ?>">Back</a>
</div>
<div class="card" style="max-width:700px">
	<?php echo form_open($action); ?>
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
				<label for="usage_type">Usage type</label>
				<select id="usage_type" name="usage_type">
					<?php $cur = $v('usage_type', 'suspected'); foreach ($types as $t): ?>
						<option value="<?php echo $t; ?>"<?php echo ($cur === $t) ? ' selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $t)); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="detected_source">Detected source</label>
				<input type="text" id="detected_source" name="detected_source" value="<?php echo html_escape($v('detected_source')); ?>">
			</div>
			<div class="form-row">
				<label for="detected_at">Detected at</label>
				<input type="datetime-local" id="detected_at" name="detected_at" value="<?php echo html_escape($detected_input); ?>">
			</div>
		</div>
		<div class="form-row">
			<label for="notes">Notes</label>
			<textarea id="notes" name="notes"><?php echo html_escape($v('notes')); ?></textarea>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn--primary"><?php echo $is_edit ? 'Save changes' : 'Log report'; ?></button>
			<a class="btn btn--ghost" href="<?php echo site_url('usage_reports'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
