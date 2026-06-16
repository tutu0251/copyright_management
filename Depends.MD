# Dependencies — Copyright Management

This document describes the **libraries and packages** used in this project: what
they are, where they are declared, and how they fit together.

The application is a **MERN** stack (MongoDB, Express, React, Node.js), **migrated
from the original PHP/CodeIgniter product** — there is no PHP / Composer in this
tree. The toolchain is **deliberately pinned to a legacy target** (Node 12 /
Firefox 52 ESR / Windows XP); see §0 below before changing any version.

For setup commands, see [README.md](README.md) and [USAGE.md](USAGE.md).

---

## 0. Legacy pins — read before upgrading anything

This project intentionally targets **Node.js 12.22.3** and **Firefox 52 ESR** so it
runs on old Windows XP machines. The following are constraints, not accidents — do
**not** "modernize" them:

- **Server is CommonJS.** No ESM, no top-level `await`, no `?.` / `??` — Node 12
  cannot parse them.
- **No Mongoose.** The data layer is the native **`mongodb` 3.5.4** driver wrapped
  by a hand-written mini-ODM at `server/src/lib/model.js` (`createModel`) that
  reproduces the model API the routes use (`find` / `populate` / `lean` / `create`
  / `findOneAndUpdate`, timestamps, `ObjectId` casting). Models are thin schema
  defs. Driver 3.5.x quirks the wrapper relies on: `findOneAndUpdate` uses
  `returnOriginal` (not `returnDocument`), and `insertOne` returns `result.ops`.
- **`mongodb` 3.5.4 only speaks to MongoDB server ≤ 4.2** (wire version). A 7.x/8.x
  server will not connect.
- **Express 4.15.4** predates `express.json()` (added in 4.16) — the server uses
  **`body-parser`**.
- **Frontend builds with Webpack 4 + Babel 7** (not Vite). Vite needs Node 18 and
  ships native ESM FF52 cannot load.
- **React 16.12.0** with Babel preset-react **`runtime: 'classic'`** — `react/jsx-runtime`
  did not exist until 16.14, so every `.jsx` file imports `React` explicitly.
- **No npm workspaces** (npm 6 lacks them) — the root `package.json` lists all deps.
- Building on **Node 17+** requires `NODE_OPTIONS=--openssl-legacy-provider` for
  Webpack 4 (not on Node 12 itself).

---

## 1. Stack at a glance

| Layer | Technology | Location / notes |
|-------|------------|------------------|
| **M** — MongoDB | Database **≤ 4.2** | External install or memory-server; URI in `server/.env` |
| **E** — Express | **4.15.4** HTTP API | `server/` — port **5000** (default), CommonJS |
| **R** — React | **16.12.0** SPA | `client/` — Webpack dev server port **5173** |
| **N** — Node.js | **12.22.3** runtime | Root `package.json` `engines.node >= 12`; see `.nvmrc` |

```text
Browser FF52 (React 16 + Chart.js)
    │  fetch /api  (+ Bearer JWT)
    ▼
Express 4.15 (server, CommonJS)
    │  native mongodb 3.5.4 driver  (via server/src/lib/model.js)
    ▼
MongoDB ≤ 4.2  (GridFS for binary assets — server/src/lib/gridfs.js)
```

---

## 2. How dependencies are organized

There are **no workspaces**. The **root `package.json` declares every dependency**
(server and client alike); the `server/` and `client/` folders resolve modules
upward into the root `node_modules`.

| Path | Role |
|------|------|
| `package.json` (root) | **All** prod + dev deps; dev orchestration (`concurrently`) |
| `package-lock.json` | Locked versions |
| `server/` | Express API source (no own `node_modules`) |
| `client/` | React source + Webpack/Babel config (no own `node_modules`) |

**Install (from repo root only):**

```bash
npm install
```

**Common scripts (root):**

| Command | Description |
|---------|-------------|
| `npm run dev` | `concurrently` runs `dev:server` (nodemon) + `dev:client` (webpack-dev-server) |
| `npm run build` | Webpack production build → `client/dist` |
| `npm start` | Express serves `client/dist` and `/api` |
| `npm run seed` | Seed roles, permissions, admin, sample data |

---

## 3. Production dependencies (root `package.json`)

| Package | Version | Role in this project | Typical usage |
|---------|---------|----------------------|---------------|
| **express** | `4.15.4` | HTTP server, `/api` routes, static `client/dist` in prod | `server/src/index.js`, `server/src/routes/*` |
| **mongodb** | `3.5.4` | **Native** MongoDB driver (no ODM) | `server/src/config/db.js`, `server/src/lib/model.js`, `gridfs.js` |
| **body-parser** | `^1.19.0` | JSON body parsing (Express 4.15 lacks `express.json()`) | `server/src/index.js` |
| **jsonwebtoken** | `^9.0.2` | Issue / verify JWT sessions | `server/src/middleware/auth.js` |
| **bcryptjs** | `^2.4.3` | Password hashing / comparison | `server/src/models/User.js`, auth routes |
| **dotenv** | `^16.4.7` | Load `server/.env` | `require('dotenv/config')` in entry / seed |
| **cors** | `^2.8.5` | Allow the dev UI origin with credentials | `server/src/index.js` (`CLIENT_URL`) |
| **morgan** | `^1.10.0` | HTTP request logging (`dev` format) | `server/src/index.js` |
| **react** / **react-dom** | `16.12.0` | UI components + DOM rendering | `client/src/**/*.jsx`, `client/src/main.jsx` |
| **react-router-dom** | `^6.28.2` | Client-side routing, protected routes | `client/src/App.jsx` |
| **chart.js** | `^4.4.7` | Dashboard charts from API `chartPayload` | `client/src/pages/Dashboard.jsx` |
| **core-js** | `^3.34.0` | Polyfills for FF52 (Babel `useBuiltIns: 'usage'`) | injected at build time |

### Node.js built-ins (not in `package.json`)

- **`path`** — static paths for `client/dist` in `server/src/index.js`
- Node's GridFS support via the `mongodb` driver — `server/src/lib/gridfs.js`

### Server environment variables

Configure in `server/.env` (from `server/.env.example`):

- `PORT` — API port (default 5000)
- `MONGODB_URI` — MongoDB connection (default `mongodb://127.0.0.1:27017/copyright_management`)
- `JWT_SECRET` — token signing key (**set a long random value in production**)
- `CLIENT_URL` — CORS origin (default `http://localhost:5173`)

---

## 4. Dev / build dependencies (root `package.json`)

| Package | Version | Role |
|---------|---------|------|
| **webpack** | `^4.47.0` | Bundler for the client | `client/webpack.config.js` |
| **webpack-cli** | `^3.3.12` | `webpack --mode production` build |
| **webpack-dev-server** | `^3.11.3` | Dev server on **:5173**, proxies `/api` → `:5000` |
| **@babel/core** | `^7.23.9` | Transpilation core |
| **@babel/preset-env** | `^7.23.9` | Target `firefox 52`, `useBuiltIns: 'usage'`, core-js 3 |
| **@babel/preset-react** | `^7.23.3` | JSX — **`runtime: 'classic'`** (React 16.12) |
| **babel-loader** | `^8.3.0` | Webpack ↔ Babel bridge |
| **html-webpack-plugin** | `^4.5.2` | Generates `dist/index.html` |
| **copy-webpack-plugin** | `^6.4.1` | Copies `client/public` → `dist` |
| **clean-webpack-plugin** | `^3.0.0` | Cleans `dist` before each build |
| **css-loader / style-loader** | `^5.2.7 / ^2.0.0` | CSS imports |
| **file-loader** | `^6.2.0` | Images / fonts |
| **concurrently** | `^6.5.1` | Runs server + client together (`npm run dev`); v9 needs Node 18 |
| **nodemon** | `^2.0.22` | Server auto-restart in dev |

### Client patterns (no extra npm packages)

| Concern | Approach |
|---------|----------|
| HTTP API | Native **`fetch`** in `client/src/api.js` |
| Auth token | `localStorage` key **`cm_token`** |
| API base | `process.env.VITE_API_URL` (Webpack `DefinePlugin`), defaults to `/api` |
| UI kit | No MUI / Bootstrap / Tailwind — project CSS |
| Dev API proxy | webpack-dev-server proxies `/api` → `http://localhost:5000` |

> `babel.config.js` must set **`sourceType: 'unambiguous'`** — otherwise
> `useBuiltIns: 'usage'` injects ESM `import` into CommonJS `node_modules` and the
> browser bundle throws `Cannot assign to read only property 'exports'`.

---

## 5. External runtime (not npm)

| Component | Requirement | Notes |
|-----------|-------------|--------|
| **MongoDB** | **≤ 4.2** | `mongodb` 3.5.4 driver caps at wire protocol 4.2. For a throwaway local DB, `mongodb-memory-server` pinned to `MONGOMS_VERSION=4.2.x` on :27017 works. |
| **Node.js** | **12.x** (`>= 12`) | 12.22.3 verified; newer Node needs `NODE_OPTIONS=--openssl-legacy-provider` for the build. |

---

## 6. Request and auth flow (how packages connect)

1. User signs in → server validates password with **bcryptjs**, returns a **JWT**
   (**jsonwebtoken**).
2. Client stores the token (`localStorage` `cm_token`) and sends
   `Authorization: Bearer <token>` on each **fetch** (`api.js`). Binary asset
   requests may also pass `?token=` so `<img>`/`<video>` can load directly.
3. **express** routes run **requireAuth** → the native **mongodb** driver (via
   `model.js`) loads the user, roles, and permissions; **requirePermission** gates
   each route by slug.
4. The dashboard requests stats and **chart.js** renders the API `chartPayload`
   (`Dashboard.jsx` + `server/src/routes/dashboard.js`).

---

## 7. Offline installation (tooling, not app libraries)

`offline_tools/` contains numbered `.bat` scripts and a `node_modules_snapshot/`
mirror of the root `node_modules` for air-gapped installs. It vendors the **same**
npm packages listed above — it adds no separate application libraries. Refresh the
snapshot after any dependency change.

See `offline_tools/OFFLINE_README.txt` and [README.md](README.md) § Offline installation.

---

## 8. Quick reference — all declared npm packages (root)

| Package | prod / dev |
|---------|------------|
| express | prod |
| mongodb | prod |
| body-parser | prod |
| jsonwebtoken | prod |
| bcryptjs | prod |
| dotenv | prod |
| cors | prod |
| morgan | prod |
| react | prod |
| react-dom | prod |
| react-router-dom | prod |
| chart.js | prod |
| core-js | prod |
| webpack | dev |
| webpack-cli | dev |
| webpack-dev-server | dev |
| @babel/core | dev |
| @babel/preset-env | dev |
| @babel/preset-react | dev |
| babel-loader | dev |
| html-webpack-plugin | dev |
| copy-webpack-plugin | dev |
| clean-webpack-plugin | dev |
| css-loader | dev |
| style-loader | dev |
| file-loader | dev |
| concurrently | dev |
| nodemon | dev |

---

## 9. Updating this document

When adding or removing dependencies:

1. **Check §0 first** — most "upgrades" break the Node 12 / FF52 / MongoDB 4.2 target.
2. Update the root `package.json` and run `npm install` at the repo root.
3. Adjust the tables in §3, §4, and §8, and note new usage (file paths / purpose).
4. Refresh the offline snapshot (`offline_tools/`).

Exact installed versions are in `package-lock.json`.
