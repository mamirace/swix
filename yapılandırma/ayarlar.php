<?php
// 🔧 Swixx Dashboard - PHP Yapılandırma Ayarları  
// Python ayarlar.py dosyasının PHP karşılığı
// Bu dosya uygulamanın tüm yapılandırma ayarlarını içerir

/**
 * Yapılandırma ayarlarını PHP array olarak döndürür
 * @return array Tüm yapılandırma ayarları
 */
function ayarlar_yukle() {
    // Veritabanı şifresini direkt tanımla (production'da çevre değişkenleri kullan)
    $_ENV['DB_PASSWORD'] = '7348799Mib!';
    $_ENV['DB_HOST'] = '92.113.22.154';
    $_ENV['DB_PORT'] = '3306';
    $_ENV['DB_USER'] = 'u534683512_mami';
    $_ENV['DB_NAME'] = 'u534683512_swixx';
    $_ENV['PORT'] = '3000';
    $_ENV['HOST'] = 'localhost';
    $_ENV['PHP_ENV'] = 'development';
    // Environment variables
    $_ENV['SESSION_SECRET'] = 'swixx-dashboard-secret-2026';
    $_ENV['PHP_ENV'] = 'production'; // Production modunu zorla
    
    $sunucu_ayarlari = [
        'port' => (int)($_ENV['PORT'] ?? 3000),
        'host' => $_ENV['HOST'] ?? 'localhost',
        'ortam' => $_ENV['PHP_ENV'] ?? 'production'
    ];
    
    $uygulama_ayarlari = [
        'ad' => 'Swixx Dashboard',
        'versiyon' => '1.0.0',
        'aciklama' => 'Modern PHP Vuexy Admin Dashboard',
        'yazar' => 'mamirace',
        'base_url' => $_ENV['BASE_URL'] ?? 'http://localhost:3000'
    ];
    
    $rota_ayarlari = [
        'ana_sayfa' => '/giris',
        'login_sayfasi' => '/giris',
        'dashboard_sayfasi' => '/dashboard',
        'api_temel_rota' => '/api'
    ];
    
    $varlıklar_yolu = [
        'css' => '/varlıklar/css',
        'js' => '/varlıklar/js',
        'resimler' => '/varlıklar/img',
        'fontlar' => '/varlıklar/fonts'
    ];
    
    $guvenlik_ayarlari = [
        'session_gizli_anahtar' => $_ENV['SESSION_SECRET'] ?? 'swixx-dashboard-secret-2026',
        'cookie_max_age' => 24 * 60 * 60 * 1000,  // 24 saat
        'https_zorunlu' => ($_ENV['PHP_ENV'] ?? 'development') === 'production'
    ];
    
    $veritabani_ayarlari = [
        'host' => $_ENV['DB_HOST'] ?? '92.113.22.154',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306),
        'kullanici' => $_ENV['DB_USER'] ?? 'u534683512_mami',
        'sifre' => $_ENV['DB_PASSWORD'] ?? '',
        'veritabani_adi' => $_ENV['DB_NAME'] ?? 'u534683512_swixx',
        'charset' => 'utf8mb4',
        'baglanti_timeout' => 30
    ];
    
    // SMTP E-posta ayarları (Hostinger için)
    $smtp_ayarlari = [
        'host' => $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com',
        'port' => (int)($_ENV['SMTP_PORT'] ?? 465),
        'username' => $_ENV['SMTP_USER'] ?? 'info@swixx.bilgeguc.io',
        'password' => $_ENV['SMTP_PASS'] ?? '7348799Mib!',
        'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'ssl',
        'from_email' => $_ENV['FROM_EMAIL'] ?? 'info@swixx.bilgeguc.io',
        'from_name' => $_ENV['FROM_NAME'] ?? 'Swixx Dashboard'
    ];
    
    return [
        'sunucu_ayarlari' => $sunucu_ayarlari,
        'uygulama_ayarlari' => $uygulama_ayarlari,
        'rota_ayarlari' => $rota_ayarlari,
        'varlıklar_yolu' => $varlıklar_yolu,
        'guvenlik_ayarlari' => $guvenlik_ayarlari,
        'veritabani_ayarlari' => $veritabani_ayarlari,
        'smtp' => $smtp_ayarlari
    ];
}

// Test için
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $ayarlar = ayarlar_yukle();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($ayarlar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
