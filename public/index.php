<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

$helperFiles = [
    __DIR__ . '/../app/Helpers/jdate.php',
    __DIR__ . '/../app/Helpers/hijri.php',
    __DIR__ . '/../app/Helpers/date.php',
    __DIR__ . '/../app/Helpers/settings.php',
    __DIR__ . '/../app/Helpers/locale.php',
    __DIR__ . '/../app/Helpers/seo.php',
    __DIR__ . '/../app/Helpers/format_price.php',
    __DIR__ . '/../app/Helpers/card.php',
];

foreach ($helperFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
