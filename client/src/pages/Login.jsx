import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function Login() {
  const { login, user } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    if (user) navigate('/dashboard', { replace: true });
  }, [user, navigate]);

  const submit = async (e) => {
    e.preventDefault();
    setError('');
    try {
      await login(email, password);
      navigate('/dashboard');
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card card">
        <h1>Sign in</h1>
        <p className="text-muted">Copyright Management</p>
        {error && <div className="alert alert--danger">{error}</div>}
        <form onSubmit={submit}>
          <label className="field">
            <span>Email</span>
            <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required autoComplete="email" />
          </label>
          <label className="field">
            <span>Password</span>
            <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required autoComplete="current-password" />
          </label>
          <button type="submit" className="btn btn--primary btn--block">
            Sign in
          </button>
        </form>
        <p className="auth-card__footer">
          No account? <Link to="/register">Register</Link>
        </p>
        <p className="text-muted" style={{ fontSize: '0.85rem', marginTop: '1rem' }}>
          Demo: admin@example.com / Admin123! (after <code>npm run seed</code>)
        </p>
      </div>
    </div>
  );
}
