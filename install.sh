#!/usr/bin/env bash
# Install all dependencies for copyright_management.
#
# This project is NOT an npm-workspaces monorepo: the ROOT package.json lists
# every dependency and is installed ONCE at the root. Node's upward module
# resolution lets server/ and client/ find packages in the root node_modules.
# Do NOT run npm install inside server/ or client/.
#
# Target runtime is Node.js 12.22.3. Newer Node also works for installing; only
# the Webpack 4 *build* needs the OpenSSL legacy flag on Node 17+ (see below).
set -euo pipefail

# Move to the repo root (the directory this script lives in).
cd "$(dirname "$0")"
ROOT="$(pwd)"

if ! command -v npm >/dev/null 2>&1; then
  echo "ERROR: npm not found. Install Node.js 12+ and add it to PATH." >&2
  exit 1
fi

echo "Node:  $(node --version)"
echo "npm:   $(npm --version)"
echo "Root:  $ROOT"
echo

# package.json changed (mongoose -> native mongodb driver, react 18 -> 16.12,
# express 4.21 -> 4.15.4), so the lockfile is stale. Install reconciles it.
# Remove the old tree first to avoid leftover packages (e.g. mongoose).
if [ -d node_modules ]; then
  echo "Removing existing node_modules ..."
  rm -rf node_modules
fi

echo "Running npm install at the root ..."
npm install --no-audit --no-fund

echo
echo "Done. Installed at: $ROOT/node_modules"
echo "Next:  cp server/.env.example server/.env   # if you don't have one"
echo "       npm run seed                          # needs MongoDB running"
echo "       npm run dev                           # API :5000 + client :5173"
echo
echo "If you build the client on Node 17+ (Webpack 4 + OpenSSL):"
echo "       export NODE_OPTIONS=--openssl-legacy-provider && npm run build"
