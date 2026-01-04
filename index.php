<?php
/**
 * 🐘 Swix Dashboard - Ana Giriş Dosyası
 * Web hosting ve Apache sunucular için standart index.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proje kök dizinini tanımla
if (!defined('PROJE_KOK')) {
    define('PROJE_KOK', __DIR__);
}

// Web server ortamı kontrolü
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($request_uri, PHP_URL_PATH);

// Debug bilgisi (production'da kapatılmalı)
if (defined('DEBUG') && DEBUG) {
    echo "🐘 Index.php - URI: " . $uri . "\n";
}

// Ana uygulamayı başlat
require_once __DIR__ . '/sunucu/sunucu.php';