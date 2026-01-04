<?php
// 🚀 Swix Dashboard - PHP Ana Sunucusu
// Python Flask sunucu.py dosyasının PHP karşılığı
// JavaScript/Node.js'den PHP'ye çevrilmiş modern CRM dashboard

// Hata raporlamayı aç (development için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
 * Login gerekli mi kontrol et
 * @return bool
 */
function login_gerekli() {
    return !isset($_SESSION['kullanici_id']);
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
                    
                    // Session'a kaydet
                    $_SESSION['kullanici_id'] = $kullanici[0];
                    $_SESSION['kullanici_adi'] = $kullanici[1];
                    $_SESSION['isim'] = $kullanici[2];
                    $_SESSION['soyisim'] = $kullanici[3];
                    $_SESSION['mail'] = $kullanici[4];
                    $_SESSION['role'] = $kullanici[5];
                    $_SESSION['firma'] = $kullanici[6];
                    $_SESSION['organization'] = $kullanici[7];
                    
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
                readfile(PROJE_KOK . '/sayfalar/dashboard.html');
            }
            break;
            
        case '/anasayfa':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            readfile(PROJE_KOK . '/sayfalar/dashboard.html');
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
                    echo log_formati('INFO', "Şifre sıfırlama e-postası gönderildi: $email") . "\n";
                    yonlendir('/sifremi-unuttum?success=true');
                } else {
                    echo log_formati('WARN', 'Geçersiz şifre sıfırlama talebi') . "\n";
                    yonlendir('/sifremi-unuttum?error=invalid_email');
                }
            }
            break;
            
        case '/logout':
            $kullanici_adi = $_SESSION['kullanici_adi'] ?? 'bilinmeyen';
            echo log_formati('INFO', "Kullanıcı çıkış yaptı: $kullanici_adi") . "\n";
            session_destroy();
            yonlendir('/giris');
            break;
            
        case '/profile':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            readfile(PROJE_KOK . '/sayfalar/profil.html');
            break;
            
        case '/settings':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            readfile(PROJE_KOK . '/sayfalar/profil-ayarlari.html');
            break;
            
        case '/user':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            readfile(PROJE_KOK . '/sayfalar/kullanicilar.html');
            break;
            
        case '/role':
            if (login_gerekli()) {
                yonlendir('/giris');
            }
            readfile(PROJE_KOK . '/sayfalar/roller.html');
            break;
            
        // --- API ENDPOINTS ---
        
        case '/api/kullanicilar':
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
            }
            
            if ($method === 'GET') {
                // Tüm kullanıcıları listele
                $sorgu = "
                    SELECT id, kullanici_adi, isim, soyisim, mail, role, organization, active
                    FROM Kullanicilar
                    ORDER BY isim ASC
                ";
                
                $sonuc = vt_sorgu($sorgu);
                
                echo log_formati('INFO', 'Kullanıcılar API - Sorgu sonucu: ' . (count($sonuc ?? []) . ' kayıt bulundu')) . "\n";
                
                if ($sonuc) {
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
                            'role' => $row[5],
                            'organization' => $row[6],
                            'status' => $status_map[$row[7]] ?? 'Inactive',
                            'avatar' => $avatar
                        ];
                    }
                    
                    echo log_formati('SUCCESS', 'Kullanıcılar API - ' . count($kullanicilar) . ' kullanıcı hazırlandı') . "\n";
                    json_yanit(['data' => $kullanicilar]);
                }
                
                echo log_formati('WARN', 'Kullanıcılar API - Veritabanında kayıt bulunamadı') . "\n";
                json_yanit(['data' => []]);
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
            }
            
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                echo "\n🔵 ========== API İsteği Başladı: /api/kullanicilar/ekle ==========\n";
                echo "📥 Alınan JSON veriler: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
                
                // Gerekli alanları kontrol et
                $gerekli = ['isim', 'soyisim', 'mail', 'kullanici_adi', 'sifre', 'role', 'organization'];
                $eksik = [];
                
                foreach ($gerekli as $alan) {
                    if (empty($data[$alan])) {
                        $eksik[] = $alan;
                    }
                }
                
                if (count($eksik) > 0) {
                    echo "❌ Eksik alanlar: " . implode(', ', $eksik) . "\n";
                    json_yanit(['error' => 'Eksik alanlar: ' . implode(', ', $eksik)], 400);
                }
                
                $isim = trim($data['isim']);
                $soyisim = trim($data['soyisim']);
                $mail = trim($data['mail']);
                $kullanici_adi = trim($data['kullanici_adi']);
                $role = trim($data['role'] ?? 'User');
                $organization = trim($data['organization']);
                $sifre = trim($data['sifre']);
                $firma = trim($data['firma'] ?? '');
                
                echo "✏️ Temizlenen veriler: isim=$isim, soyisim=$soyisim, mail=$mail, kullanici_adi=$kullanici_adi\n";
                
                // Şifreyi hash'le
                $sifre_hash = hash('sha256', $sifre);
                echo "🔐 Şifre hash'lenmiş: " . substr($sifre_hash, 0, 10) . "...\n";
                
                // Sorgu
                $sorgu = "
                    INSERT INTO Kullanicilar (kullanici_adi, isim, soyisim, mail, sifre, role, organization, firma, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                ";
                
                $params = [$kullanici_adi, $isim, $soyisim, $mail, $sifre_hash, $role, $organization, $firma];
                echo "🔄 Çalıştırılacak sorgu parametreleri: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
                
                $result = vt_guncelle($sorgu, $params);
                echo "✅ vt_guncelle sonucu: $result (etkilenen satır)\n";
                
                if ($result > 0) {
                    echo log_formati('SUCCESS', "Yeni kullanıcı eklendi: $kullanici_adi") . "\n";
                    echo "🟢 ========== İşlem Başarılı ==========\n\n";
                    json_yanit(['message' => 'Kullanıcı başarıyla eklendi', 'status' => 'success'], 201);
                } else {
                    echo log_formati('ERROR', 'Kullanıcı eklenirken hata oluştu') . "\n";
                    echo "🔴 ========== İşlem Başarısız ==========\n\n";
                    json_yanit(['error' => 'Kullanıcı eklenirken hata oluştu', 'status' => 'error'], 400);
                }
            }
            break;
            
        case (preg_match('#^/api/kullanicilar/sil/(\d+)$#', $uri, $matches) ? true : false):
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
            }
            
            if ($method === 'DELETE') {
                $kullanici_id = (int)$matches[1];
                
                echo "\n🔵 ========== DELETE İsteği: /api/kullanicilar/sil ==========\n";
                echo "📥 Silinecek Kullanıcı ID: $kullanici_id\n";
                
                $sorgu = "DELETE FROM Kullanicilar WHERE id = ?";
                $params = [$kullanici_id];
                
                echo "🔄 Çalıştırılacak sorgu: $sorgu\n";
                echo "📋 Parametreler: " . json_encode($params) . "\n";
                
                $result = vt_guncelle($sorgu, $params);
                echo "✅ Silme sonucu: $result (etkilenen satır)\n";
                
                if ($result > 0) {
                    echo log_formati('SUCCESS', "Kullanıcı silindi: ID=$kullanici_id") . "\n";
                    echo "🟢 ========== Silme Başarılı ==========\n\n";
                    json_yanit(['message' => 'Kullanıcı başarıyla silindi', 'status' => 'success'], 200);
                } else {
                    echo log_formati('ERROR', "Kullanıcı bulunamadı: ID=$kullanici_id") . "\n";
                    echo "🔴 ========== Silme Başarısız ==========\n\n";
                    json_yanit(['error' => 'Kullanıcı bulunamadı', 'status' => 'error'], 404);
                }
            }
            break;
            
        case (preg_match('#^/api/kullanicilar/guncelle/(\d+)$#', $uri, $matches) ? true : false):
            if (login_gerekli()) {
                json_yanit(['error' => 'Unauthorized'], 401);
            }
            
            if ($method === 'PUT') {
                $kullanici_id = (int)$matches[1];
                $data = json_decode(file_get_contents('php://input'), true);
                
                echo "\n🔵 ========== PUT İsteği: /api/kullanicilar/guncelle ==========\n";
                echo "📥 Güncellenecek Kullanıcı ID: $kullanici_id\n";
                echo "📥 Alınan veriler: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
                
                $isim = trim($data['isim'] ?? '');
                $soyisim = trim($data['soyisim'] ?? '');
                $email = trim($data['email'] ?? '');
                $username = trim($data['username'] ?? '');
                $role = trim($data['role'] ?? '');
                $organization = trim($data['organization'] ?? '');
                
                // Gerekli alanları kontrol et
                if (empty($isim) || empty($soyisim) || empty($email) || empty($username) || empty($role) || empty($organization)) {
                    echo "❌ Bazı alanlar eksik\n";
                    json_yanit(['error' => 'Tüm alanlar zorunludur', 'status' => 'error'], 400);
                }
                
                // Sorgu
                $sorgu = "
                    UPDATE Kullanicilar 
                    SET isim = ?, soyisim = ?, mail = ?, kullanici_adi = ?, role = ?, organization = ?
                    WHERE id = ?
                ";
                
                $params = [$isim, $soyisim, $email, $username, $role, $organization, $kullanici_id];
                echo "🔄 Çalıştırılacak sorgu parametreleri: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
                
                $result = vt_guncelle($sorgu, $params);
                echo "✅ vt_guncelle sonucu: $result (etkilenen satır)\n";
                
                if ($result > 0) {
                    echo log_formati('SUCCESS', "Kullanıcı güncellendi: ID=$kullanici_id") . "\n";
                    echo "🟢 ========== Güncelleme Başarılı ==========\n\n";
                    json_yanit(['message' => 'Kullanıcı başarıyla güncellendi', 'status' => 'success'], 200);
                } else {
                    echo log_formati('ERROR', "Kullanıcı bulunamadı veya değişiklik yok: ID=$kullanici_id") . "\n";
                    echo "🔴 ========== Güncelleme Başarısız ==========\n\n";
                    json_yanit(['error' => 'Kullanıcı bulunamadı', 'status' => 'error'], 404);
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
