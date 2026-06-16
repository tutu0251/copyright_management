<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$is_edit = ($mode === 'edit');
$name  = set_value('name', $item ? $item['name'] : '');
$org   = set_value('organization', $item ? $item['organization'] : '');
$email = set_value('contact_email', $item ? $item['contact_email'] : '');
$notes = set_value('notes', $item ? $item['notes'] : '');
$action = $is_edit ? 'licensees/edit/'.$item['id'] : 'licensees/create';
?>
<div class="page-head">
	<div><h1><?php echo $is_edit ? 'Edit licensee' : 'New licensee'; ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo site_url('licensees'); ?>">Back</a>
</div>
<div class="card" style="max-width:640px">
	<?php echo form_open($action); ?>
		<div class="form-row">
			<label for="name">Name</label>
			<input type="text" id="name" name="name" value="<?php echo html_escape($name); ?>" required autofocus>
		</div>
		<div class="form-grid">
			<div class="form-row">
				<label for="organization">Organization</label>
				<input type="text" id="organization" name="organization" value="<?php echo html_escape($org); ?>">
			</div>
			<div class="form-row">
				<label for="contact_email">Contact email</label>
				<input type="email" id="contact_email" name="contact_email" value="<?php echo html_escape($email); ?>">
			</div>
		</div>
		<div class="form-row">
			<label for="notes">Notes</label>
			<textarea id="notes" name="notes"><?php echo html_escape($notes); ?></textarea>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn--primary"><?php echo $is_edit ? 'Save changes' : 'Create licensee'; ?></button>
			<a class="btn btn--ghost" href="<?php echo site_url('licensees'); ?>">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
