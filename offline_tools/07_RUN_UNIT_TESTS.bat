@echo off
setlocal EnableExtensions
cd /d "%~dp0.."
set "ROOT=%CD%"

if not exist "%ROOT%\vendor\autoload.php" (
  echo ERROR: vendor\ missing. Run 01_INSTALL_VENDOR_OFFLINE.bat or 03_COMPOSER_INSTALL_ONLINE.bat
  exit /b 1
)

if not exist "%ROOT%\vendor\bin\phpunit.bat" (
  echo ERROR: vendor\bin\phpunit.bat not found. Run 01 or 03 to install dependencies (including require-dev^).
  exit /b 1
)

echo Running PHPUnit (Unit testsuite, no coverage^) ...
echo.
call "%ROOT%\vendor\bin\phpunit.bat" --testsuite Unit --no-coverage
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" exit /b %RC%
echo.
endlocal
exit /b 0
