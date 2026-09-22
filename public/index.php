<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan apakah aplikasi sedang dalam mode pemeliharaan...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Daftarkan Composer Autoloader...
require __DIR__.'/../vendor/autoload.php';

// Pastikan direktori framework storage selalu ada otomatis (auto-heal)
$storageDirs = [
    __DIR__.'/../storage/framework/sessions',
    __DIR__.'/../storage/framework/views',
    __DIR__.'/../storage/framework/cache/data',
    __DIR__.'/../storage/logs',
];
foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Jalankan Laravel Application...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());

