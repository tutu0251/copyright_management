import './polyfills';
import React, { StrictMode } from 'react';
import ReactDOM from 'react-dom';
import './styles/auth.css';
import App from './App.jsx';

// React 16 renders through ReactDOM.render; createRoot is React 18 only.
ReactDOM.render(
  <StrictMode>
    <App />
  </StrictMode>,
  document.getElementById('root'),
);
