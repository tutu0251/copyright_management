import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';

const NAV = [
  { id: 'dashboard', label: 'Dashboard', path: '/dashboard', perm: 'dashboard.view', group: 'Overview' },
  { id: 'assets', label: 'Assets', path: '/works', perm: 'works.view', group: 'Catalog' },
  { id: 'owners', label: 'Owners', path: '/owners', perm: 'owners.view', group: 'Catalog' },
  { id: 'licensees', label: 'Licensees', path: '/licensees', perm: 'licensees.view', group: 'Catalog' },
  { id: 'licenses', label: 'Licenses', path: '/licenses', perm: 'licenses.view', group: 'Licensing' },
  { id: 'usage_reports', label: 'Usage reports', path: '/usage-reports', perm: 'usage_reports.view', group: 'Licensing' },
  { id: 'cases', label: 'Cases', path: '/cases', perm: 'cases.view', group: 'Enforcement' },
  { id: 'activities', label: 'Activity', path: '/activities', perm: 'activities.view', group: 'Enforcement' },
  { id: 'reports', label: 'Reports', path: '/reports', perm: 'reports.view', group: 'Insights' },
  { id: 'users', label: 'Users', path: '/users', perm: 'users.manage', group: 'Administration' },
  { id: 'settings_roles', label: 'Roles & permissions', path: '/settings/roles', perm: 'settings.manage', group: 'Administration' },
];

function ThemeToggle() {
  const { theme, toggleTheme } = useTheme();
  return (
    <button
      type="button"
      className="ui-theme-toggle"
      onClick={toggleTheme}
      title={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
      aria-label="Toggle color theme"
    >
      <span className="ui-theme-toggle__icon ui-theme-toggle__icon--sun" />
      <span className="ui-theme-toggle__icon ui-theme-toggle__icon--moon" />
    </button>
  );
}

export function Layout() {
  const { user, logout, can } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const nav = NAV.filter((item) => can(item.perm));
  const initial = (user?.displayName || 'U').charAt(0).toUpperCase();
  const current = nav.find((n) => location.pathname.startsWith(n.path));
  const sectionLabel = current?.label || 'Dashboard';

  // Preserve sidebar order while grouping links under section headers.
  const groups = [];
  for (const item of nav) {
    let g = groups.find((x) => x.name === item.group);
    if (!g) groups.push((g = { name: item.group, items: [] }));
    g.items.push(item);
  }

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="app-shell">
      <aside className="app-sidebar" aria-label="Primary navigation">
        <div className="app-brand">
          <span className="app-brand__mark">CM</span>
          <div>
            <div className="app-brand__name">Copyright Manager</div>
            <div className="app-brand__tag">MERN</div>
          </div>
        </div>
        <nav className="app-nav">
          {groups.map((group) => (
            <div className="app-nav__group" key={group.name}>
              <div className="app-nav__group-label">{group.name}</div>
              {group.items.map((item) => (
                <NavLink
                  key={item.id}
                  to={item.path}
                  className={({ isActive }) => `app-nav__link${isActive ? ' is-active' : ''}`}
                >
                  {item.label}
                </NavLink>
              ))}
            </div>
          ))}
        </nav>
        <div className="app-sidebar__footer">
          <div className="app-sidebar__user">
            <span className="app-user__avatar app-user__avatar--sm" aria-hidden="true">
              {initial}
            </span>
            <div className="app-sidebar__user-meta">
              <span className="app-sidebar__user-name">{user?.displayName}</span>
              <span className="app-sidebar__user-role">{user?.primaryRole}</span>
            </div>
          </div>
          <button type="button" className="btn btn--ghost btn--sm app-sidebar__logout" onClick={handleLogout}>
            Logout
          </button>
        </div>
      </aside>
      <div className="app-main">
        <header className="app-topbar">
          <div className="app-topbar__crumb">
            <span className="app-topbar__crumb-root">Copyright Management</span>
            <span className="app-topbar__crumb-sep">/</span>
            <span className="app-topbar__crumb-current">{sectionLabel}</span>
          </div>
          <label className="app-topbar__search">
            <span className="app-topbar__search-icon" aria-hidden="true">⌕</span>
            <input
              className="app-topbar__search-input"
              type="search"
              placeholder="Search the workspace…"
              aria-label="Global search"
            />
            <kbd className="app-topbar__kbd">/</kbd>
          </label>
          <div className="app-topbar__actions">
            <ThemeToggle />
            <button type="button" className="ui-icon-btn ui-icon-btn--ghost" aria-label="Notifications">
              <span className="ui-bell" />
              <span className="ui-dot" />
            </button>
            <div className="app-topbar__user">
              <span className="app-user__avatar" aria-hidden="true">{initial}</span>
              <span className="app-topbar__user-name">{user?.displayName}</span>
            </div>
          </div>
        </header>
        <main className="app-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
