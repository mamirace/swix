# 👥 Swix Dashboard - Kullanicilar Login Kontrol
# Basit login fonksiyonu

import sys
import os
from hashlib import sha256

sys.path.append(os.path.dirname(os.path.dirname(__file__)))

from veritabani.sql_baglantisi import vt_sorgu

def login_kontrol(mail, sifre):
    """Login kontrol - mail VEYA kullanici_adi ve şifreyi kontrol et"""
    # Email veya kullanıcı adı ile kontrol et
    sorgu_mail = """
    SELECT id, kullanici_adi, isim, soyisim, mail, sifre, role, active, firma, organization
    FROM Kullanicilar 
    WHERE (mail = %s OR kullanici_adi = %s) AND active = 1
    """
    
    sonuc_mail = vt_sorgu(sorgu_mail, (mail, mail))
    
    if sonuc_mail:
        kullanici = sonuc_mail[0]
        sifre_veritabani = kullanici[5]
        
        # Şifre kontrolü - plaintext ve hash'lenmiş şifreleri destekle
        sifre_plaintext_eslesme = sifre_veritabani == sifre
        sifre_hash = sha256(sifre.encode()).hexdigest()
        sifre_hash_eslesme = sifre_veritabani == sifre_hash
        
        if sifre_plaintext_eslesme or sifre_hash_eslesme:
            # Dönen değerler: (id, kullanici_adi, isim, soyisim, mail, role, firma, organization)
            return (kullanici[0], kullanici[1], kullanici[2], kullanici[3], kullanici[4], kullanici[6], kullanici[8], kullanici[9])
    
    return None

# Test
if __name__ == "__main__":
    print("Login Test:")
    kullanici = login_kontrol("muhammed.guc@bilgeguc.com", "admin")
    if kullanici:
        print(f"Başarılı: {kullanici[1]} ({kullanici[3]})")
    else:
        print("Başarısız")