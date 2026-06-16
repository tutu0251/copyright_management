<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$is_edit = ($mode === 'edit');
$v = function ($k, $d = '') use ($item) { return set_value($k, $item ? $item[$k] : $d); };
$action = $is_edit ? 'licenses/edit/'.$item['id'] : 'licenses/create';

$types = array('non_exclusive' => 'Non-exclusive', 'exclusive' => 'Exclusive', 'sole' => 'Sole', 'royalty_free' => 'Royalty-free');
$statuses = array('draft', 'active', 'expired', 'cancelled', 'pending');
$payments = array('unpaid', 'partial', 'paid');
?>
<div class="page-head">
	<div><h1><?php echo $is_edit ? 'Edit license' : 'New license'; ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo site_url('licenses'); ?>">Back</a>
</div>
<div class="card" style="max-width:760px">
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
				<label for="licensee_id">Licensee</label>
				<select id="licensee_id" name="licensee_id">
					<option value="">— Select licensee —</option>
					<?php $cur = (int) $v('licensee_id'); foreach ($licensees as $id => $name): ?>
						<option value="<?php echo $id; ?>"<?php echo ($cur === $id) ? ' selected' : ''; ?>><?php echo html_escape($name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="license_type">License type</label>
				<select id="license_type" name="license_type">
					<?php $cur = $v('license_type', 'non_exclusive'); foreach ($types as $val => $lbl): ?>
						<option value="<?php echo $val; ?>"<?php echo ($cur === $val) ? ' selected' : ''; ?>><?php echo $lbl; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="license_status">Status</label>
				<select id="license_status" name="license_status">
					<?php $cur = $v('license_status', 'draft'); foreach ($statuses as $s): ?>
						<option value="<?php echo $s; ?>"<?php echo ($cur === $s) ? ' selected' : ''; ?>><?php echo ucfirst($s); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="payment_status">Payment</label>
				<select id="payment_status" name="payment_status">
					<?php $cur = $v('payment_status', 'unpaid'); foreach ($payments as $p): ?>
						<option value="<?php echo $p; ?>"<?php echo ($cur === $p) ? ' selected' : ''; ?>><?php echo ucfirst($p); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="fee_amount">Fee amount</label>
				<input type="number" step="0.01" min="0" id="fee_amount" name="fee_amount" value="<?php echo html_escape($v('fee_amount', '0')); ?>">
			</div>
			<div class="form-row">
				<label for="start_date">Start date</label>
				<input type="date" id="start_date" name="start_date" value="<?php echo html_escape($v('start_date')); ?>">
			</div>
			<div class="form-row">
				<label for="end_date">End date</label>
				<input type="date" id="end_date" name="end_date" value="<?php echo html_escape($v('end_date')); ?>">
			</div>
		</div>
		<div class="form-row">
			<label for="territory">Territory</label>
			<input type="text" id="territory" name="territory" value="<?php echo html_escape($v('territory')); ?>" placeholder="e.g. Worldwide, North America">
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn--primary"><?php echo $is_edit ? 'Save changes' : 'Create license'; ?></button>
			<a class="btn btn--ghost" href="<?php echo site_url('licenses'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
