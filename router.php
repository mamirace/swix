<?php
/**
 * 🐘 PHP Built-in Server Router
 * Bu dosya PHP built-in server için özel router scriptidir
 * Statik dosyaları doğru şekilde serve eder
 */

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gelen isteği analiz et
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

error_log("🐘 Router DEBUG - URI: " . $uri);
error_log("🐘 Router DEBUG - File: " . $file);

// Eğer istenen bir dosya ise ve var ise, direkt serve et
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    // Statik dosya mimetypes
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'json' => 'application/json',
        'html' => 'text/html',
        'txt' => 'text/plain'
    ];
    
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = $mimes[$ext] ?? 'application/octet-stream';
    
    header("Content-Type: $mime");
    header("Cache-Control: public, max-age=3600");
    header("Content-Length: " . filesize($file));
    
    error_log("🐘 Serving static file: " . $file . " (MIME: " . $mime . ")");
    readfile($file);
    return true; // PHP built-in server'a dosyanın serve edildiğini söyle
}

// Statik dosya bulunamadı, ana uygulamaya yönlendir
error_log("🐘 Routing to main app: " . $uri);

try {
    require_once 'başlat.php';
    return false;
} catch (Exception $e) {
    error_log("🐘 ROUTER ERROR: " . $e->getMessage());
    http_response_code(500);
    echo "Server Error: " . $e->getMessage();
    return false;
}