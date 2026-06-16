<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="page-head">
	<div><h1>Roles &amp; permissions</h1><p>Control what each role can do.</p></div>
</div>

<div class="table-wrap">
	<table class="data">
		<thead><tr><th>Role</th><th>Slug</th><th>Description</th><th class="right">Permissions</th><th class="right">Actions</th></tr></thead>
		<tbody>
		<?php foreach ($items as $r): ?>
			<tr>
				<td><?php echo html_escape($r['name']); ?></td>
				<td class="muted"><code><?php echo html_escape($r['slug']); ?></code></td>
				<td class="muted"><?php echo html_escape($r['description']); ?></td>
				<td class="right"><span class="badge badge--blue"><?php echo (int) $r['permission_count']; ?></span></td>
				<td class="right nowrap">
					<a class="btn btn--ghost btn--sm" href="<?php echo site_url('roles/edit/'.$r['id']); ?>">Edit permissions</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
