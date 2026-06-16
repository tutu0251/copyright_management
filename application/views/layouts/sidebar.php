<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$active = isset($active) ? $active : '';
$cu = isset($current_user) ? $current_user : NULL;

// id => [label, url, permission, group]; order preserved, grouped in the sidebar.
$NAV = array(
	array('dashboard', 'Dashboard', 'dashboard', 'dashboard.view', 'Overview'),
	array('works', 'Works', 'works', 'works.view', 'Catalog'),
	array('owners', 'Owners', 'owners', 'owners.view', 'Catalog'),
	array('licensees', 'Licensees', 'licensees', 'licensees.view', 'Catalog'),
	array('licenses', 'Licenses', 'licenses', 'licenses.view', 'Licensing'),
	array('usage_reports', 'Usage reports', 'usage_reports', 'usage_reports.view', 'Licensing'),
	array('cases', 'Cases', 'cases', 'cases.view', 'Enforcement'),
	array('activities', 'Activity', 'activities', 'activities.view', 'Enforcement'),
	array('reports', 'Reports', 'reports', 'reports.view', 'Insights'),
	array('users', 'Users', 'users', 'users.manage', 'Administration'),
	array('roles', 'Roles & permissions', 'roles', 'settings.manage', 'Administration'),
);

$groups = array();
$current_label = 'Dashboard';
foreach ($NAV as $item)
{
	if ( ! has_perm($item[3])) continue;
	$groups[$item[4]][] = $item;
	if ($item[0] === $active) $current_label = $item[1];
}
$initial = strtoupper(substr($cu ? $cu['display_name'] : 'U', 0, 1));
?>
<aside class="app-sidebar">
	<div class="app-brand">
		<span class="app-brand__mark">CM</span>
		<div>
			<div class="app-brand__name">Copyright Manager</div>
			<div class="app-brand__tag">CodeIgniter</div>
		</div>
	</div>
	<nav class="app-nav">
		<?php foreach ($groups as $group_name => $items): ?>
			<div class="app-nav__group">
				<div class="app-nav__group-label"><?php echo html_escape($group_name); ?></div>
				<?php foreach ($items as $item): ?>
					<a class="app-nav__link<?php echo ($item[0] === $active) ? ' is-active' : ''; ?>"
					   href="<?php echo site_url($item[2]); ?>"><?php echo html_escape($item[1]); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</nav>
	<div class="app-sidebar__footer">
		<div class="app-sidebar__user">
			<span class="app-user__avatar"><?php echo html_escape($initial); ?></span>
			<div>
				<span class="app-sidebar__user-name"><?php echo html_escape($cu ? $cu['display_name'] : ''); ?></span>
				<span class="app-sidebar__user-role"><?php echo html_escape($cu ? $cu['email'] : ''); ?></span>
			</div>
		</div>
		<a class="btn btn--ghost btn--sm" href="<?php echo site_url('auth/logout'); ?>">Logout</a>
	</div>
</aside>
<div class="app-main">
	<header class="app-topbar">
		<div class="app-topbar__crumb">
			Copyright Management <span class="muted">/</span>
			<span class="app-topbar__crumb-current"><?php echo html_escape($current_label); ?></span>
		</div>
		<div class="app-topbar__user">
			<span class="app-user__avatar"><?php echo html_escape($initial); ?></span>
			<span><?php echo html_escape($cu ? $cu['display_name'] : ''); ?></span>
		</div>
	</header>
	<main class="app-content">
<?php if ($f = $this->session->flashdata('ok')): ?>
		<div class="alert alert--ok"><?php echo html_escape($f); ?></div>
<?php endif; ?>
<?php if ($f = $this->session->flashdata('error')): ?>
		<div class="alert alert--error"><?php echo html_escape($f); ?></div>
<?php endif; ?>
