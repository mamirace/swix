# 🐘 Swixx Dashboard - Kurulum Rehberi

## Web Hosting Kurulumu (Önerilen)

### 1. Dosyaları Upload Etme
- Tüm proje dosyalarını `public_html/` veya `www/` klasörüne yükleyin
- Ana giriş dosyası: `index.php`
- Assets dosyaları: `varlıklar/` klasöründe

### 2. Gerekli Dosyalar
✅ `index.php` - Ana giriş dosyası (otomatik oluşturuldu)
✅ `.htaccess` - Apache routing yapılandırması (mevcut)
✅ `sunucu/` - Backend mantığı
✅ `varlıklar/` - CSS, JS, resim dosyaları
✅ `veritabani/` - Veritabanı işlemleri

### 3. Web Server Gereksinimleri
- PHP 7.4+ (önerilen PHP 8.2+)
- Apache mod_rewrite etkin
- MySQL/MariaDB veritabanı
- cURL extension

### 4. Veritabanı Kurulumu
1. MySQL veritabanı oluşturun
2. `yapılandırma/ayarlar.php` dosyasındaki veritabanı bilgilerini güncelleyin
3. Gerekli tabloları oluşturun

## Development Server (Local Test)

### PHP Built-in Server
```bash
php -S localhost:3000 router.php
```

### XAMPP ile Test
- XAMPP'ı başlatın (Apache + MySQL)
- Proje klasörünü `htdocs/` içine kopyalayın
- `http://localhost/swixx/` adresine gidin

## Dosya Yapısı
```
public_html/
├── index.php          # Ana giriş dosyası
├── .htaccess          # Apache yapılandırması  
├── sunucu/            # Backend PHP dosyaları
├── varlıklar/         # Frontend assets (/assets/ URL'i ile erişim)
├── veritabani/        # Veritabanı işlemleri
├── sayfalar/          # HTML template dosyaları
└── yapılandırma/      # Yapılandırma dosyaları
```

## URL Yapısı
- `yoursite.com/` → Ana sayfa
- `yoursite.com/dashboard` → Dashboard
- `yoursite.com/assets/css/style.css` → CSS dosyaları (varlıklar/css/style.css)
- `yoursite.com/giris` → Giriş sayfası

## Güvenlik
- Production ortamında `error_reporting` kapatın
- `.env` dosyasındaki hassas bilgileri güncelleyin
- Dosya izinlerini kontrol edin (644 for files, 755 for directories)

## Sorun Giderme
- **404 Hatası**: `.htaccess` dosyasının yüklendiğini kontrol edin
- **500 Hatası**: PHP error log'larını kontrol edin
- **Assets Yüklenmezse**: `varlıklar/` klasörünün yüklendiğini kontrol edin