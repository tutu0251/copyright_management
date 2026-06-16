import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function Register() {
  const { register } = useAuth();
  const navigate = useNavigate();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  const submit = async (e) => {
    e.preventDefault();
    setError('');
    setMessage('');
    try {
      const res = await register(name, email, password);
      setMessage(res.message || 'Registration successful.');
      setTimeout(() => navigate('/login'), 1500);
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card card">
        <div className="app-brand" style={{ borderBottom: 'none', paddingBottom: '0.5rem', marginBottom: '0.5rem' }}>
          <span className="app-brand__mark">CM</span>
          <div>
            <div className="app-brand__name">Copyright Manager</div>
            <div className="app-brand__tag">MERN</div>
          </div>
        </div>
        <h1>Register</h1>
        <p className="text-muted">Create your account to get started.</p>
        {error && <div className="alert alert--danger">{error}</div>}
        {message && <div className="alert alert--success">{message}</div>}
        <form onSubmit={submit}>
          <label className="field">
            <span>Name</span>
            <input value={name} onChange={(e) => setName(e.target.value)} required />
          </label>
          <label className="field">
            <span>Email</span>
            <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </label>
          <label className="field">
            <span>Password (8+ characters)</span>
            <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} />
          </label>
          <button type="submit" className="btn btn--primary btn--block">
            Create account
          </button>
        </form>
        <p className="auth-card__footer">
          Already have an account? <Link to="/login">Sign in</Link>
        </p>
      </div>
    </div>
  );
}
