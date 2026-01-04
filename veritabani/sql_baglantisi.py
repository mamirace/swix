# 🗄️ Swix Dashboard - MySQL Bağlantı Yöneticisi
import mysql.connector
import os
from dotenv import load_dotenv

# .env dosyalarını yükle (.env.local öncelikli)
load_dotenv('.env')        # Önce ana dosya
load_dotenv('.env.local')  # Sonra local geçersiz kılar

def mysql_baglan():
    """Basit MySQL bağlantısı"""
    return mysql.connector.connect(
        host='92.113.22.154',
        port=3306,
        user='u534683512_mami',
        password=os.getenv('DB_PASSWORD'),
        database='u534683512_swixx',
        auth_plugin='mysql_native_password',
        ssl_disabled=True
    )

def vt_sorgu(sorgu, parametreler=None):
    """SELECT sorguları için"""
    try:
        baglanti = mysql_baglan()
        cursor = baglanti.cursor()
        cursor.execute(sorgu, parametreler)
        sonuc = cursor.fetchall()
        cursor.close()
        baglanti.close()
        return sonuc
    except Exception as e:
        print(f"Sorgu hatası: {e}")
        return None

def vt_guncelle(sorgu, parametreler=None):
    """INSERT/UPDATE/DELETE sorguları için"""
    try:
        baglanti = mysql_baglan()
        cursor = baglanti.cursor()
        cursor.execute(sorgu, parametreler)
        baglanti.commit()
        etkilenen = cursor.rowcount
        cursor.close()
        baglanti.close()
        return etkilenen
    except Exception as e:
        print(f"Güncelleme hatası: {e}")
        return -1

def vt_test():
    """Bağlantı testi"""
    try:
        baglanti = mysql_baglan()
        cursor = baglanti.cursor()
        cursor.execute("SELECT VERSION()")
        version = cursor.fetchone()
        cursor.close()
        baglanti.close()
        return True, f"MySQL {version[0]}"
    except Exception as e:
        return False, str(e)

# Test
if __name__ == "__main__":
    success, message = vt_test()
    print("BAŞARILI:" if success else "HATA:", message)