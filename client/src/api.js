const API_BASE = process.env.VITE_API_URL || '/api';

function getToken() {
  return localStorage.getItem('cm_token');
}

export async function api(path, options = {}) {
  const headers = { 'Content-Type': 'application/json', ...options.headers };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  const res = await fetch(`${API_BASE}${path}`, { ...options, headers });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.error || res.statusText || 'Request failed');
    err.status = res.status;
    err.data = data;
    throw err;
  }
  return data;
}

export const authApi = {
  login: (body) => api('/auth/login', { method: 'POST', body: JSON.stringify(body) }),
  register: (body) => api('/auth/register', { method: 'POST', body: JSON.stringify(body) }),
  me: () => api('/auth/me'),
  logout: () => api('/auth/logout', { method: 'POST' }),
};

export function setToken(token) {
  if (token) localStorage.setItem('cm_token', token);
  else localStorage.removeItem('cm_token');
}

// --- Asset (binary file) helpers -----------------------------------------

// Direct URL to stream an asset's bytes. The JWT travels as a query param so the
// URL can be used straight in <img>/<video>/<iframe> or a download link, where a
// request header isn't available. Pass { download: true } to force a download.
export function assetUrl(id, opts = {}) {
  const params = [];
  const token = getToken();
  if (token) params.push('token=' + encodeURIComponent(token));
  if (opts.download) params.push('download=1');
  const qs = params.length ? '?' + params.join('&') : '';
  return `${API_BASE}/assets/${id}${qs}`;
}

// Upload one file to a work. Sent as a raw octet-stream body (so the server's
// JSON body-parser ignores it); the real name/type ride along in headers.
export async function uploadAsset(workId, file) {
  const headers = {
    'Content-Type': 'application/octet-stream',
    'X-File-Name': encodeURIComponent(file.name),
    'X-Content-Type': file.type || 'application/octet-stream',
  };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  const res = await fetch(`${API_BASE}/works/${workId}/assets`, {
    method: 'POST',
    headers,
    body: file,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.error || res.statusText || 'Upload failed');
    err.status = res.status;
    throw err;
  }
  return data;
}
