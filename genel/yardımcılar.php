<?php
// 🛠️ Swixx Dashboard - PHP Yardımcı Fonksiyonlar
// Python yardımcılar.py dosyasının PHP karşılığı  
// Bu dosya genel kullanım için yardımcı fonksiyonları içerir

/**
 * Tarih formatlaması - Türkçe format
 * @param string|null $tarih Formatlanacak tarih (null ise şimdi)
 * @return string Formatlanmış tarih
 */
function tarih_formatlama($tarih = null) {
    if ($tarih === null) {
        $tarih = time();
    } elseif (is_string($tarih)) {
        $tarih = strtotime($tarih);
    }
    
    // Türkçe locale ayarla
    setlocale(LC_TIME, 'tr_TR.UTF-8', 'Turkish_Turkey.1254', 'tr_TR', 'turkish');
    
    return date('d.m.Y H:i:s', $tarih);
}

/**
 * Güvenli path oluşturma
 * @param string ...$parcalar Path parçaları
 * @return string Güvenli path
 */
function guvenli_path(...$parcalar) {
    $path = implode(DIRECTORY_SEPARATOR, $parcalar);
    return str_replace('\\', '/', $path);
}

/**
 * API yanıt formatı - Standardize edilmiş API yanıtları
 * @param bool $basarili İşlem başarılı mı?
 * @param mixed $veri Döndürülecek veri
 * @param string $mesaj İşlem mesajı
 * @param array|null $meta Meta bilgiler
 * @return array Formatlanmış API yanıtı
 */
function api_yaniti($basarili = true, $veri = null, $mesaj = '', $meta = null) {
    $yanit = [
        'basarili' => $basarili,
        'veri' => $veri,
        'mesaj' => $mesaj,
        'zaman' => date('c')  // ISO 8601 format
    ];
    
    if ($meta !== null) {
        $yanit['meta'] = $meta;
    }
    
    return $yanit;
}

/**
 * Log formatlaması - Konsistant log formatı
 * @param string $seviye Log seviyesi (INFO, WARN, ERROR, SUCCESS)
 * @param string $mesaj Log mesajı
 * @return string Formatlanmış log mesajı
 */
function log_formati($seviye, $mesaj) {
    $zaman = tarih_formatlama();
    
    // Renk kodları (terminal için)
    $renkler = [
        'INFO' => "\033[94m",     // Mavi
        'WARN' => "\033[93m",     // Sarı
        'ERROR' => "\033[91m",    // Kırmızı
        'SUCCESS' => "\033[92m",  // Yeşil
        'RESET' => "\033[0m"      // Reset
    ];
    
    $renk = $renkler[$seviye] ?? '';
    $reset = $renkler['RESET'];
    
    return "{$renk}[{$zaman}] {$seviye}: {$mesaj}{$reset}";
}

/**
 * Dosya boyutunu okunabilir formata çevir
 * @param int $boyut_byte Byte cinsinden dosya boyutu
 * @return string Formatlanmış dosya boyutu
 */
function dosya_boyutu_formatlama($boyut_byte) {
    $birimler = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    foreach ($birimler as $birim) {
        if ($boyut_byte < 1024.0) {
            return sprintf("%.1f %s", $boyut_byte, $birim);
        }
        $boyut_byte /= 1024.0;
    }
    
    return sprintf("%.1f TB", $boyut_byte);
}

/**
 * JSON'a güvenli string dönüşümü
 * @param mixed $obj Dönüştürülecek obje
 * @return string JSON güvenli string
 */
function json_guvenli_str($obj) {
    return json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * HTML karakterlerini güvenli hale getir (XSS koruması)
 * @param string $str Temizlenecek string
 * @return string Güvenli string
 */
function guvenli_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON yanıtı gönder
 * @param array $data Gönderilecek veri
 * @param int $status_code HTTP status kodu
 */
function json_yanit($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Redirect yap
 * @param string $url Yönlendirilecek URL
 */
function yonlendir($url) {
    header("Location: $url");
    exit;
}

/**
 * Token ile şifre güncelleme
 * @param string $token Reset token'ı
 * @param string $yeni_sifre Yeni şifre
 * @return array Sonuç dizisi
 */
function token_ile_sifre_guncelle($token, $yeni_sifre) {
    try {
        require_once __DIR__ . '/../veritabani/sql_baglantisi.php';
        
        // Token dosyasından token'ı ara
        $token_file = __DIR__ . '/../temp/reset_tokens.json';
        if (!file_exists($token_file)) {
            return [
                'success' => false,
                'message' => 'Geçersiz token'
            ];
        }
        
        $tokens = json_decode(file_get_contents($token_file), true) ?: [];
        $found_token = null;
        
        foreach ($tokens as $t) {
            if ($t['token'] === $token && $t['expires'] > time()) {
                $found_token = $t;
                break;
            }
        }
        
        if (!$found_token) {
            return [
                'success' => false,
                'message' => 'Geçersiz veya süresi dolmuş token'
            ];
        }
        
        $email = $found_token['email'];
        
        // Kullanıcıyı bul
        $sorgu = "SELECT id FROM Kullanicilar WHERE mail = ? AND active = 1";
        $result = vt_sorgu($sorgu, [$email]);
        
        if (!$result || empty($result)) {
            return [
                'success' => false,
                'message' => 'Kullanıcı bulunamadı'
            ];
        }
        
        $kullanici_id = $result[0][0];
        
        // Şifreyi hash'le
        $hashlenmis_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        
        // Şifreyi güncelle
        $guncelle_sorgu = "UPDATE Kullanicilar SET sifre = ? WHERE id = ?";
        $guncelle_sonuc = vt_guncelle($guncelle_sorgu, [$hashlenmis_sifre, $kullanici_id]);
        
        if ($guncelle_sonuc > 0) {
            // Token'ı dosyadan sil
            $tokens = array_filter($tokens, function($t) use ($token) {
                return $t['token'] !== $token;
            });
            file_put_contents($token_file, json_encode($tokens, JSON_PRETTY_PRINT));
            
            return [
                'success' => true,
                'message' => 'Şifre başarıyla güncellendi',
                'email' => $email
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Şifre güncellenirken hata oluştu'
            ];
        }
        
    } catch (Exception $e) {
        error_log('Token ile şifre güncelleme hatası: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Sistem hatası'
        ];
    }
}

// Test
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    echo "Yardımcı Fonksiyonlar Test:\n";
    echo "Tarih: " . tarih_formatlama() . "\n";
    echo "Dosya boyutu: " . dosya_boyutu_formatlama(1024 * 1024 * 2.5) . "\n";
    echo log_formati('INFO', 'Test mesajı') . "\n";
}
