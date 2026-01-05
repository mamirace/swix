<?php
// 🚀 Swixx Dashboard - PHP Ana Sunucusu
// Python Flask sunucu.py dosyasının PHP karşılığı
// JavaScript/Node.js'den PHP'ye çevrilmiş modern CRM dashboard

// Hata raporlamayı aç (development için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS başlıklarını ekle (API istekleri için gerekli)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// OPTIONS isteğini hemen yanıtla (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Proje kök dizini
if (!defined('PROJE_KOK')) {
    define('PROJE_KOK', dirname(__DIR__));
}

// Session ayarları - 7 gün persistent session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 7 * 24 * 60 * 60);
    ini_set('session.gc_maxlifetime', 7 * 24 * 60 * 60);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Tüm modülleri yükle
require_once PROJE_KOK . '/yapılandırma/ayarlar.php';
require_once PROJE_KOK . '/genel/yardımcılar.php';
require_once PROJE_KOK . '/genel/email_gonderici.php';
require_once PROJE_KOK . '/veritabani/__init__.php';

// Ayarları yükle
$ayarlar = ayarlar_yukle();
$PORT = $ayarlar['sunucu_ayarlari']['port'];

// Veritabanı bağlantı testi (sessiz)
list($success, $message) = vt_test();
if (!$success) {
    error_log("❌ Veritabanı hatası: $message");
}

/**
 * Session token kontrolü - başka yerden giriş yapılmış mı?
 * @return bool Token geçerli mi?
 */
function session_token_gecerli() {
    if (!isset($_SESSION['kullanici_id']) || !isset($_SESSION['session_token'])) {
        return false;
    }
    
    // Veritabanından token'ı kontrol et
    $sorgu = "SELECT session_token FROM Kullanicilar WHERE id = ?";
    $sonuc = vt_sorgu($sorgu, [$_SESSION['kullanici_id']]);
    
    if (!$sonuc || count($sonuc) === 0) {
        return false;
    }
    
    $db_token = $sonuc[0][0];
    
    // Session token ile veritabanındaki token eşleşiyor mu?
    return ($db_token === $_SESSION['session_token']);
}

/**
 * Login gerekli mi kontrol et
 * @return bool
 */
function login_gerekli() {
    if (!isset($_SESSION['kullanici_id'])) {
        return true;
    }
    
    // Token kontrolü yap
    if (!session_token_gecerli()) {
        // Token uyuşmuyor, başka yerden giriş yapılmış
        error_log(log_formati('WARN', 'Geçersiz session token - başka yerden giriş yapılmış. Kullanıcı: ' . ($_SESSION['kullanici_adi'] ?? 'bilinmeyen')));
        session_destroy();
        return true;
    }
    
    return false;
}

/**
 * Kullanıcı verisini al
 * @return array
 */
function get_user_data() {
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
 * Ana routing fonksiyonu
 */
function route() {
    global $ayarlar;
    
    // REQUEST_URI'yi al ve temizle
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uri = parse_url($request_uri, PHP_URL_PATH);
    
    // GET parametrelerini al
    $query_params = $_GET;
    
    // POST mu GET mi?
    $method = $_SERVER['REQUEST_METHOD'];
    
    // --- ROUTE HANDLING ---
    
    switch ($uri) {
        case '/':
            // Ana sayfa - eğer login ise dashboard, değilse giriş
            if (isset($_SESSION['kullanici_id'])) {
                yonlendir('/dashboard');
            } else {
                readfile(PROJE_KOK . '/sayfalar/giris.html');
            }
            break;
            
        case '/giris':
        case '/login':
            readfile(PROJE_KOK . '/sayfalar/giris.html');
            break;
            
        case '/dashboard':
            if ($method === 'POST') {
                // Login işlemi
                $email_username = $_POST['email-username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                echo log_formati('INFO', "Giriş denemesi: $email_username") . "\n";
                
                $kullanici = login_kontrol($email_username, $password);
                
                if ($kullanici) {
                    echo log_formati('SUCCESS', "Başarılı giriş: {$kullanici[1]} ({$kullanici[5]})") . "\n";
                    
                    // Unique session token oluştur
                    $session_token = bin2hex(random_bytes(32));
                    
                    // Token'ı veritabanına kaydet
                    $token_guncelle = "UPDATE Kullanicilar SET session_token = ?, last_login = NOW() WHERE id = ?";
                    vt_guncelle($token_guncelle, [$session_token, $kullanici[0]]);
                    
                    // Session'a kaydet
                    $_SESSION['kullanici_id'] = $kullanici[0];
                    $_SESSION['kullanici_adi'] = $kullanici[1];
                    $_SESSION['isim'] = $kullanici[2];
                    $_SESSION['soyisim'] = $kullanici[3];
                    $_SESSION['mail'] = $kullanici[4];
                    $_SESSION['role'] = $kullanici[5];
                    $_SESSION['firma'] = $kullanici[6];
                    $_SESSION['organization'] = $kullanici[7];
                    $_SESSION['session_token'] = $session_token;
                    
                    yonlendir('/dashboard?login=success');
                } else {
                    echo log_formati('WARN', "Başarısız giriş denemesi: $email_username") . "\n";
                    yonlendir('/giris?error=invalid_credentials');
                }
            } else {
                // Dashboard göster (LOGIN ZORUNLU)
                if (login_gerekli()) {
                    echo log_formati('WARN', 'Yetkisiz dashboard erişim - giriş sayfasına yönlendir') . "\n";
                    yonlendir('/giris');
                }
                
                // Dashboard'ı kullanıcı verileriyle render et
                $user_data = get_user_data();
                $template_content = file_get_contents(PROJE_KOK . '/sayfalar/dashboard.html');
                
                // Template variables'ları değiştir
                $template_content = str_replace('John Doe', htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı'), $template_content);
                $template_content = str_replace('Admin', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
                
                echo $template_content;
            }
            break;
            
        case '/anasayfa':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            // Dashboard'ı kullanıcı verileriyle render et
            $user_data = get_user_data();
            $template_content = file_get_contents(PROJE_KOK . '/sayfalar/dashboard.html');
            
            // Template variables'ları değiştir
            $template_content = str_replace('John Doe', htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı'), $template_content);
            $template_content = str_replace('Admin', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
            
            echo $template_content;
            break;
            
        case '/sifremi-unuttum':
        case '/forgot-password':
            readfile(PROJE_KOK . '/sayfalar/sifremi-unuttum.html');
            break;
            
        case '/reset-password':
            if ($method === 'POST') {
                $email = $_POST['email'] ?? '';
                echo log_formati('INFO', "Şifre sıfırlama talebi: $email") . "\n";
                
                if ($email) {
                    // E-posta gönder
                    $sonuc = sifre_sifirlama_emaili_gonder($email);
                    
                    if ($sonuc['success']) {
                        echo log_formati('SUCCESS', "Şifre sıfırlama e-postası gönderildi: $email") . "\n";
                        yonlendir('/sifremi-unuttum?success=true&message=' . urlencode('Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.'));
                    } else {
                        echo log_formati('WARN', "E-posta gönderilemedi: $email - " . $sonuc['message']) . "\n";
                        yonlendir('/sifremi-unuttum?error=email_error&message=' . urlencode($sonuc['message']));
                    }
                } else {
                    echo log_formati('WARN', 'Geçersiz şifre sıfırlama talebi') . "\n";
                    yonlendir('/sifremi-unuttum?error=invalid_email&message=' . urlencode('Lütfen geçerli bir e-posta adresi girin.'));
                }
            }
            break;
            
        case '/sifre-sifirla-onay':
            readfile(PROJE_KOK . '/sayfalar/sifre-sifirla-onay.html');
            break;
            
        case '/logout':
            $kullanici_adi = $_SESSION['kullanici_adi'] ?? 'bilinmeyen';
            $kullanici_id = $_SESSION['kullanici_id'] ?? null;
            
            // Veritabanındaki token'ı temizle
            if ($kullanici_id) {
                $token_temizle = "UPDATE Kullanicilar SET session_token = NULL WHERE id = ?";
                vt_guncelle($token_temizle, [$kullanici_id]);
            }
            
            echo log_formati('INFO', "Kullanıcı çıkış yaptı: $kullanici_adi") . "\n";
            session_destroy();
            yonlendir('/giris');
            break;
            
        case '/profile':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            // Profil sayfasını kullanıcı verileriyle render et
            $user_data = get_user_data();
            $template_content = file_get_contents(PROJE_KOK . '/sayfalar/profil.html');
            
            // Template variables'ları değiştir
            $template_content = str_replace('{{ user.kullanici_adi }}', htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı'), $template_content);
            $template_content = str_replace('{{ user.isim }}', htmlspecialchars($user_data['isim'] ?? ''), $template_content);
            $template_content = str_replace('{{ user.soyisim }}', htmlspecialchars($user_data['soyisim'] ?? ''), $template_content);
            $template_content = str_replace('{{ user.role }}', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
            $template_content = str_replace('{{ user.mail }}', htmlspecialchars($user_data['mail'] ?? 'email@example.com'), $template_content);
            
            // Static bilgileri değiştir - Dashboard kullanıcı dropdown
            $template_content = str_replace('John Doe', htmlspecialchars($user_data['isim'] . ' ' . $user_data['soyisim']), $template_content);
            $template_content = str_replace('Admin', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
            
            // Profil title'ı değiştir
            $template_content = str_replace('<title>Profil - Swixx Dashboard</title>', '<title>' . htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı') . ' - Profil - Swixx Dashboard</title>', $template_content);
            
            echo $template_content;
            break;
            
        case '/settings':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            // Settings sayfasını kullanıcı verileriyle render et
            $user_data = get_user_data();
            $template_content = file_get_contents(PROJE_KOK . '/sayfalar/profil-ayarlari.html');
            
            // Template variables'ları değiştir
            $template_content = str_replace('John Doe', htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı'), $template_content);
            $template_content = str_replace('Admin', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
            
            // Kullanıcı bilgilerini Jinja2 template syntax'ından değiştir
            $template_content = str_replace('{{ user.isim }}', htmlspecialchars($user_data['isim'] ?? ''), $template_content);
            $template_content = str_replace('{{ user.soyisim }}', htmlspecialchars($user_data['soyisim'] ?? ''), $template_content);
            $template_content = str_replace('{{ user.mail }}', htmlspecialchars($user_data['mail'] ?? ''), $template_content);
            $template_content = str_replace('{{ user.organization }}', htmlspecialchars($user_data['organization'] ?? ''), $template_content);
            
            // Flash messages template syntax'ını temizle (şimdilik kaldır)
            $template_content = preg_replace('/\{%\s*with\s+messages.*?endwith\s*%\}/s', '', $template_content);
            
            echo $template_content;
            break;
            
        case '/user':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            // Users sayfasını kullanıcı verileriyle render et
            $user_data = get_user_data();
            $template_content = file_get_contents(PROJE_KOK . '/sayfalar/kullanicilar.html');
            
            // Template variables'ları değiştir
            $template_content = str_replace('John Doe', htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı'), $template_content);
            $template_content = str_replace('Admin', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
            
            echo $template_content;
            break;
            
        case '/role':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            // Roles sayfasını kullanıcı verileriyle render et
            $user_data = get_user_data();
            $template_content = file_get_contents(PROJE_KOK . '/sayfalar/roller.html');
            
            // Template variables'ları değiştir
            $template_content = str_replace('John Doe', htmlspecialchars($user_data['kullanici_adi'] ?? 'Kullanıcı'), $template_content);
            $template_content = str_replace('Admin', htmlspecialchars($user_data['role'] ?? 'Kullanıcı'), $template_content);
            
            echo $template_content;
            break;
            
        case '/api/reset-password':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $token = $data['token'] ?? '';
                $yeni_sifre = $data['yeni_sifre'] ?? '';
                
                error_log("🔍 API Debug - Gelen veri: " . json_encode($data));
                error_log("🔍 Token: " . substr($token, 0, 20) . "... (uzunluk: " . strlen($token) . ")");
                error_log("🔍 Şifre uzunluğu: " . strlen($yeni_sifre));
                
                if (empty($token) || empty($yeni_sifre)) {
                    error_log("❌ Eksik veri: token=" . (empty($token) ? 'BOŞ' : 'VAR') . ", şifre=" . (empty($yeni_sifre) ? 'BOŞ' : 'VAR'));
                    json_yanit(['durum' => 'hata', 'mesaj' => 'Token ve yeni şifre gerekli'], 400);
                    return;
                }
                
                // Token ile şifre güncelle
                error_log("🚀 token_ile_sifre_guncelle() çağrılıyor...");
                $sonuc = token_ile_sifre_guncelle($token, $yeni_sifre);
                error_log("📝 Fonksiyon sonucu: " . json_encode($sonuc));
                
                if ($sonuc['success']) {
                    error_log("✅ Başarılı - Token: " . substr($token, 0, 10) . '...');
                    json_yanit(['durum' => 'basarili', 'mesaj' => 'Şifre başarıyla güncellendi'], 200);
                } else {
                    error_log("❌ Başarısız: " . $sonuc['message']);
                    json_yanit(['durum' => 'hata', 'mesaj' => $sonuc['message']], 400);
                }
            }
            break;
            
        // --- API ENDPOINTS ---
        
        case '/api/kullanicilar':
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
                return;
            }
            
            if ($method === 'GET') {
                try {
                    // Tüm kullanıcıları listele
                    $sorgu = "
                        SELECT id, kullanici_adi, isim, soyisim, mail, role, organization, active
                        FROM Kullanicilar
                        ORDER BY isim ASC
                    ";
                    
                    $sonuc = vt_sorgu($sorgu);
                    
                    error_log(log_formati('INFO', 'Kullanıcılar API - Sorgu sonucu: ' . (count($sonuc ?? []) . ' kayıt bulundu')));
                    
                    if ($sonuc && count($sonuc) > 0) {
                        $kullanicilar = [];
                        foreach ($sonuc as $row) {
                            $status_map = [0 => 'Inactive', 1 => 'Active'];
                            
                            $isim = trim($row[2] ?? '');
                            $soyisim = trim($row[3] ?? '');
                            $avatar = '';
                            
                            if ($isim && $soyisim) {
                                $avatar = strtoupper(mb_substr($isim, 0, 1) . mb_substr($soyisim, 0, 1));
                            } elseif ($isim) {
                                $avatar = strtoupper(mb_substr($isim, 0, 1));
                            } else {
                                $avatar = '?';
                            }
                            
                            $kullanicilar[] = [
                                'id' => $row[0],
                                'full_name' => trim("$isim $soyisim"),
                                'email' => $row[4],
                                'username' => $row[1],
                                'role' => $row[5] ?? 'user',
                                'organization' => $row[6] ?? '',
                                'status' => $status_map[$row[7]] ?? 'Inactive',
                                'avatar' => $avatar
                            ];
                        }
                        
                        error_log(log_formati('SUCCESS', 'Kullanıcılar API - ' . count($kullanicilar) . ' kullanıcı hazırlandı'));
                        json_yanit(['data' => $kullanicilar]);
                        return;
                    }
                    
                    error_log(log_formati('WARN', 'Kullanıcılar API - Veritabanında kayıt bulunamadı'));
                    json_yanit(['data' => []]);
                    return;
                    
                } catch (Exception $e) {
                    error_log(log_formati('ERROR', 'Kullanıcılar API Hatası: ' . $e->getMessage()));
                    json_yanit(['error' => 'Veritabanı hatası: ' . $e->getMessage()], 500);
                    return;
                }
            }
            break;
            
        case (preg_match('#^/api/kullanicilar/(\d+)/toggle-status$#', $uri, $matches) ? true : false):
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
            }
            
            if ($method === 'POST') {
                $kullanici_id = (int)$matches[1];
                
                // Mevcut durumu öğren
                $sorgu_check = "SELECT active FROM Kullanicilar WHERE id = ?";
                $result = vt_sorgu($sorgu_check, [$kullanici_id]);
                
                if (!$result) {
                    json_yanit(['error' => 'Kullanıcı bulunamadı'], 404);
                }
                
                $mevcut_durum = $result[0][0];
                $yeni_durum = ($mevcut_durum == 1) ? 0 : 1;
                
                // Durumu güncelle
                $sorgu_update = "UPDATE Kullanicilar SET active = ? WHERE id = ?";
                vt_guncelle($sorgu_update, [$yeni_durum, $kullanici_id]);
                
                $status_text = ($yeni_durum == 1) ? 'Active' : 'Inactive';
                echo log_formati('SUCCESS', "Kullanıcı $kullanici_id durumu $status_text olarak değiştirildi") . "\n";
                
                json_yanit(['status' => $status_text, 'active' => $yeni_durum]);
            }
            break;
            
        case '/api/kullanicilar/ekle':
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
                return;
            }
            
            if ($method === 'POST') {
                try {
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    error_log("🔵 API İsteği Başladı: /api/kullanicilar/ekle");
                    error_log("📥 Alınan JSON veriler: " . json_encode($data, JSON_UNESCAPED_UNICODE));
                    
                    // Gerekli alanları kontrol et
                    $gerekli = ['isim', 'soyisim', 'mail', 'kullanici_adi', 'sifre', 'role', 'organization'];
                    $eksik = [];
                    
                    foreach ($gerekli as $alan) {
                        if (empty($data[$alan])) {
                            $eksik[] = $alan;
                        }
                    }
                    
                    if (count($eksik) > 0) {
                        error_log("❌ Eksik alanlar: " . implode(', ', $eksik));
                        json_yanit(['error' => 'Eksik alanlar: ' . implode(', ', $eksik)], 400);
                        return;
                    }
                    
                    $isim = trim($data['isim']);
                    $soyisim = trim($data['soyisim']);
                    $mail = trim($data['mail']);
                    $kullanici_adi = trim($data['kullanici_adi']);
                    $role = trim($data['role'] ?? 'User');
                    $organization = trim($data['organization']);
                    $sifre = trim($data['sifre']);
                    $firma = trim($data['firma'] ?? '');
                    
                    error_log("✏️ Temizlenen veriler: isim=$isim, soyisim=$soyisim, mail=$mail, kullanici_adi=$kullanici_adi");
                    
                    // Şifreyi hash'le
                    $sifre_hash = hash('sha256', $sifre);
                    error_log("🔐 Şifre hash'lenmiş: " . substr($sifre_hash, 0, 10) . "...");
                    
                    // Sorgu
                    $sorgu = "
                        INSERT INTO Kullanicilar (kullanici_adi, isim, soyisim, mail, sifre, role, organization, firma, active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                    ";
                    
                    $params = [$kullanici_adi, $isim, $soyisim, $mail, $sifre_hash, $role, $organization, $firma];
                    error_log("🔄 Çalıştırılacak sorgu parametreleri: " . json_encode($params, JSON_UNESCAPED_UNICODE));
                    
                    $result = vt_guncelle($sorgu, $params);
                    error_log("✅ vt_guncelle sonucu: $result (etkilenen satır)");
                    
                    if ($result > 0) {
                        error_log(log_formati('SUCCESS', "Yeni kullanıcı eklendi: $kullanici_adi"));
                        json_yanit(['message' => 'Kullanıcı başarıyla eklendi', 'status' => 'success'], 201);
                        return;
                    } else {
                        error_log(log_formati('ERROR', 'Kullanıcı eklenirken hata oluştu'));
                        json_yanit(['error' => 'Kullanıcı eklenirken hata oluştu', 'status' => 'error'], 400);
                        return;
                    }
                } catch (Exception $e) {
                    error_log(log_formati('ERROR', 'Kullanıcı ekleme hatası: ' . $e->getMessage()));
                    json_yanit(['error' => 'Kullanıcı eklenirken hata oluştu', 'status' => 'error'], 500);
                    return;
                }
            }
            break;
            
        case (preg_match('#^/api/kullanicilar/sil/(\d+)$#', $uri, $matches) ? true : false):
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
                return;
            }
            
            if ($method === 'DELETE') {
                try {
                    $kullanici_id = (int)$matches[1];
                    
                    error_log("🔵 DELETE İsteği: /api/kullanicilar/sil");
                    error_log("📥 Silinecek Kullanıcı ID: $kullanici_id");
                    
                    $sorgu = "DELETE FROM Kullanicilar WHERE id = ?";
                    $params = [$kullanici_id];
                    
                    error_log("🔄 Çalıştırılacak sorgu: $sorgu");
                    error_log("📋 Parametreler: " . json_encode($params));
                    
                    $result = vt_guncelle($sorgu, $params);
                    error_log("✅ Silme sonucu: $result (etkilenen satır)");
                    
                    if ($result > 0) {
                        error_log(log_formati('SUCCESS', "Kullanıcı silindi: ID=$kullanici_id"));
                        json_yanit(['message' => 'Kullanıcı başarıyla silindi', 'status' => 'success'], 200);
                        return;
                    } else {
                        error_log(log_formati('ERROR', "Kullanıcı bulunamadı: ID=$kullanici_id"));
                        json_yanit(['error' => 'Kullanıcı bulunamadı', 'status' => 'error'], 404);
                        return;
                    }
                } catch (Exception $e) {
                    error_log(log_formati('ERROR', 'Silme hatası: ' . $e->getMessage()));
                    json_yanit(['error' => 'Kullanıcı silinirken hata oluştu', 'status' => 'error'], 500);
                    return;
                }
            }
            break;
            
        case (preg_match('#^/api/kullanicilar/guncelle/(\d+)$#', $uri, $matches) ? true : false):
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
                return;
            }
            
            if ($method === 'PUT') {
                try {
                    $kullanici_id = (int)$matches[1];
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    error_log("🔵 PUT İsteği: /api/kullanicilar/guncelle");
                    error_log("📥 Güncellenecek Kullanıcı ID: $kullanici_id");
                    error_log("📥 Alınan veriler: " . json_encode($data, JSON_UNESCAPED_UNICODE));
                    
                    $isim = trim($data['isim'] ?? '');
                    $soyisim = trim($data['soyisim'] ?? '');
                    $email = trim($data['email'] ?? '');
                    $username = trim($data['username'] ?? '');
                    $role = trim($data['role'] ?? '');
                    $organization = trim($data['organization'] ?? '');
                    
                    // Gerekli alanları kontrol et
                    if (empty($isim) || empty($soyisim) || empty($email) || empty($username) || empty($role) || empty($organization)) {
                        error_log("❌ Bazı alanlar eksik");
                        json_yanit(['error' => 'Tüm alanlar zorunludur', 'status' => 'error'], 400);
                        return;
                    }
                    
                    // Önce mevcut kullanıcıyı kontrol et
                    $kontrol_sorgu = "SELECT id, isim, soyisim, mail, kullanici_adi, role, organization FROM Kullanicilar WHERE id = ?";
                    $mevcut = vt_sorgu($kontrol_sorgu, [$kullanici_id]);
                    
                    if (!$mevcut || count($mevcut) === 0) {
                        error_log("❌ Kullanıcı bulunamadı: ID=$kullanici_id");
                        json_yanit(['error' => 'Kullanıcı bulunamadı', 'status' => 'error'], 404);
                        return;
                    }
                    
                    error_log("✅ Mevcut kullanıcı bulundu: " . json_encode($mevcut[0], JSON_UNESCAPED_UNICODE));
                    
                    // Sorgu
                    $sorgu = "
                        UPDATE Kullanicilar 
                        SET isim = ?, soyisim = ?, mail = ?, kullanici_adi = ?, role = ?, organization = ?
                        WHERE id = ?
                    ";
                    
                    $params = [$isim, $soyisim, $email, $username, $role, $organization, $kullanici_id];
                    error_log("🔄 Çalıştırılacak sorgu parametreleri: " . json_encode($params, JSON_UNESCAPED_UNICODE));
                    
                    $result = vt_guncelle($sorgu, $params);
                    error_log("✅ vt_guncelle sonucu: $result (etkilenen satır)");
                    
                    // 0 etkilenen satır = veri değişmedi (hata değil, başarılı sayılır)
                    error_log(log_formati('SUCCESS', "Kullanıcı güncellendi: ID=$kullanici_id"));
                    json_yanit(['message' => 'Kullanıcı başarıyla güncellendi', 'status' => 'success'], 200);
                    return;
                    
                } catch (Exception $e) {
                    error_log(log_formati('ERROR', "Güncelleme hatası: " . $e->getMessage()));
                    json_yanit(['error' => 'Güncelleme sırasında hata oluştu', 'status' => 'error'], 500);
                    return;
                }
            }
            break;
            
        case '/ayarlar-guncelle':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            if ($method === 'POST') {
                $isim = trim($_POST['firstName'] ?? '');
                $soyisim = trim($_POST['lastName'] ?? '');
                $mail = trim($_POST['email'] ?? '');
                $organization = trim($_POST['organization'] ?? '');
                $kullanici_id = $_SESSION['kullanici_id'];
                
                if (empty($isim) || empty($soyisim) || empty($mail)) {
                    $_SESSION['flash_error'] = 'İsim, soyisim ve e-mail alanları zorunludur!';
                    yonlendir('/settings');
                }
                
                $sorgu = "UPDATE Kullanicilar SET isim = ?, soyisim = ?, mail = ?, organization = ? WHERE id = ?";
                $etkilenen = vt_guncelle($sorgu, [$isim, $soyisim, $mail, $organization, $kullanici_id]);
                
                if ($etkilenen > 0) {
                    $_SESSION['isim'] = $isim;
                    $_SESSION['soyisim'] = $soyisim;
                    $_SESSION['mail'] = $mail;
                    $_SESSION['organization'] = $organization;
                    
                    echo log_formati('SUCCESS', "✅ Ayarlar güncellendi: $kullanici_id") . "\n";
                    $_SESSION['flash_success'] = 'Ayarlarınız başarıyla kaydedildi!';
                    yonlendir('/settings');
                } else {
                    $_SESSION['flash_error'] = 'Güncelleme başarısız oldu!';
                    yonlendir('/settings');
                }
            }
            break;
            
        case '/ubah-sifre':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            
            if ($method === 'POST') {
                $sifre_eski = trim($_POST['currentPassword'] ?? '');
                $sifre_yeni = trim($_POST['newPassword'] ?? '');
                $sifre_dogrula = trim($_POST['confirmPassword'] ?? '');
                $kullanici_id = $_SESSION['kullanici_id'];
                
                if (empty($sifre_eski) || empty($sifre_yeni) || empty($sifre_dogrula)) {
                    $_SESSION['flash_error'] = 'Tüm alanlar zorunludur!';
                    yonlendir('/settings');
                }
                
                if ($sifre_yeni !== $sifre_dogrula) {
                    $_SESSION['flash_error'] = 'Yeni şifreler eşleşmiyor!';
                    yonlendir('/settings');
                }
                
                if (strlen($sifre_yeni) < 6) {
                    $_SESSION['flash_error'] = 'Şifre en az 6 karakter olmalıdır!';
                    yonlendir('/settings');
                }
                
                // Eski şifreyi kontrol et
                $sorgu = "SELECT sifre FROM Kullanicilar WHERE id = ?";
                $sonuc = vt_sorgu($sorgu, [$kullanici_id]);
                
                if (!$sonuc || $sonuc[0][0] !== $sifre_eski) {
                    $_SESSION['flash_error'] = 'Mevcut şifre yanlış!';
                    yonlendir('/settings');
                }
                
                // Yeni şifreyi hash et ve kaydet
                $sifre_hash = hash('sha256', $sifre_yeni);
                $sorgu_guncelle = "UPDATE Kullanicilar SET sifre = ? WHERE id = ?";
                $etkilenen = vt_guncelle($sorgu_guncelle, [$sifre_hash, $kullanici_id]);
                
                if ($etkilenen > 0) {
                    echo log_formati('SUCCESS', "✅ Şifre değiştirildi: $kullanici_id") . "\n";
                    $_SESSION['flash_success'] = 'Şifreniz başarıyla değiştirildi!';
                    yonlendir('/settings');
                } else {
                    $_SESSION['flash_error'] = 'Şifre değişimi başarısız oldu!';
                    yonlendir('/settings');
                }
            }
            break;
            
        case '/api/saglik':
        case '/api/health':
            $memory_info = [
                'total' => memory_get_usage(true),
                'used' => memory_get_usage(),
                'peak' => memory_get_peak_usage(true)
            ];
            
            json_yanit(api_yaniti(true, [
                'durum' => 'ÇALIŞIYOR',
                'sunucu' => $ayarlar['uygulama_ayarlari']['ad'],
                'versiyon' => $ayarlar['uygulama_ayarlari']['versiyon'],
                'bellek' => $memory_info,
                'ortam' => $ayarlar['sunucu_ayarlari']['ortam']
            ], 'Sunucu başarıyla çalışıyor! 🐘'));
            break;
            
        case '/api/bilgi':
        case '/api/info':
            json_yanit(api_yaniti(true, [
                'ad' => $ayarlar['uygulama_ayarlari']['ad'],
                'versiyon' => $ayarlar['uygulama_ayarlari']['versiyon'],
                'aciklama' => $ayarlar['uygulama_ayarlari']['aciklama'],
                'yazar' => $ayarlar['uygulama_ayarlari']['yazar'],
                'teknolojiler' => ['PHP', 'MySQL', 'Vuexy Bootstrap 5', 'Modern Backend'],
                'özellikler' => ['Türkçe Dil Desteği', 'Responsive Tasarım', 'Modern UI/UX']
            ], 'Proje bilgileri başarıyla alındı'));
            break;
            
        case '/api/rol-yetkileri':
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
                return;
            }
            
            require_once PROJE_KOK . '/veritabani/rol_yetkileri.php';
            
            if ($method === 'GET') {
                // Rol ID'sini al
                $rol_id = isset($_GET['rol_id']) ? (int)$_GET['rol_id'] : null;
                
                if (!$rol_id) {
                    json_yanit(['error' => 'rol_id parametresi gerekli'], 400);
                    return;
                }
                
                // Rol yetkilerini getir
                $yetkiler = rol_yetkileri_getir($rol_id);
                
                // Rol isimleri
                $rol_isimleri = [1 => 'Admin', 2 => 'User'];
                
                // Yanıtı hazırla
                $yanit = [
                    'basarili' => true,
                    'rol_id' => $rol_id,
                    'rol_adi' => $rol_isimleri[$rol_id] ?? 'Bilinmeyen',
                    'yetkiler' => []
                ];
                
                // Yetkileri formatla
                foreach ($yetkiler as $sayfa => $yetki) {
                    $yanit['yetkiler'][] = [
                        'sayfa' => $sayfa,
                        'sayfa_adi' => ucfirst($sayfa),
                        'okuma' => $yetki['okuma'],
                        'yazma' => $yetki['yazma'],
                        'duzenleme' => $yetki['duzenleme'],
                        'silme' => $yetki['silme']
                    ];
                }
                
                json_yanit($yanit, 200);
                
            } elseif ($method === 'POST') {
                // Yetkileri güncelle
                $data = json_decode(file_get_contents('php://input'), true);
                $rol_id = isset($data['rol_id']) ? (int)$data['rol_id'] : null;
                $yetkiler = $data['yetkiler'] ?? [];
                
                if (!$rol_id || empty($yetkiler)) {
                    json_yanit(['error' => 'rol_id ve yetkiler gerekli'], 400);
                    return;
                }
                
                // Yetkileri güncelle
                $basarili = rol_yetkilerini_toplu_guncelle($rol_id, $yetkiler);
                
                if ($basarili) {
                    json_yanit([
                        'basarili' => true,
                        'mesaj' => 'Yetkiler başarıyla güncellendi'
                    ], 200);
                } else {
                    json_yanit([
                        'basarili' => false,
                        'mesaj' => 'Yetkiler güncellenirken hata oluştu'
                    ], 500);
                }
            }
            break;
            
        default:
            // 404 - API routes'u hariç tut
            if (strpos($uri, '/api/') === 0) {
                json_yanit(api_yaniti(false, null, "API endpoint bulunamadı: $uri", [
                    'mevcut_endpoints' => ['/api/saglik', '/api/bilgi', '/api/health', '/api/info', '/api/kullanicilar']
                ]), 404);
            }
            
            // Diğer sayfalar - Dashboard'a yönlendir
            echo log_formati('WARN', "404 - Bulunamayan sayfa dashboard'a yönlendirildi: $uri") . "\n";
            yonlendir('/dashboard');
            break;
    }
}

// Routing'i başlat
route();
