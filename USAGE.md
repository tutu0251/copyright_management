# Usage — Copyright Management (MERN)

This application runs as a **Node.js 12** stack with a **React 16** UI (built with
Webpack 4) and a **MongoDB ≤ 4.2** database accessed through the native `mongodb`
driver. See **[README.md](README.md)** for setup and the **legacy toolchain pins**.

## Run

```bash
npm install        # at the repo root (no workspaces — root holds all deps)
npm run seed       # first time only
npm run dev        # API :5000 + Webpack dev server :5173
```

For a production-style run: `npm run build` then `npm start` (Express serves
`client/dist` and `/api` on :5000).

## Default account (after `npm run seed`)

- Email: `admin@example.com`
- Password: `Admin123!`

**Change this password in any shared environment**, and set a strong `JWT_SECRET`
in `server/.env` before deploying.

## URLs

| Mode | UI | API |
|------|----|-----|
| `npm run dev` | http://localhost:5173 | http://localhost:5000/api |
| `npm start` (built) | http://localhost:5000 | http://localhost:5000/api |

In dev, the Webpack dev server proxies `/api` to the Express server on :5000.

## Browser support

Tested against **Firefox 52 ESR** (Windows XP). The bundle is transpiled and
polyfilled (Babel 7 + core-js 3) for that target — do not assume modern browser
features.

## Offline use

1. On a connected machine, run `offline_tools\10_PREPARE_OFFLINE_PACK_ONLINE.bat`.
2. Copy the project folder (including `offline_tools\node_modules_snapshot\`) to the target PC.
3. Follow `offline_tools\OFFLINE_README.txt`.
