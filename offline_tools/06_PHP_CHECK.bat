@echo off
setlocal EnableExtensions
echo PHP executable:
where php 2>nul
if errorlevel 1 (
  echo ERROR: php.exe not on PATH.
  exit /b 1
)
echo.
php -v
echo.
echo Checking extensions (intl, mbstring, openssl, curl are commonly needed^):
php -r "$e=get_loaded_extensions(); foreach (['intl','mbstring','openssl','curl','pdo_mysql'] as $x) echo (in_array($x,$e)?'OK ':'NO ').$x.PHP_EOL;"
echo.
endlocal
exit /b 0
