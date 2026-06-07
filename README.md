# Copyright Management (MERN)

MongoDB, Express, React, and Node.js application for copyright / asset / license management.

## Prerequisites

- **Node.js** 18+ ([nodejs.org](https://nodejs.org/) or use `.nvmrc`)
- **MongoDB** 6+ (local install or Atlas URI in `server/.env`)

## Quick start (online)

```bash
npm install
copy server\.env.example server\.env   # Windows; edit MONGODB_URI if needed
npm run seed
npm run dev
```

Open **http://localhost:5173** — sign in with `admin@example.com` / `Admin123!` after seeding.

| Command | Description |
|---------|-------------|
| `npm run dev` | API (:5000) + React dev server (:5173) |
| `npm run build` | Production React build |
| `npm start` | API serves `client/dist` + `/api` |
| `npm run seed` | Roles, permissions, admin user, sample data |

## Offline installation

See **`offline_tools/OFFLINE_README.txt`**.

**While online (once):** run `offline_tools\10_PREPARE_OFFLINE_PACK_ONLINE.bat` — installs deps, builds the client, and mirrors `node_modules` into `offline_tools\node_modules_snapshot\`.

**Offline PC:**

1. `01_INSTALL_NODE_MODULES_OFFLINE.bat`
2. `05_COPY_ENV_FROM_SAMPLE.bat`
3. Start MongoDB → `08_RUN_SEED.bat`
4. `09_START_DEV.bat`

Copy the whole project folder to USB, including `offline_tools\node_modules_snapshot\`.

## Project layout

```
client/          React + Vite UI
server/          Express API + Mongoose
offline_tools/   Offline npm snapshot scripts
package.json     npm workspaces root
```

## API

REST API under `/api` with JWT auth. Permission slugs match the original product (e.g. `works.view`, `licenses.create`).
