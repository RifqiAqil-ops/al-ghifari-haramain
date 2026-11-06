<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * Al Ghifari Haramain Management System - cPanel Front Controller
 * Target Core Path: /home/xonkgvsz/al-ghifari-core
 */

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../al-ghifari-core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
if (file_exists(__DIR__.'/../al-ghifari-core/vendor/autoload.php')) {
    require __DIR__.'/../al-ghifari-core/vendor/autoload.php';
} else {
    die('<div style="font-family:sans-serif;padding:30px;text-align:center;"><h3>Sistem Sedang Dipersiapkan</h3><p>Folder vendor tidak ditemukan pada <code>../al-ghifari-core/vendor</code>. Pastikan paket deployment telah diunggah dan diekstrak.</p></div>');
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../al-ghifari-core/bootstrap/app.php';

$app->handleRequest(Request::capture());

