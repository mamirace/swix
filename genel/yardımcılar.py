# 🛠️ Swix Dashboard - Python Flask Yardımcı Fonksiyonlar
# JavaScript yardımcılar.js dosyasının Python karşılığı  
# Bu dosya genel kullanım için yardımcı fonksiyonları içerir

from datetime import datetime
import locale
import os

# Türkçe locale ayarla (Windows için)
try:
    locale.setlocale(locale.LC_TIME, 'Turkish_Turkey.1254')
except:
    try:
        locale.setlocale(locale.LC_TIME, 'tr_TR.UTF-8')
    except:
        pass  # Varsayılan locale kullan

def tarih_formatlama(tarih=None):
    """
    Tarih formatlaması - Türkçe format
    
    Args:
        tarih (datetime): Formatlanacak tarih
        
    Returns:
        str: Formatlanmış tarih
    """
    if tarih is None:
        tarih = datetime.now()
    
    return tarih.strftime('%d.%m.%Y %H:%M:%S')

def guvenli_path(*parcalar):
    """
    Güvenli path oluşturma
    
    Args:
        *parcalar: Path parçaları
        
    Returns:
        str: Güvenli path
    """
    return os.path.join(*parcalar).replace('\\', '/')

def api_yaniti(basarili=True, veri=None, mesaj='', meta=None):
    """
    API yanıt formatı - Standardize edilmiş API yanıtları
    
    Args:
        basarili (bool): İşlem başarılı mı?
        veri: Döndürülecek veri
        mesaj (str): İşlem mesajı
        meta: Meta bilgiler
        
    Returns:
        dict: Formatlanmış API yanıtı
    """
    yanit = {
        'basarili': basarili,
        'veri': veri,
        'mesaj': mesaj,
        'zaman': datetime.now().isoformat()
    }
    
    if meta:
        yanit['meta'] = meta
        
    return yanit

def log_formati(seviye, mesaj):
    """
    Log formatlaması - Konsistant log formatı
    
    Args:
        seviye (str): Log seviyesi (INFO, WARN, ERROR)
        mesaj (str): Log mesajı
        
    Returns:
        str: Formatlanmış log mesajı
    """
    zaman = tarih_formatlama()
    
    # Renk kodları
    renkler = {
        'INFO': '\033[94m',   # Mavi
        'WARN': '\033[93m',   # Sarı
        'ERROR': '\033[91m',  # Kırmızı
        'RESET': '\033[0m'    # Reset
    }
    
    renk = renkler.get(seviye, '')
    reset = renkler['RESET']
    
    return f"{renk}[{zaman}] {seviye}: {mesaj}{reset}"

def dosya_boyutu_formatlama(boyut_byte):
    """
    Dosya boyutunu okunabilir formata çevir
    
    Args:
        boyut_byte (int): Byte cinsinden dosya boyutu
        
    Returns:
        str: Formatlanmış dosya boyutu
    """
    for birim in ['B', 'KB', 'MB', 'GB']:
        if boyut_byte < 1024.0:
            return f"{boyut_byte:.1f} {birim}"
        boyut_byte /= 1024.0
    return f"{boyut_byte:.1f} TB"

def json_guvenli_str(obj):
    """
    JSON'a güvenli string dönüşümü
    
    Args:
        obj: Dönüştürülecek obje
        
    Returns:
        str: JSON güvenli string
    """
    import json
    try:
        return json.dumps(obj, ensure_ascii=False, indent=2)
    except:
        return str(obj)