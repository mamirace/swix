<?php
// 👥 Swix Dashboard - Kullanicilar Login Kontrol
// Python kullanicilar.py dosyasının PHP karşılığı
// Basit login fonksiyonu

require_once __DIR__ . '/sql_baglantisi.php';

/**
 * Login kontrol - mail VEYA kullanici_adi ve şifreyi kontrol et
 * @param string $mail Email veya kullanıcı adı
 * @param string $sifre Şifre
 * @return array|null Kullanıcı bilgileri veya null
 */
function login_kontrol($mail, $sifre) {
    // Email veya kullanıcı adı ile kontrol et
    $sorgu_mail = "
        SELECT id, kullanici_adi, isim, soyisim, mail, sifre, role, active, firma, organization
        FROM Kullanicilar 
        WHERE (mail = ? OR kullanici_adi = ?) AND active = 1
    ";
    
    $sonuc_mail = vt_sorgu($sorgu_mail, [$mail, $mail]);
    
    if ($sonuc_mail && count($sonuc_mail) > 0) {
        $kullanici = $sonuc_mail[0];
        $sifre_veritabani = $kullanici[5];
        
        // Şifre kontrolü - plaintext ve hash'lenmiş şifreleri destekle
        $sifre_plaintext_eslesme = ($sifre_veritabani === $sifre);
        $sifre_hash = hash('sha256', $sifre);
        $sifre_hash_eslesme = ($sifre_veritabani === $sifre_hash);
        
        if ($sifre_plaintext_eslesme || $sifre_hash_eslesme) {
            // Dönen değerler: [id, kullanici_adi, isim, soyisim, mail, role, firma, organization]
            return [
                $kullanici[0],  // id
                $kullanici[1],  // kullanici_adi
                $kullanici[2],  // isim
                $kullanici[3],  // soyisim
                $kullanici[4],  // mail
                $kullanici[6],  // role
                $kullanici[8],  // firma
                $kullanici[9]   // organization
            ];
        }
    }
    
    return null;
}

// Test
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    echo "Login Test:\n";
    $kullanici = login_kontrol("muhammed.guc@bilgeguc.com", "admin");
    if ($kullanici) {
        echo "Başarılı: {$kullanici[1]} ({$kullanici[3]})\n";
    } else {
        echo "Başarısız\n";
    }
}
