# 🗄️ Swix Dashboard - Veritabanı Modülü

# Temel SQL bağlantı fonksiyonları
from .sql_baglantisi import vt_test, vt_sorgu, vt_guncelle

# Kullanicilar login kontrol
from .kullanicilar import login_kontrol

__version__ = '1.0.0'
__author__ = 'mamirace'

# Modül açıklaması
__doc__ = """
Swix Dashboard MySQL Veritabanı Modülü

Bu modül şunları içerir:
- MySQL bağlantı yönetimi
- Sorgu çalıştırma fonksiyonları
- Kullanicilar tablosu CRUD işlemleri
- Bağlantı test fonksiyonu
"""