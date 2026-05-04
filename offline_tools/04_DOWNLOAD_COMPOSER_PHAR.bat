@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo Downloading latest stable composer.phar from getcomposer.org ...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "Invoke-WebRequest -Uri 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile '%CD%\composer.phar.new' -UseBasicParsing"
if errorlevel 1 (
  echo ERROR: Download failed. Check your internet connection.
  if exist "%CD%\composer.phar.new" del "%CD%\composer.phar.new"
  exit /b 1
)

if not exist "%CD%\composer.phar.new" (
  echo ERROR: Download did not create composer.phar.new
  exit /b 1
)

move /y "%CD%\composer.phar.new" "%CD%\composer.phar" >nul
echo Saved: %CD%\composer.phar
echo.
endlocal
exit /b 0
