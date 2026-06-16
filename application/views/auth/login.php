<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="auth-wrap">
	<div class="auth-card">
		<h1>Copyright Manager</h1>
		<p class="sub">Sign in to your workspace</p>

		<?php if ($flash = $this->session->flashdata('ok')): ?>
			<div class="alert alert--ok"><?php echo html_escape($flash); ?></div>
		<?php endif; ?>
		<?php if ( ! empty($error)): ?>
			<div class="alert alert--error"><?php echo html_escape($error); ?></div>
		<?php endif; ?>

		<?php echo form_open('auth/login'); ?>
			<div class="form-row">
				<label for="email">Email</label>
				<input type="email" id="email" name="email" value="<?php echo html_escape($email); ?>" required autofocus>
			</div>
			<div class="form-row">
				<label for="password">Password</label>
				<input type="password" id="password" name="password" required>
			</div>
			<button type="submit" class="btn btn--primary" style="width:100%">Sign in</button>
		<?php echo form_close(); ?>

		<div class="auth-foot">
			No account? <a href="<?php echo site_url('auth/register'); ?>">Create one</a>
		</div>
	</div>
</div>
