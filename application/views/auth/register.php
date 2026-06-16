<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="auth-wrap">
	<div class="auth-card">
		<h1>Create account</h1>
		<p class="sub">New accounts get read-only (Viewer) access</p>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert--error"><?php echo html_escape($error); ?></div>
		<?php endif; ?>

		<?php echo form_open('auth/register'); ?>
			<div class="form-row">
				<label for="name">Full name</label>
				<input type="text" id="name" name="name" value="<?php echo html_escape($name); ?>" required autofocus>
			</div>
			<div class="form-row">
				<label for="email">Email</label>
				<input type="email" id="email" name="email" value="<?php echo html_escape($email); ?>" required>
			</div>
			<div class="form-row">
				<label for="password">Password</label>
				<input type="password" id="password" name="password" required>
				<span class="help">At least 8 characters.</span>
			</div>
			<button type="submit" class="btn btn--primary" style="width:100%">Create account</button>
		<?php echo form_close(); ?>

		<div class="auth-foot">
			Already have an account? <a href="<?php echo site_url('auth/login'); ?>">Sign in</a>
		</div>
	</div>
</div>
