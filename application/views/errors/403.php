<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div>
		<h1>Access denied</h1>
		<p>You don't have permission to perform this action.</p>
	</div>
</div>
<div class="card">
	<p>Your account lacks the required permission<?php echo isset($permission) ? ' <code>'.html_escape($permission).'</code>' : ''; ?>.
	If you believe this is a mistake, ask an administrator to adjust your role.</p>
	<p class="mt"><a class="btn btn--primary" href="<?php echo site_url('dashboard'); ?>">Back to dashboard</a></p>
</div>
