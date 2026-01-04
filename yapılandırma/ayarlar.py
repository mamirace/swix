# 🔧 Swix Dashboard - Python Flask Yapılandırma Ayarları  
# JavaScript ayarlar.js dosyasının Python karşılığı
# Bu dosya uygulamanın tüm yapılandırma ayarlarını içerir

import os
from dotenv import load_dotenv
from pathlib import Path

# .env dosyasını yükle (güvenlik için)
env_dosyasi = Path(__file__).parent.parent / '.env'
env_local_dosyasi = Path(__file__).parent.parent / '.env.local'

load_dotenv(env_dosyasi)  # Önce temel .env
load_dotenv(env_local_dosyasi)  # Sonra yerel şifreleri (.env.local öncelikli)

def ayarlar_yukle():
    """Yapılandırma ayarlarını Python dict olarak döndürür"""
    
    sunucu_ayarlari = {
        'port': int(os.getenv('PORT', 3000)),
        'host': os.getenv('HOST', 'localhost'),
        'ortam': os.getenv('FLASK_ENV', 'development')
    }
    
    uygulama_ayarlari = {
        'ad': 'Swix Dashboard',
        'versiyon': '1.0.0',
        'aciklama': 'Modern Python Flask Vuexy Admin Dashboard',
        'yazar': 'mamirace'
    }
    
    rota_ayarlari = {
        'ana_sayfa': '/giris',
        'login_sayfasi': '/giris',
        'dashboard_sayfasi': '/dashboard',
        'api_temel_rota': '/api'
    }
    
    varlıklar_yolu = {
        'css': '/varlıklar/css',
        'js': '/varlıklar/js',
        'resimler': '/varlıklar/img',
        'fontlar': '/varlıklar/fonts'
    }
    
    guvenlik_ayarlari = {
        'session_gizli_anahtar': os.getenv('SESSION_SECRET', 'swix-dashboard-secret-2026'),
        'cookie_max_age': 24 * 60 * 60 * 1000,  # 24 saat
        'https_zorunlu': os.getenv('FLASK_ENV') == 'production'
    }
    
    veritabani_ayarlari = {
        'host': os.getenv('DB_HOST', '92.113.22.154'),  # Orijinal IP'ye geri dön
        'port': int(os.getenv('DB_PORT', 3306)),
        'kullanici': os.getenv('DB_USER', 'u534683512_mami'),
        'sifre': os.getenv('DB_PASSWORD'),  # .env dosyasından güvenli okuma
        'veritabani_adi': os.getenv('DB_NAME', 'u534683512_swixx'),
        'charset': 'utf8mb4',
        'baglanti_timeout': 30
    }
    
    return {
        'sunucu_ayarlari': sunucu_ayarlari,
        'uygulama_ayarlari': uygulama_ayarlari,
        'rota_ayarlari': rota_ayarlari,
        'varlıklar_yolu': varlıklar_yolu,
        'guvenlik_ayarlari': guvenlik_ayarlari,
        'veritabani_ayarlari': veritabani_ayarlari
    }