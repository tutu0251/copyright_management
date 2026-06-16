@echo off
REM ===========================================================================
REM  run.bat - launch the Copyright Management app (MERN, Node 12 / FF52 target)
REM
REM  Usage (double-click, or from a terminal at the project root):
REM     run.bat            Build the UI if needed, then start the app on :5000
REM     run.bat seed       Seed the database (roles, admin user, sample data)
REM     run.bat dev        Dev mode: API :5000 + Webpack dev server :5173
REM     run.bat build      Build the production UI into client\dist only
REM
REM  First time on a fresh database, run:  run.bat seed   (then run.bat)
REM  Requires: Node.js 12+ on PATH, dependencies installed, MongoDB reachable.
REM ===========================================================================
setlocal EnableExtensions EnableDelayedExpansion
cd /d "%~dp0"
set "ROOT=%CD%"
set "MODE=%~1"
if "%MODE%"=="" set "MODE=start"

echo ===========================================================
echo  Copyright Management  -  mode: %MODE%
echo  Root: %ROOT%
echo ===========================================================

REM --- 1. Node / npm present ------------------------------------------------
where node >nul 2>&1
if errorlevel 1 (
  echo ERROR: Node.js not found on PATH. Install Node.js 12+ and retry.
  exit /b 1
)
where npm >nul 2>&1
if errorlevel 1 (
  echo ERROR: npm not found on PATH.
  exit /b 1
)

REM --- 2. Dependencies installed (single hoisted node_modules at root) -------
if not exist "%ROOT%\node_modules\express\package.json" (
  echo ERROR: dependencies are not installed.
  echo   Online : npm install
  echo   Offline: offline_tools\01_INSTALL_NODE_MODULES_OFFLINE.bat
  exit /b 1
)

REM --- 3. server\.env (fall back to the sample) -----------------------------
if not exist "%ROOT%\server\.env" (
  if exist "%ROOT%\server\.env.example" (
    copy /y "%ROOT%\server\.env.example" "%ROOT%\server\.env" >nul
    echo Created server\.env from .env.example
  )
)

REM --- 4. Webpack 4 needs the OpenSSL legacy provider on Node 17+ ------------
for /f "tokens=1 delims=." %%v in ('node -p "process.versions.node"') do set "NODEMAJOR=%%v"
if !NODEMAJOR! GEQ 17 (
  set "NODE_OPTIONS=--openssl-legacy-provider"
  echo Node !NODEMAJOR! detected -> NODE_OPTIONS=--openssl-legacy-provider
)

REM --- 5. MongoDB reachability probe (warn only; the 3.5.4 driver needs <=4.2)
node -e "var s=require('net').connect(27017,'127.0.0.1');s.on('connect',function(){s.end();process.exit(0)});s.on('error',function(){process.exit(1)});setTimeout(function(){process.exit(1)},1500);"
if errorlevel 1 (
  echo.
  echo WARNING: MongoDB not reachable on 127.0.0.1:27017.
  echo   Start MongoDB first, or set MONGODB_URI in server\.env.
  echo   Note: the mongodb 3.5.4 driver only connects to MongoDB server 4.2 or older.
  echo.
)

if /i "%MODE%"=="seed"  goto :seed
if /i "%MODE%"=="dev"   goto :dev
if /i "%MODE%"=="build" goto :build
goto :start

:seed
echo Seeding database...
call npm run seed
goto :end

:dev
echo Starting DEV (API http://localhost:5000  +  Webpack dev server http://localhost:5173)
echo Press Ctrl+C to stop.
call npm run dev
goto :end

:build
echo Building production UI into client\dist ...
call npm run build
if errorlevel 1 ( echo ERROR: client build failed. & exit /b 1 )
echo Build complete: client\dist
goto :end

:start
if not exist "%ROOT%\client\dist\index.html" (
  echo Production UI not built yet - building now ...
  call npm run build
  if errorlevel 1 ( echo ERROR: client build failed. & exit /b 1 )
)
echo.
echo Starting server: http://localhost:5000   (Ctrl+C to stop)
echo Login after seeding: admin@example.com / Admin123!
echo.
call npm start
goto :end

:end
endlocal
