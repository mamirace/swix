<?php
/**
 * 🐘 Swixx Dashboard - PHP Development Server Başlatıcısı
 * Sadece PHP Built-in Server için kullanılır
 * Web hosting için index.php kullanın!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bu dosya sadece development server için
if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Development Server') === false) {
    http_response_code(404);
    echo "Bu dosya sadece PHP Development Server için kullanılır. Web hosting için index.php kullanın!";
    exit;
}

// Proje kök dizinini tanımla
if (!defined('PROJE_KOK')) {
    define('PROJE_KOK', __DIR__);
}

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($request_uri, PHP_URL_PATH);

error_log("🐘 Development Server - URI: " . $uri);

// Ana uygulamayı başlat
require_once __DIR__ . '/sunucu/sunucu.php';
