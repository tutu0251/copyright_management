@echo off
setlocal EnableExtensions
cd /d "%~dp0.."
set "ROOT=%CD%"

if not exist "%ROOT%\offline_tools\composer.phar" (
  echo ERROR: offline_tools\composer.phar not found. Run 04_DOWNLOAD_COMPOSER_PHAR.bat
  exit /b 1
)

where php >nul 2>nul
if errorlevel 1 (
  echo ERROR: php.exe was not found on PATH. Install PHP 8.2+ or add it to PATH.
  exit /b 1
)

echo.
echo Running Composer install from composer.lock (internet required^)...
echo Project: %ROOT%
echo.

php "%ROOT%\offline_tools\composer.phar" install --no-interaction --prefer-dist
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" (
  echo.
  echo Composer exited with code %RC%.
  exit /b %RC%
)

echo.
echo Done. Optionally refresh the offline mirror with:
echo   offline_tools\02_REFRESH_VENDOR_SNAPSHOT_ONLINE.bat
echo.
endlocal
exit /b 0
