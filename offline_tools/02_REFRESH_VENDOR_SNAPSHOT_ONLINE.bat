@echo off
setlocal EnableExtensions
cd /d "%~dp0.."
set "ROOT=%CD%"

if not exist "%ROOT%\vendor\autoload.php" (
  echo ERROR: "%ROOT%\vendor" is missing or incomplete.
  echo Run 03_COMPOSER_INSTALL_ONLINE.bat first while you have internet.
  exit /b 1
)

echo.
echo Mirroring current vendor\ into offline_tools\vendor_snapshot\ (robocopy only, no network^) ...
echo.

if not exist "%ROOT%\offline_tools\vendor_snapshot" mkdir "%ROOT%\offline_tools\vendor_snapshot"

robocopy "%ROOT%\vendor" "%ROOT%\offline_tools\vendor_snapshot" /MIR /NFL /NDL /NJH /NJS /nc /ns /np
set "RC=%ERRORLEVEL%"
if %RC% GEQ 8 (
  echo ERROR: robocopy failed with code %RC%.
  exit /b 1
)

if not exist "%ROOT%\offline_tools\vendor_snapshot\autoload.php" (
  echo ERROR: vendor_snapshot\autoload.php was not created.
  exit /b 1
)

echo.
echo Done. offline_tools\vendor_snapshot is ready for offline installs.
echo.
endlocal
exit /b 0
