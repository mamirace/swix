<?php
// 🔐 Swix Dashboard - Session Yönetimi Helper
// Session işlemlerini kolaylaştıran yardımcı fonksiyonlar

/**
 * Session başlat (eğer başlatılmamışsa)
 */
function session_baslat() {
    if (session_status() === PHP_SESSION_NONE) {
        // Session ayarları
        ini_set('session.cookie_lifetime', 7 * 24 * 60 * 60);  // 7 gün
        ini_set('session.gc_maxlifetime', 7 * 24 * 60 * 60);
        ini_set('session.cookie_httponly', 1);  // JavaScript'ten erişilemesin
        ini_set('session.cookie_samesite', 'Lax');  // CSRF koruması
        
        session_start();
    }
}

/**
 * Session'a değer kaydet
 * @param string $key Anahtar
 * @param mixed $value Değer
 */
function session_set($key, $value) {
    session_baslat();
    $_SESSION[$key] = $value;
}

/**
 * Session'dan değer al
 * @param string $key Anahtar
 * @param mixed $default Varsayılan değer
 * @return mixed
 */
function session_get($key, $default = null) {
    session_baslat();
    return $_SESSION[$key] ?? $default;
}

/**
 * Session'dan değer sil
 * @param string $key Anahtar
 */
function session_unset_key($key) {
    session_baslat();
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Kullanıcı login olmuş mu?
 * @return bool
 */
function is_logged_in() {
    session_baslat();
    return isset($_SESSION['kullanici_id']);
}

/**
 * Kullanıcı verilerini al
 * @return array
 */
function get_current_user() {
    session_baslat();
    return [
        'kullanici_id' => $_SESSION['kullanici_id'] ?? null,
        'kullanici_adi' => $_SESSION['kullanici_adi'] ?? null,
        'isim' => $_SESSION['isim'] ?? null,
        'soyisim' => $_SESSION['soyisim'] ?? null,
        'mail' => $_SESSION['mail'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'firma' => $_SESSION['firma'] ?? null,
        'organization' => $_SESSION['organization'] ?? null
    ];
}

/**
 * Flash message ayarla (tek kullanımlık mesaj)
 * @param string $type success, error, warning, info
 * @param string $message Mesaj
 */
function flash_set($type, $message) {
    session_baslat();
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Flash messages'ları al ve temizle
 * @return array
 */
function flash_get() {
    session_baslat();
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * CSRF token oluştur
 * @return string
 */
function csrf_token_create() {
    session_baslat();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 * @param string $token Token
 * @return bool
 */
function csrf_token_verify($token) {
    session_baslat();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Session'ı tamamen yok et
 */
function session_destroy_all() {
    session_baslat();
    $_SESSION = [];
    
    // Session cookie'yi sil
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}
