<?php

declare(strict_types=1);

/**
 * PHP built-in server router — serves files under `public/` directly, otherwise
 * boots CodeIgniter (same behavior as `php spark serve`).
 *
 * From the project root:
 *   php -S 127.0.0.1:8765 -t public vendor/codeigniter4/framework/system/rewrite.php
 *
 * Or from the `public` directory (simpler):
 *   cd public
 *   php -S 127.0.0.1:8765 router.php
 *
 * Do not use `index.php` as the router script; CSS/JS under public/assets will not load.
 */

require dirname(__DIR__) . '/vendor/codeigniter4/framework/system/rewrite.php';
