import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { authApi, setToken } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      const { user: u } = await authApi.me();
      setUser(u);
    } catch {
      setUser(null);
      setToken(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (localStorage.getItem('cm_token')) refresh();
    else setLoading(false);
  }, [refresh]);

  const login = async (email, password) => {
    const { token, user: u } = await authApi.login({ email, password });
    setToken(token);
    setUser(u);
    return u;
  };

  const register = async (name, email, password) => {
    return authApi.register({ name, email, password });
  };

  const logout = async () => {
    try {
      await authApi.logout();
    } catch {
      /* ignore */
    }
    setToken(null);
    setUser(null);
  };

  const can = (perm) => user?.permissions?.includes(perm) ?? false;

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout, refresh, can }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
