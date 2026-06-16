import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function ProtectedRoute({ permission, children }) {
  const { user, loading, can } = useAuth();

  if (loading) {
    return (
      <div className="app-main" style={{ padding: '2rem' }}>
        <p className="text-muted">Loading…</p>
      </div>
    );
  }

  if (!user) return <Navigate to="/login" replace />;

  if (permission && !can(permission)) {
    return (
      <div className="app-main" style={{ padding: '2rem' }}>
        <div className="card">
          <h2>Access denied</h2>
          <p className="text-muted">You do not have permission to view this page.</p>
        </div>
      </div>
    );
  }

  if (children) return children;
  return <Outlet />;
}
