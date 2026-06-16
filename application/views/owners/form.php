<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$is_edit = ($mode === 'edit');
$legal = set_value('legal_name', $item ? $item['legal_name'] : '');
$type  = set_value('entity_type', $item ? $item['entity_type'] : 'individual');
$email = set_value('email', $item ? $item['email'] : '');
$action = $is_edit ? 'owners/edit/'.$item['id'] : 'owners/create';
?>
<div class="page-head">
	<div><h1><?php echo $is_edit ? 'Edit owner' : 'New owner'; ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo site_url('owners'); ?>">Back</a>
</div>
<div class="card" style="max-width:640px">
	<?php echo form_open($action); ?>
		<div class="form-row">
			<label for="legal_name">Legal name</label>
			<input type="text" id="legal_name" name="legal_name" value="<?php echo html_escape($legal); ?>" required autofocus>
		</div>
		<div class="form-grid">
			<div class="form-row">
				<label for="entity_type">Entity type</label>
				<select id="entity_type" name="entity_type">
					<?php foreach (array('individual', 'company', 'estate', 'trust') as $t): ?>
						<option value="<?php echo $t; ?>"<?php echo ($type === $t) ? ' selected' : ''; ?>><?php echo ucfirst($t); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="email">Email</label>
				<input type="email" id="email" name="email" value="<?php echo html_escape($email); ?>">
			</div>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn--primary"><?php echo $is_edit ? 'Save changes' : 'Create owner'; ?></button>
			<a class="btn btn--ghost" href="<?php echo site_url('owners'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
