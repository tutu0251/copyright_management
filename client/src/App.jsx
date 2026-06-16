import React from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import { Layout } from './components/Layout';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Login } from './pages/Login';
import { Register } from './pages/Register';
import { Dashboard } from './pages/Dashboard';
import { Works } from './pages/Works';
import { Owners } from './pages/Owners';
import { Licensees } from './pages/Licensees';
import { Licenses } from './pages/Licenses';
import { UsageReports } from './pages/UsageReports';
import { Cases } from './pages/Cases';
import { Activities } from './pages/Activities';
import { Reports } from './pages/Reports';
import { Users } from './pages/Users';
import { Roles } from './pages/Roles';

export default function App() {
  return (
    <ThemeProvider>
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route element={<ProtectedRoute />}>
            <Route element={<Layout />}>
              <Route index element={<Navigate to="/dashboard" replace />} />
              <Route path="dashboard" element={<ProtectedRoute permission="dashboard.view"><Dashboard /></ProtectedRoute>} />
              <Route path="works" element={<ProtectedRoute permission="works.view"><Works /></ProtectedRoute>} />
              <Route path="owners" element={<ProtectedRoute permission="owners.view"><Owners /></ProtectedRoute>} />
              <Route path="licensees" element={<ProtectedRoute permission="licensees.view"><Licensees /></ProtectedRoute>} />
              <Route path="licenses" element={<ProtectedRoute permission="licenses.view"><Licenses /></ProtectedRoute>} />
              <Route path="usage-reports" element={<ProtectedRoute permission="usage_reports.view"><UsageReports /></ProtectedRoute>} />
              <Route path="cases" element={<ProtectedRoute permission="cases.view"><Cases /></ProtectedRoute>} />
              <Route path="activities" element={<ProtectedRoute permission="activities.view"><Activities /></ProtectedRoute>} />
              <Route path="reports" element={<ProtectedRoute permission="reports.view"><Reports /></ProtectedRoute>} />
              <Route path="users" element={<ProtectedRoute permission="users.manage"><Users /></ProtectedRoute>} />
              <Route path="settings/roles" element={<ProtectedRoute permission="settings.manage"><Roles /></ProtectedRoute>} />
            </Route>
          </Route>
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
    </ThemeProvider>
  );
}
