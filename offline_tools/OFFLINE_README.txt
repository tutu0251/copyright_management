Offline package for copyright_management
(CodeIgniter 2.1.3 / PHP 5.6.2 / MySQL 5.x -- classic LAMP, server-rendered)

This product was refactored BACK from a MERN port to classic LAMP. There is no
Node, npm, MongoDB, Webpack, or Composer in this application anymore. The old
Node/MERN snapshot and the unrelated CodeIgniter 4 + Composer (dompdf/phpunit/
faker) snapshot that used to live here have been removed.

WHAT IS INCLUDED
----------------
- ci-2.1.3-system\          The official CodeIgniter 2.1.3 framework.
    system\                 The framework core. This is the ONLY package the app
                            needs that is not committed to the repo (system/ is
                            gitignored -- "you provide" it).
    license.txt             CodeIgniter (Open Software License 3.0).

WHY THIS IS THE ONLY BUNDLED PACKAGE
------------------------------------
- CodeIgniter 2.1.3 bundles everything the app uses (Session, Upload, Form
  validation, URL/Form helpers, Query Builder). There are NO Composer packages.
- PHP 5.6.2 and MySQL 5.x are system runtimes you install separately on the
  target machine (see PREREQUISITES). They are not bundled here.

PREREQUISITES (OFFLINE PC)
--------------------------
- PHP 5.6.2 with the mysqli extension (gd / fileinfo recommended for uploads),
  on PATH, OR served by Apache with mod_rewrite.
- MySQL 5.x reachable on 127.0.0.1:3306.

OFFLINE SETUP
-------------
1) Provide the framework: copy ci-2.1.3-system\system  to the project root, next
   to index.php (so you end up with  <project>\system\ ).
       e.g.  xcopy /E /I offline_tools\ci-2.1.3-system\system  ..\system

2) Create the database and load schema + seed:
       mysql -u root -e "CREATE DATABASE copyright_management CHARACTER SET utf8 COLLATE utf8_general_ci;"
       mysql -u root copyright_management < ..\sql\schema.sql
       mysql -u root copyright_management < ..\sql\seed.sql

3) Configure (in ..\application\config\):
       database.php  -> hostname / username / password / database (mysqli)
                        (or create database.local.php -- gitignored)
       config.php    -> set a long random $config['encryption_key']

4) Serve it:
       php -S localhost:8080        (dev: http://localhost:8080)
   ...or point an Apache vhost at the project root (uses the bundled .htaccess;
   ensure mod_rewrite is enabled for clean URLs).

DEFAULT LOGIN (after seed)
--------------------------
Email:    admin@example.com
Password: Admin123!
Change this password in any shared environment.

GIT
---
.gitignore excludes offline_tools\* to keep the repo small. Keep this folder on
USB/backup, or commit ci-2.1.3-system\ if you want the framework versioned.
