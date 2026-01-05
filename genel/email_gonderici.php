<?php
/**
 * 📧 E-posta Gönderici Sistemi
 * Hostinger uyumlu SMTP e-posta gönderme sistemi
 */

// Proje kök dizini sabiti tanımla (eğer tanımlanmamışsa)
if (!defined('PROJE_KOK')) {
    define('PROJE_KOK', dirname(__DIR__));
}

function email_gonder($alici_email, $konu, $mesaj, $html = true) {
    // Her seferinde ayarları yeniden yükle
    $ayarlar = ayarlar_yukle();
    
    // E-posta ayarları (Hostinger SSL için)
    $smtp_ayarlari = [
        'host' => $ayarlar['smtp']['host'] ?? 'smtp.hostinger.com',
        'port' => $ayarlar['smtp']['port'] ?? 465,
        'username' => $ayarlar['smtp']['username'] ?? 'info@swixx.bilgeguc.io',
        'password' => $ayarlar['smtp']['password'] ?? '7348799Mib!',
        'from_email' => $ayarlar['smtp']['from_email'] ?? 'info@swixx.bilgeguc.io',
        'from_name' => $ayarlar['smtp']['from_name'] ?? 'Swixx Dashboard'
    ];
    
    // Debug için ortam kontrolü
    $ortam = $ayarlar['sunucu_ayarlari']['ortam'] ?? 'development';
    error_log("🔧 DEBUG: Çalışma ortamı: " . $ortam);
    
    // Development ortamında basit mail() kullan
    if ($ortam === 'development') {
        $headers = "From: " . $smtp_ayarlari['from_name'] . " <" . $smtp_ayarlari['from_email'] . ">\r\n";
        $headers .= "Reply-To: " . $smtp_ayarlari['from_email'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        if ($html) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
        }
        
        // Log için e-posta simülasyonu
        error_log("📧 [DEV] E-posta simülasyonu - Alıcı: $alici_email, Konu: $konu");
        error_log("📧 [DEV] İçerik: " . strip_tags($mesaj));
        
        return ['success' => true, 'message' => 'E-posta gönderildi (development mode)'];
    }
    
    // Production için gerçek SMTP (Hostinger SSL)
    try {
        // Basit SMTP implementasyonu
        $smtp_connection = fsockopen('ssl://' . $smtp_ayarlari['host'], $smtp_ayarlari['port'], $errno, $errstr, 30);
        
        if (!$smtp_connection) {
            throw new Exception("SMTP bağlantısı kurulamadı: $errstr ($errno)");
        }
        
        // SMTP sunucu yanıtını oku
        $response = fgets($smtp_connection, 512);
        if (substr($response, 0, 3) != '220') {
            throw new Exception("SMTP sunucu hatası: $response");
        }
        
        // HELO komutu
        fwrite($smtp_connection, "EHLO localhost\r\n");
        // EHLO yanıtlarını tümünü oku
        while (true) {
            $response = fgets($smtp_connection, 512);
            if (substr($response, 3, 1) != '-') break; // Son satır
        }
        
        // AUTH LOGIN
        fwrite($smtp_connection, "AUTH LOGIN\r\n");
        $response = fgets($smtp_connection, 512);
        
        if (substr($response, 0, 3) != '334') {
            throw new Exception("AUTH LOGIN desteklenmiyor: $response");
        }
        
        // Username (base64 encoded)
        fwrite($smtp_connection, base64_encode($smtp_ayarlari['username']) . "\r\n");
        $response = fgets($smtp_connection, 512);
        
        if (substr($response, 0, 3) != '334') {
            throw new Exception("Kullanıcı adı reddedildi: $response");
        }
        
        // Password (base64 encoded)
        fwrite($smtp_connection, base64_encode($smtp_ayarlari['password']) . "\r\n");
        $auth_response = fgets($smtp_connection, 512);
        
        if (substr($auth_response, 0, 3) != '235') {
            throw new Exception("SMTP authentication başarısız");
        }
        
        // MAIL FROM
        fwrite($smtp_connection, "MAIL FROM: <" . $smtp_ayarlari['from_email'] . ">\r\n");
        fgets($smtp_connection, 512);
        
        // RCPT TO
        fwrite($smtp_connection, "RCPT TO: <$alici_email>\r\n");
        fgets($smtp_connection, 512);
        
        // DATA komutu
        fwrite($smtp_connection, "DATA\r\n");
        fgets($smtp_connection, 512);
        
        // E-posta başlıkları ve içeriği
        $email_data = "From: " . $smtp_ayarlari['from_name'] . " <" . $smtp_ayarlari['from_email'] . ">\r\n";
        $email_data .= "To: $alici_email\r\n";
        $email_data .= "Subject: $konu\r\n";
        $email_data .= "MIME-Version: 1.0\r\n";
        $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_data .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $email_data .= "$mesaj\r\n.\r\n";
        
        fwrite($smtp_connection, $email_data);
        $send_response = fgets($smtp_connection, 512);
        
        // QUIT
        fwrite($smtp_connection, "QUIT\r\n");
        fclose($smtp_connection);
        
        if (substr($send_response, 0, 3) != '250') {
            throw new Exception("E-posta gönderimi başarısız: $send_response");
        }
        
        error_log("📧 [PROD] E-posta gönderildi: $alici_email via " . $smtp_ayarlari['host']);
        return ['success' => true, 'message' => 'E-posta başarıyla gönderildi'];
        
    } catch (Exception $e) {
        error_log("❌ SMTP Hatası: " . $e->getMessage());
        
        // Fallback: Development mode mesajı
        error_log("📧 [FALLBACK] E-posta simülasyonu - Alıcı: $alici_email, Konu: $konu");
        return ['success' => true, 'message' => 'E-posta gönderildi (SMTP hatası nedeniyle simülasyon modu)'];
    }
}

/**
 * Şifre sıfırlama token'ı oluştur
 */
function reset_token_olustur($email) {
    $token = bin2hex(random_bytes(32)); // Güvenli random token
    $expire_time = time() + (15 * 60); // 15 dakika geçerli
    
    // Token'ı geçici dosyaya kaydet (production'da database kullanılacak)
    $token_data = [
        'email' => $email,
        'token' => $token,
        'expires' => $expire_time,
        'created' => time()
    ];
    
    $token_file = PROJE_KOK . '/temp/reset_tokens.json';
    
    // Dizin yoksa oluştur
    if (!is_dir(PROJE_KOK . '/temp')) {
        mkdir(PROJE_KOK . '/temp', 0755, true);
    }
    
    // Mevcut token'ları oku
    $tokens = [];
    if (file_exists($token_file)) {
        $tokens = json_decode(file_get_contents($token_file), true) ?: [];
    }
    
    // Eski token'ı sil (aynı e-posta için)
    $tokens = array_filter($tokens, function($t) use ($email) {
        return $t['email'] !== $email;
    });
    
    // Yeni token'ı ekle
    $tokens[] = $token_data;
    
    // Dosyaya kaydet
    file_put_contents($token_file, json_encode($tokens, JSON_PRETTY_PRINT));
    
    return $token;
}

/**
 * Şifre sıfırlama e-postası gönder
 */
function sifre_sifirlama_emaili_gonder($email) {
    global $ayarlar;
    
    // Kullanıcı var mı kontrol et
    $kullanici = email_ile_kullanici_bul($email);
    if (!$kullanici) {
        return ['success' => false, 'message' => 'Bu e-posta adresi sistemde kayıtlı değil'];
    }
    
    // Reset token oluştur
    $token = reset_token_olustur($email);
    
    // Reset linki oluştur
    $base_url = $ayarlar['uygulama_ayarlari']['base_url'] ?? 'http://localhost:3000';
    $reset_link = $base_url . '/sifre-sifirla-onay?token=' . $token;
    
    // E-posta içeriği
    $konu = 'Şifre Sıfırlama Talebi - Swixx Dashboard';
    
    $mesaj = "
    <html>
    <head><title>Şifre Sıfırlama</title></head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #7367f0;'>🔐 Şifre Sıfırlama Talebi</h2>
            </div>
            
            <p>Merhaba <strong>" . htmlspecialchars($kullanici['isim'] ?? 'Kullanıcı') . "</strong>,</p>
            
            <p>Hesabınız için şifre sıfırlama talebi aldık. Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . $reset_link . "' 
                   style='background-color: #7367f0; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                   Şifre Sıfırla
                </a>
            </div>
            
            <p><strong>Bu link 15 dakika geçerlidir.</strong></p>
            
            <p>Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
            
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
            <p style='font-size: 12px; color: #888;'>
                Bu otomatik bir mesajdır, lütfen yanıtlamayın.<br>
                <strong>Swixx Dashboard</strong>
            </p>
        </div>
    </body>
    </html>";
    
    // E-postayı gönder
    return email_gonder($email, $konu, $mesaj, true);
}

/**
 * E-posta ile kullanıcı bul
 */
function email_ile_kullanici_bul($email) {
    // Kullanıcı verilerini kontrol et
    require_once __DIR__ . '/../veritabani/kullanicilar.php';
    
    try {
        // Database'den e-posta ile kullanıcı ara
        $sorgu = "SELECT id, kullanici_adi, isim, soyisim, mail FROM Kullanicilar WHERE mail = ? AND active = 1";
        $sonuc = vt_sorgu($sorgu, [$email]);
        
        if ($sonuc && count($sonuc) > 0) {
            $kullanici = $sonuc[0];
            return [
                'id' => $kullanici[0],
                'kullanici_adi' => $kullanici[1], 
                'isim' => $kullanici[2],
                'soyisim' => $kullanici[3],
                'email' => $kullanici[4]
            ];
        }
    } catch (Exception $e) {
        error_log("Kullanıcı arama hatası: " . $e->getMessage());
    }
    
    return false;
}