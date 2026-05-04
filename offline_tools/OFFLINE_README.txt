Offline tools for copyright_management (CodeIgniter 4, PHP 8.2+)

WHAT IS INCLUDED
----------------
- composer.phar          Composer CLI (no separate Composer installer needed).
- vendor_snapshot\       Full mirror of PHP dependencies (~31 MB) matching composer.lock.
                           Restore into ..\vendor when you have no network.

PRIMARY WORKFLOW (NO INTERNET)
------------------------------
1) Ensure PHP 8.2+ is on PATH (XAMPP: typically C:\xampp\php). Required extensions: intl, mbstring (see README.md).
2) Double-click: 01_INSTALL_VENDOR_OFFLINE.bat
   This copies vendor_snapshot\ to the project root as vendor\.
3) If you have no .env yet: 05_COPY_ENV_FROM_SAMPLE.bat (copies ..\env to ..\.env)
4) Start the app: from project root,  php spark serve
   Or point your web server at the "public" folder (see project README.md).

AFTER CHANGING composer.json / composer.lock (WHILE ONLINE)
-------------------------------------------------------------
- Run: 03_COMPOSER_INSTALL_ONLINE.bat
- Then refresh the offline mirror: 02_REFRESH_VENDOR_SNAPSHOT_ONLINE.bat
  (So the next offline machine gets the new dependencies.)

IF composer.phar IS MISSING OR OUTDATED
-----------------------------------------
- While online, run: 04_DOWNLOAD_COMPOSER_PHAR.bat

COMPOSER AND NETWORK
--------------------
- This project keeps a full vendor snapshot because relying only on Composer's
  package cache is fragile on some Windows + PHP setups.
- 03_COMPOSER_INSTALL_ONLINE.bat uses the internet to install or update vendor\.
- 01_INSTALL_VENDOR_OFFLINE.bat does not use the network.

OPTIONAL
--------
- 06_PHP_CHECK.bat         Shows PHP version and whether intl/mbstring are loaded.
- 07_RUN_UNIT_TESTS.bat    Runs phpunit (requires vendor\ from step 1 or 3).

COPYING THE PROJECT TO A USB DRIVE
------------------------------------
- Copy the whole project folder, including offline_tools\vendor_snapshot\.
- On the offline PC: run 01_INSTALL_VENDOR_OFFLINE.bat after unpack.

GIT
---
- .gitignore excludes offline_tools\vendor_snapshot\ to keep the repository small.
  If you want the snapshot in version control, remove that line from .gitignore
  and commit the folder, or keep the snapshot only on your USB / backup copy.
