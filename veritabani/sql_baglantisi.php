<?php
// 🗄️ Swix Dashboard - MySQL Bağlantı Yöneticisi
// Python sql_baglantisi.py dosyasının PHP karşılığı

require_once __DIR__ . '/../yapılandırma/ayarlar.php';

/**
 * Basit MySQL bağlantısı
 * @return mysqli MySQL bağlantı nesnesi
 */
function mysql_baglan() {
    $ayarlar = ayarlar_yukle();
    $vt = $ayarlar['veritabani_ayarlari'];
    
    // .env dosyasından şifreyi al
    $sifre = $_ENV['DB_PASSWORD'] ?? $vt['sifre'];
    
    // MySQLi bağlantısı oluştur
    $baglanti = new mysqli(
        $vt['host'],
        $vt['kullanici'],
        $sifre,
        $vt['veritabani_adi'],
        $vt['port']
    );
    
    // Bağlantı hatası kontrolü
    if ($baglanti->connect_error) {
        error_log("MySQL Bağlantı Hatası: " . $baglanti->connect_error);
        throw new Exception("Veritabanı bağlantısı kurulamadı: " . $baglanti->connect_error);
    }
    
    // Karakter setini ayarla
    $baglanti->set_charset($vt['charset']);
    
    return $baglanti;
}

/**
 * SELECT sorguları için
 * @param string $sorgu SQL sorgusu
 * @param array $parametreler Sorgu parametreleri (opsiyonel)
 * @return array|null Sonuç dizisi veya null
 */
function vt_sorgu($sorgu, $parametreler = null) {
    try {
        $baglanti = mysql_baglan();
        
        // Prepared statement kullan
        if ($parametreler !== null && count($parametreler) > 0) {
            $stmt = $baglanti->prepare($sorgu);
            
            if (!$stmt) {
                throw new Exception("Sorgu hazırlama hatası: " . $baglanti->error);
            }
            
            // Parametre tiplerini belirle
            $types = '';
            $bind_params = [];
            
            foreach ($parametreler as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';  // blob
                }
                $bind_params[] = $param;
            }
            
            // Parametreleri bind et
            if (count($bind_params) > 0) {
                $stmt->bind_param($types, ...$bind_params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Sonuçları diziye çevir
            $sonuc = [];
            while ($row = $result->fetch_row()) {
                $sonuc[] = $row;
            }
            
            $stmt->close();
        } else {
            // Parametresiz sorgu
            $result = $baglanti->query($sorgu);
            
            if (!$result) {
                throw new Exception("Sorgu hatası: " . $baglanti->error);
            }
            
            $sonuc = [];
            while ($row = $result->fetch_row()) {
                $sonuc[] = $row;
            }
        }
        
        $baglanti->close();
        return $sonuc;
        
    } catch (Exception $e) {
        error_log("Sorgu hatası: " . $e->getMessage());
        return null;
    }
}

/**
 * INSERT/UPDATE/DELETE sorguları için
 * @param string $sorgu SQL sorgusu
 * @param array $parametreler Sorgu parametreleri (opsiyonel)
 * @return int Etkilenen satır sayısı veya hata durumunda -1
 */
function vt_guncelle($sorgu, $parametreler = null) {
    try {
        $baglanti = mysql_baglan();
        
        // Prepared statement kullan
        if ($parametreler !== null && count($parametreler) > 0) {
            $stmt = $baglanti->prepare($sorgu);
            
            if (!$stmt) {
                throw new Exception("Sorgu hazırlama hatası: " . $baglanti->error);
            }
            
            // Parametre tiplerini belirle
            $types = '';
            $bind_params = [];
            
            foreach ($parametreler as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';  // blob
                }
                $bind_params[] = $param;
            }
            
            // Parametreleri bind et
            if (count($bind_params) > 0) {
                $stmt->bind_param($types, ...$bind_params);
            }
            
            $stmt->execute();
            $etkilenen = $stmt->affected_rows;
            $stmt->close();
        } else {
            // Parametresiz sorgu
            $baglanti->query($sorgu);
            $etkilenen = $baglanti->affected_rows;
        }
        
        $baglanti->close();
        return $etkilenen;
        
    } catch (Exception $e) {
        error_log("Güncelleme hatası: " . $e->getMessage());
        return -1;
    }
}

/**
 * Bağlantı testi
 * @return array [başarılı (bool), mesaj (string)]
 */
function vt_test() {
    try {
        $baglanti = mysql_baglan();
        $version = $baglanti->server_info;
        $baglanti->close();
        
        return [true, "MySQL $version"];
        
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }
}

// Test
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    list($success, $message) = vt_test();
    echo ($success ? "BAŞARILI: " : "HATA: ") . $message . "\n";
}
