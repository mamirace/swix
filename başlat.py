#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🐍 Swix Dashboard - Python Flask Başlatıcı
Bu dosya Python Flask sunucusunu başlatmak için kullanılır
"""

import sys
import os
from pathlib import Path

# Proje kök dizinini Python path'ine ekle
proje_kok = Path(__file__).parent
sys.path.insert(0, str(proje_kok))

# Sunucu modülünü import et ve çalıştır
if __name__ == '__main__':
    try:
        from sunucu.sunucu import app, ayarlar, PORT
        
        print("🐍 Python Flask sunucusu başlatılıyor...")
        
        app.run(
            host=ayarlar['sunucu_ayarlari']['host'],
            port=PORT,
            debug=ayarlar['sunucu_ayarlari']['ortam'] == 'development',
            use_reloader=True,
            threaded=True
        )
        
    except ImportError as e:
        print(f"❌ Import hatası: {e}")
        print("📦 Gerekli paketleri yüklemek için: pip install -r requirements.txt")
        sys.exit(1)
    except Exception as e:
        print(f"❌ Sunucu başlatma hatası: {e}")
        sys.exit(1)