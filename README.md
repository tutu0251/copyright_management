# Copyright Management (MERN)

MongoDB, Express, React, and Node.js application for copyright / asset / license
management. Migrated from the original PHP/CodeIgniter product to a MERN stack.

> **Legacy target.** This project is deliberately pinned to an old toolchain so it
> runs on **Node.js 12** and is browsable on **Firefox 52 ESR** (the last Firefox
> for Windows XP). Do **not** "modernize" the stack — see
> [Depends.MD](Depends.MD) for the exact pins and the reasons behind them.

## Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| **M** — MongoDB | server **≤ 4.2** | Native `mongodb` **3.5.4** driver (no Mongoose); driver wire protocol caps at MongoDB 4.2 |
| **E** — Express | **4.15.4** | CommonJS; uses `body-parser` (no `express.json()` on 4.15.x) |
| **R** — React | **16.12.0** | Built with **Webpack 4 + Babel 7** (not Vite); Babel preset-react `runtime: 'classic'` |
| **N** — Node.js | **12.22.3** | See `.nvmrc`; `engines.node >= 12` |

There are **no npm workspaces** (npm 6 lacks them). The **root `package.json` lists
all dependencies**; `server/` and `client/` resolve modules upward. Install once at
the root — do **not** run `npm install` inside the subfolders.

## Prerequisites

- **Node.js 12.x** (12.22.3 recommended — `nvm use` reads `.nvmrc`)
- **MongoDB ≤ 4.2** reachable on `mongodb://127.0.0.1:27017` (or set `MONGODB_URI`)

> Building on a **newer Node (17+)** requires `set NODE_OPTIONS=--openssl-legacy-provider`
> for the Webpack 4 build (see `NODE_OPTIONS` file / `run.bat`). Not needed on Node 12 itself.

## Quick start

```bash
npm install                          # install ALL deps at the repo root
copy server\.env.example server\.env  # Windows; edit MONGODB_URI / JWT_SECRET
npm run seed                         # roles, permissions, admin user, sample data
npm run dev                          # API (:5000) + Webpack dev server (:5173)
```

Open **http://localhost:5173** and sign in with `admin@example.com` / `Admin123!`
after seeding. **Change this password in any shared environment.**

### Production-style run

```bash
npm run build    # Webpack production bundle -> client/dist
npm start        # Express serves client/dist + /api on :5000
```

Then browse **http://localhost:5000**.

## Scripts (run from repo root)

| Command | Description |
|---------|-------------|
| `npm run dev` | API (`nodemon`, :5000) + Webpack dev server (:5173, proxies `/api` → :5000) |
| `npm run dev:server` | API only |
| `npm run dev:client` | Webpack dev server only |
| `npm run build` | Production React build → `client/dist` |
| `npm start` | Express serves `client/dist` and `/api` on :5000 |
| `npm run seed` | Seed roles, permissions, admin user, sample data |

## Configuration

Server env vars (`server/.env`, copied from `server/.env.example`):

| Variable | Default | Purpose |
|----------|---------|---------|
| `PORT` | `5000` | API port |
| `MONGODB_URI` | `mongodb://127.0.0.1:27017/copyright_management` | Mongo connection |
| `JWT_SECRET` | `dev-secret` (fallback) | **Set a long random value in production** |
| `CLIENT_URL` | `http://localhost:5173` | CORS origin for the dev UI |

Client (build-time): `VITE_API_URL` overrides the API base (defaults to `/api`).
The name is historical — Webpack's `DefinePlugin` injects it; there is no Vite here.

## Offline installation

For air-gapped / Windows XP machines, see **`offline_tools/OFFLINE_README.txt`**.

**While online (once):** run `offline_tools\10_PREPARE_OFFLINE_PACK_ONLINE.bat` —
installs deps, builds the client, and mirrors `node_modules` into
`offline_tools\node_modules_snapshot\`.

**On the offline PC:**

1. `01_INSTALL_NODE_MODULES_OFFLINE.bat`
2. `05_COPY_ENV_FROM_SAMPLE.bat`
3. Start MongoDB → `08_RUN_SEED.bat`
4. `09_START_DEV.bat`

Copy the whole project folder to USB, including `offline_tools\node_modules_snapshot\`.

## Project layout

```
client/          React 16 UI, built with Webpack 4 + Babel 7
server/          Express 4.15 API (CommonJS, native mongodb driver)
  src/lib/       model.js (mini-ODM over the native driver), gridfs.js (asset storage)
  src/models/    thin schema defs (Work, License, Licensee, Owner, Case, ...)
  src/routes/    REST routes + crudFactory; auth, dashboard, reports, assets
offline_tools/   Offline npm snapshot + numbered .bat scripts
package.json     Single root manifest — lists ALL deps (no workspaces)
```

## API

REST API under `/api` with JWT auth (`Authorization: Bearer <token>`; binary assets
also accept `?token=`). Authorization is role/permission based and enforced
server-side per route via `requireAuth` / `requirePermission`. Permission slugs
match the original product (e.g. `works.view`, `licenses.create`).
