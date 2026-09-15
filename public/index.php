<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan apakah aplikasi sedang dalam mode pemeliharaan...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Daftarkan Composer Autoloader...
require __DIR__.'/../vendor/autoload.php';

// Jalankan Laravel Application...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
