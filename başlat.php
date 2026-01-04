<?php
/**
 * 🐘 Swix Dashboard - PHP Ana Dosya
 * XAMPP Apache veya PHP Built-in Server için uyumlu
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proje kök dizinini tanımla
if (!defined('PROJE_KOK')) {
    define('PROJE_KOK', __DIR__);
}

// PHP Built-in server check - eğer router.php aracılığıyla geliyorsa statik dosya kontrolü yapma
$is_builtin_server = strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Development Server') !== false;
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($request_uri, PHP_URL_PATH);

// PHP Built-in server için statik dosya kontrolü (router.php'de handle edilmiş olmalı)
// XAMPP/Apache için bu kod çalışmayacak çünkü .htaccess routing yapacak

echo "🐘 Başlat.php - URI: " . $uri . "\n";

// Ana uygulamayı başlat
require_once __DIR__ . '/sunucu/sunucu.php';
