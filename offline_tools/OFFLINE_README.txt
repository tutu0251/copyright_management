Offline tools for copyright_management (MERN: Node.js 18+, MongoDB)

WHAT IS INCLUDED
----------------
- node_modules_snapshot\   Mirror of npm dependencies (from package-lock.json).
                           Restore into the project root when you have no network.

PREREQUISITES (OFFLINE PC)
----------------------------
- Node.js 18 or newer on PATH (run 06_NODE_CHECK.bat to verify).
- MongoDB 6+ running locally, OR set MONGODB_URI in server\.env to a reachable instance.
  (MongoDB itself is not bundled; install separately or use a LAN/Atlas URI.)

PRIMARY WORKFLOW (NO INTERNET)
------------------------------
1) Double-click: 01_INSTALL_NODE_MODULES_OFFLINE.bat
   Copies node_modules_snapshot\ to the project root as node_modules\.
2) If you have no server\.env yet: 05_COPY_ENV_FROM_SAMPLE.bat
3) Optional: 07_BUILD_CLIENT.bat  (production React build into client\dist)
4) Start MongoDB, then: 08_RUN_SEED.bat  (roles, admin user, sample data)
5) Start the app: 09_START_DEV.bat
   UI: http://localhost:5173   API: http://localhost:5000/api

AFTER CHANGING package.json / package-lock.json (WHILE ONLINE)
---------------------------------------------------------------
- Run: 03_NPM_CI_ONLINE.bat
- Refresh the offline mirror: 02_REFRESH_NODE_MODULES_SNAPSHOT_ONLINE.bat
  (Copy the updated project—including node_modules_snapshot—to USB or backup.)

SCRIPTS
-------
01_INSTALL_NODE_MODULES_OFFLINE.bat   Restore node_modules from snapshot
02_REFRESH_NODE_MODULES_SNAPSHOT_ONLINE.bat   Mirror node_modules into snapshot (online)
03_NPM_CI_ONLINE.bat                  npm ci from lockfile (online)
04_DOWNLOAD_NODE_MODULES_ONLINE.bat   npm install if no lockfile workflow (online)
05_COPY_ENV_FROM_SAMPLE.bat           Copy server\.env.example to server\.env
06_NODE_CHECK.bat                     Node/npm version check
07_BUILD_CLIENT.bat                   vite build (needs node_modules)
08_RUN_SEED.bat                       node server seed script (needs MongoDB)
09_START_DEV.bat                      API + Vite dev servers

COPYING TO USB
--------------
Copy the whole project folder, including offline_tools\node_modules_snapshot\.
On the offline PC: run 01, then 05, then 08 and 09.

GIT
---
.gitignore excludes offline_tools\node_modules_snapshot\ to keep the repo small.
Keep the snapshot on USB, backup, or remove that gitignore line if you commit it.

DEFAULT LOGIN (after seed)
--------------------------
Email: admin@example.com
Password: Admin123!
