@echo off
setlocal EnableExtensions
cd /d "%~dp0.."
set "ROOT=%CD%"

if not exist "%ROOT%\offline_tools\vendor_snapshot\autoload.php" (
  echo ERROR: "%ROOT%\offline_tools\vendor_snapshot" is missing or incomplete.
  echo On a machine with the project and a working vendor folder, run:
  echo   offline_tools\02_REFRESH_VENDOR_SNAPSHOT_ONLINE.bat
  exit /b 1
)

echo.
echo Restoring dependencies: offline_tools\vendor_snapshot  -^>  vendor
echo.

if exist "%ROOT%\vendor" (
  echo Removing existing vendor folder...
  rmdir /s /q "%ROOT%\vendor"
)

robocopy "%ROOT%\offline_tools\vendor_snapshot" "%ROOT%\vendor" /E /NFL /NDL /NJH /NJS /nc /ns /np
set "RC=%ERRORLEVEL%"
if %RC% GEQ 8 (
  echo ERROR: robocopy failed with code %RC%.
  exit /b 1
)

if not exist "%ROOT%\vendor\autoload.php" (
  echo ERROR: vendor\autoload.php was not created.
  exit /b 1
)

echo.
echo Done. You can run PHP CodeIgniter commands from:
echo   %ROOT%
echo Example:  php spark serve
echo.
endlocal
exit /b 0
