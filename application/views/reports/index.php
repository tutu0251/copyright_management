<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Reports</h1><p>Catalog and licensing summaries.</p></div>
</div>

<div class="grid grid--stats">
	<?php foreach ($summary as $label => $value): ?>
		<div class="stat">
			<div class="stat__label"><?php echo html_escape($label); ?></div>
			<div class="stat__value"><?php echo (int) $value; ?></div>
		</div>
	<?php endforeach; ?>
</div>

<div class="card mt">
	<h2>Works</h2>
	<div class="table-wrap">
		<table class="data">
			<thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Creator</th></tr></thead>
			<tbody>
			<?php if (empty($works)): ?>
				<tr><td colspan="4" class="empty">No works.</td></tr>
			<?php else: foreach ($works as $w): ?>
				<tr>
					<td><?php echo html_escape($w['title']); ?></td>
					<td class="muted"><?php echo html_escape($w['work_type']); ?></td>
					<td><?php echo badge($w['copyright_status']); ?></td>
					<td class="muted"><?php echo html_escape($w['creator']); ?></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="card mt">
	<h2>Licenses</h2>
	<div class="table-wrap">
		<table class="data">
			<thead><tr><th>Work</th><th>Licensee</th><th>Status</th><th>Payment</th><th class="right">Fee</th></tr></thead>
			<tbody>
			<?php if (empty($licenses)): ?>
				<tr><td colspan="5" class="empty">No licenses.</td></tr>
			<?php else: foreach ($licenses as $l): ?>
				<tr>
					<td><?php echo html_escape($l['work_title'] ? $l['work_title'] : '—'); ?></td>
					<td><?php echo html_escape($l['licensee_name'] ? $l['licensee_name'] : '—'); ?></td>
					<td><?php echo badge($l['license_status']); ?></td>
					<td><?php echo badge($l['payment_status']); ?></td>
					<td class="right nowrap">$<?php echo number_format((float) $l['fee_amount'], 2); ?></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
