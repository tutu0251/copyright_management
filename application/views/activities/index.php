<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Activity log</h1><p>Audit trail of actions across the workspace.</p></div>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
		<tbody>
		<?php if (empty($items)): ?>
			<tr><td colspan="5" class="empty">No activity recorded.</td></tr>
		<?php else: foreach ($items as $a): ?>
			<tr>
				<td class="muted nowrap"><?php echo html_escape($a['created_at']); ?></td>
				<td><?php echo html_escape($a['actor_name'] ? $a['actor_name'] : ($a['actor_email'] ? $a['actor_email'] : '—')); ?></td>
				<td><span class="badge badge--gray"><?php echo html_escape($a['action_type']); ?></span></td>
				<td class="muted"><?php echo html_escape(trim($a['entity_type'].' '.$a['entity_id'])) ?: '—'; ?></td>
				<td class="muted"><?php echo $a['metadata'] ? html_escape($a['metadata']) : '—'; ?></td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
