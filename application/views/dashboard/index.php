<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$max = 1;
foreach ($series as $s) { if ($s['value'] > $max) $max = $s['value']; }
$case_total = 0;
foreach ($cases_status as $c) { $case_total += (int) $c['cnt']; }
?>
<div class="page-head">
	<div><h1>Dashboard</h1><p>Workspace overview.</p></div>
</div>

<div class="grid grid--stats">
	<?php foreach ($stats as $s): ?>
		<div class="stat">
			<div class="stat__label"><?php echo html_escape($s[0]); ?></div>
			<div class="stat__value"><?php echo html_escape($s[1]); ?></div>
		</div>
	<?php endforeach; ?>
</div>

<div class="grid grid--2 mt">
	<div class="card">
		<h2>Works registered (12 months)</h2>
		<div style="display:flex; align-items:flex-end; gap:6px; height:180px; padding-top:10px">
			<?php foreach ($series as $s): $h = (int) round(($s['value'] / $max) * 150); ?>
				<div style="flex:1; text-align:center" title="<?php echo html_escape($s['label'].': '.$s['value']); ?>">
					<div style="background:var(--brand); border-radius:4px 4px 0 0; height:<?php echo max($h, 2); ?>px"></div>
					<div class="muted" style="font-size:10px; margin-top:4px"><?php echo html_escape(substr($s['label'], 0, 3)); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="card">
		<h2>Cases by status</h2>
		<?php if (empty($cases_status)): ?>
			<p class="muted">No cases yet.</p>
		<?php else: foreach ($cases_status as $c): $pct = $case_total ? round(((int) $c['cnt'] / $case_total) * 100) : 0; ?>
			<div style="margin-bottom:10px">
				<div class="flex spread" style="font-size:13px">
					<span><?php echo badge($c['case_status']); ?></span>
					<span class="muted"><?php echo (int) $c['cnt']; ?> (<?php echo $pct; ?>%)</span>
				</div>
				<div style="background:var(--soft); border-radius:6px; height:8px; margin-top:4px">
					<div style="background:var(--brand); height:8px; border-radius:6px; width:<?php echo $pct; ?>%"></div>
				</div>
			</div>
		<?php endforeach; endif; ?>
	</div>
</div>

<div class="grid grid--2 mt">
	<div class="card">
		<h2>Recent activity</h2>
		<table class="data">
			<thead><tr><th>When</th><th>Actor</th><th>Action</th></tr></thead>
			<tbody>
			<?php if (empty($activity)): ?>
				<tr><td colspan="3" class="empty">No activity.</td></tr>
			<?php else: foreach ($activity as $a): ?>
				<tr>
					<td class="muted nowrap"><?php echo html_escape($a['created_at']); ?></td>
					<td><?php echo html_escape($a['actor_name'] ? $a['actor_name'] : ($a['actor_email'] ? $a['actor_email'] : '—')); ?></td>
					<td><span class="badge badge--gray"><?php echo html_escape($a['action_type']); ?></span>
						<span class="muted"><?php echo html_escape(trim($a['entity_type'].' '.$a['entity_id'])); ?></span></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>

	<div class="card">
		<h2>Recent usage detections</h2>
		<table class="data">
			<thead><tr><th>Work</th><th>Source</th><th>When</th></tr></thead>
			<tbody>
			<?php if (empty($recent_usage)): ?>
				<tr><td colspan="3" class="empty">No recent detections.</td></tr>
			<?php else: foreach ($recent_usage as $u): ?>
				<tr>
					<td><?php echo html_escape($u['work_title'] ? $u['work_title'] : '—'); ?></td>
					<td class="muted"><?php echo html_escape($u['detected_source']); ?></td>
					<td class="muted nowrap"><?php echo html_escape($u['detected_at']); ?></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
		<h2 class="mt">Recent works</h2>
		<ul>
			<?php foreach ($pinned as $w): ?>
				<li><a href="<?php echo site_url('works/show/'.$w['id']); ?>"><?php echo html_escape($w['title']); ?></a>
					— <?php echo badge($w['copyright_status']); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
