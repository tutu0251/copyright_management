Offline tools for copyright_management
(MERN, built to run on legacy hardware: Node.js 12 + Firefox 52 ESR / Windows XP)

TARGET COMPATIBILITY
--------------------
- Backend: Node.js 12+ (CommonJS; no ESM, no top-level await, no optional chaining).
- Frontend: bundled with Webpack 4 + Babel 7, transpiled + polyfilled (core-js 3)
  to run in Firefox 52 ESR (the last Firefox for Windows XP). No native ES modules
  are sent to the browser; everything is one classic <script> bundle.
- Database: MongoDB. mongoose 6 is used (compatible with Node 12).

WHAT IS INCLUDED
----------------
- node_modules_snapshot\   Mirror of ALL npm dependencies (server + client + build
                           tools) for a single hoisted node_modules at the project
                           root. Restore it into the project root when offline.
- mongodb-windows-x86_64-8.3.2-signed.msi   MongoDB installer (optional; install
                           separately, or point MONGODB_URI at any reachable server).

WHY ONE node_modules AT THE ROOT
--------------------------------
npm 6 (which ships with Node 12) has no "workspaces". So this project is NOT a
workspaces monorepo: the ROOT package.json lists every dependency and is installed
once at the root. Node's normal upward module resolution lets both server\ and
client\ find packages in the root node_modules. Do not run "npm install" inside
server\ or client\ -- always install at the root.

PREREQUISITES (OFFLINE PC)
----------------------------
- Node.js 12 or newer on PATH (run 06_NODE_CHECK.bat to verify).
- MongoDB running locally, OR set MONGODB_URI in server\.env to a reachable instance.

PRIMARY WORKFLOW (NO INTERNET)
------------------------------
1) Double-click: 01_INSTALL_NODE_MODULES_OFFLINE.bat
   Copies node_modules_snapshot\ to the project root as node_modules\.
2) If you have no server\.env yet: 05_COPY_ENV_FROM_SAMPLE.bat
3) Build the production UI (optional if client\dist was shipped pre-built):
   07_BUILD_CLIENT.bat   (Webpack production build into client\dist)
4) Start MongoDB, then: 08_RUN_SEED.bat  (roles, admin user, sample data)
5) Run the app:
   - Production (single server serves API + built UI on port 5000):
       npm start   then open  http://localhost:5000
   - Development (API on 5000 + Webpack dev server with live reload on 5173):
       09_START_DEV.bat   then open  http://localhost:5173

   On Firefox 52 / Windows XP, open the port-5000 production URL. The dev server
   (5173) also serves the same transpiled bundle and works in FF52.

AFTER CHANGING package.json (WHILE ONLINE)
------------------------------------------
- Run: 03_NPM_CI_ONLINE.bat        (npm ci from package-lock.json at the root)
- Rebuild + refresh everything:    10_PREPARE_OFFLINE_PACK_ONLINE.bat
  (Runs npm ci, builds client\dist, and re-mirrors node_modules_snapshot.)
  Then copy the whole project folder -- including node_modules_snapshot -- to USB.

SCRIPTS
-------
01_INSTALL_NODE_MODULES_OFFLINE.bat          Restore node_modules from snapshot
02_REFRESH_NODE_MODULES_SNAPSHOT_ONLINE.bat  Mirror node_modules -> snapshot (online)
03_NPM_CI_ONLINE.bat                         npm ci from lockfile (online)
04_DOWNLOAD_NODE_MODULES_ONLINE.bat          npm install (online, no lockfile path)
05_COPY_ENV_FROM_SAMPLE.bat                  Create server\.env from .env.example
06_NODE_CHECK.bat                            Node/npm version check
07_BUILD_CLIENT.bat                          Webpack production build -> client\dist
08_RUN_SEED.bat                              Seed database (needs MongoDB)
09_START_DEV.bat                             API + Webpack dev server
10_PREPARE_OFFLINE_PACK_ONLINE.bat           One-shot: ci + build + refresh snapshot

NOTE ON BUILDING
----------------
On Node 12 the Webpack build just works (07_BUILD_CLIENT.bat / npm run build).
If you ever build on a NEWER Node (17+), Webpack 4 needs the OpenSSL legacy flag:
   set NODE_OPTIONS=--openssl-legacy-provider
   npm run build
This is NOT needed on the Node 12 target machine.

COPYING TO USB
--------------
Copy the whole project folder, including offline_tools\node_modules_snapshot\
(and client\dist if you pre-built it). On the offline PC: run 01, then 05, then
08, then start with npm start (production) or 09 (development).

GIT
---
.gitignore excludes offline_tools\node_modules_snapshot\ to keep the repo small.
Keep the snapshot on USB/backup, or remove that gitignore line if you commit it.

DEFAULT LOGIN (after seed)
--------------------------
Email: admin@example.com
Password: Admin123!
