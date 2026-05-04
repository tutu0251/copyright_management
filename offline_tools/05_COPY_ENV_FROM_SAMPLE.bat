@echo off
setlocal EnableExtensions
cd /d "%~dp0.."
set "ROOT=%CD%"

if exist "%ROOT%\.env" (
  echo .env already exists — not overwriting:
  echo   %ROOT%\.env
  exit /b 0
)

if not exist "%ROOT%\env" (
  echo ERROR: Sample env file not found: %ROOT%\env
  exit /b 1
)

copy /y "%ROOT%\env" "%ROOT%\.env" >nul
if errorlevel 1 (
  echo ERROR: Could not copy env to .env
  exit /b 1
)

echo Created %ROOT%\.env from env — edit it for baseURL, database, etc.
echo.
endlocal
exit /b 0
