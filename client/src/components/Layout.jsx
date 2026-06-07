import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const NAV = [
  { id: 'dashboard', label: 'Dashboard', path: '/dashboard', perm: 'dashboard.view' },
  { id: 'assets', label: 'Assets', path: '/works', perm: 'works.view' },
  { id: 'owners', label: 'Owners', path: '/owners', perm: 'owners.view' },
  { id: 'licensees', label: 'Licensees', path: '/licensees', perm: 'licensees.view' },
  { id: 'licenses', label: 'Licenses', path: '/licenses', perm: 'licenses.view' },
  { id: 'usage_reports', label: 'Usage reports', path: '/usage-reports', perm: 'usage_reports.view' },
  { id: 'cases', label: 'Cases', path: '/cases', perm: 'cases.view' },
  { id: 'activities', label: 'Activity', path: '/activities', perm: 'activities.view' },
  { id: 'reports', label: 'Reports', path: '/reports', perm: 'reports.view' },
  { id: 'users', label: 'Users', path: '/users', perm: 'users.manage' },
  { id: 'settings_roles', label: 'Roles & permissions', path: '/settings/roles', perm: 'settings.manage' },
];

export function Layout() {
  const { user, logout, can } = useAuth();
  const navigate = useNavigate();
  const nav = NAV.filter((item) => can(item.perm));
  const initial = (user?.displayName || 'U').charAt(0).toUpperCase();

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
          {nav.map((item) => (
            <NavLink
              key={item.id}
              to={item.path}
              className={({ isActive }) => `app-nav__link${isActive ? ' is-active' : ''}`}
            >
              {item.label}
            </NavLink>
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
      <div className="app-content">
        <header className="app-topbar">
          <div className="app-topbar__crumb">Copyright Management · Signed in</div>
        </header>
        <main className="app-main">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
