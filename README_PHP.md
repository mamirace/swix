# 🐘 Swix Dashboard - PHP Versiyonu

## 📋 Genel Bakış

Bu proje, Python Flask'dan PHP'ye tam çevrilmiş **Swix Dashboard** projesidir. Tüm özellikler korunmuştur ve Python projesindeki tüm fonksiyonlar PHP'de aynı şekilde çalışır.

## 🎯 Özellikler

✅ **Tam Python Eşdeğeri** - Tüm Python kodları PHP'ye çevrildi  
✅ **Vuexy Admin Template** - Modern ve profesyonel arayüz  
✅ **MySQL Veritabanı** - Kullanıcı yönetimi ve authentication  
✅ **Session Yönetimi** - Güvenli 7 günlük persistent session  
✅ **RESTful API** - Kullanıcı CRUD işlemleri  
✅ **Türkçe Dil Desteği** - Tam Türkçe arayüz ve mesajlar  
✅ **Login/Logout Sistemi** - Güvenli authentication  
✅ **Responsive Design** - Mobil uyumlu tasarım  

## 📂 Proje Yapısı

```
swix/
├── başlat.php              # Ana PHP başlatıcı (Python başlat.py karşılığı)
├── composer.json           # PHP bağımlılıkları
├── .htaccess              # Apache routing yapılandırması
├── .env                   # Ortam değişkenleri (veritabanı şifresi)
│
├── yapılandırma/
│   └── ayarlar.php        # Uygulama ayarları (Python ayarlar.py karşılığı)
│
├── genel/
│   ├── yardımcılar.php    # Yardımcı fonksiyonlar (Python yardımcılar.py karşılığı)
│   └── session_yonetimi.php  # Session helper fonksiyonları
│
├── veritabani/
│   ├── __init__.php       # Veritabanı modül başlatıcı
│   ├── sql_baglantisi.php # MySQL bağlantı yönetimi (Python sql_baglantisi.py karşılığı)
│   └── kullanicilar.php   # Kullanıcı login fonksiyonları (Python kullanicilar.py karşılığı)
│
├── sunucu/
│   └── sunucu.php         # Ana routing ve API endpoints (Python sunucu.py karşılığı)
│
├── sayfalar/              # HTML sayfaları (Python'la aynı)
│   ├── giris.html
│   ├── dashboard.html
│   ├── kullanicilar.html
│   ├── profil.html
│   ├── profil-ayarlari.html
│   ├── roller.html
│   └── sifremi-unuttum.html
│
└── varlıklar/             # Statik dosyalar (Python'la aynı)
    ├── css/
    ├── js/
    ├── img/
    └── vendor/
```

## 🚀 Kurulum ve Çalıştırma

### Gereksinimler

- **PHP 7.4 veya üzeri**
- **MySQL 5.7 veya üzeri**
- **Apache** (mod_rewrite aktif) veya **PHP Built-in Server**

### Adım 1: .env Dosyasını Oluştur

`.env` dosyası oluştur ve veritabanı bilgilerini gir:

```env
# Veritabanı Ayarları
DB_HOST=92.113.22.154
DB_PORT=3306
DB_USER=u534683512_mami
DB_PASSWORD=Mami321...
DB_NAME=u534683512_swixx

# Sunucu Ayarları
PORT=3000
HOST=localhost
PHP_ENV=development

# Güvenlik
SESSION_SECRET=swix-dashboard-secret-2026
```

### Adım 2: PHP Built-in Server ile Çalıştır

**Terminal'de şu komutu çalıştır:**

```bash
php -S localhost:3000 başlat.php
```

**veya composer ile:**

```bash
composer start
# veya
composer dev
```

### Adım 3: Tarayıcıda Aç

```
http://localhost:3000
```

## 🔐 Giriş Bilgileri

Varsayılan kullanıcı (veritabanınıza göre değişebilir):

```
Email/Kullanıcı Adı: muhammed.guc@bilgeguc.com
Şifre: admin
```

## 🌐 API Endpoints

### Authentication
- `POST /dashboard` - Login işlemi
- `GET /logout` - Çıkış yap

### Kullanıcı Yönetimi
- `GET /api/kullanicilar` - Tüm kullanıcıları listele
- `POST /api/kullanicilar/ekle` - Yeni kullanıcı ekle
- `PUT /api/kullanicilar/guncelle/{id}` - Kullanıcı güncelle
- `DELETE /api/kullanicilar/sil/{id}` - Kullanıcı sil
- `POST /api/kullanicilar/{id}/toggle-status` - Kullanıcı aktif/pasif

### Sistem
- `GET /api/saglik` veya `/api/health` - Sunucu sağlık kontrolü
- `GET /api/bilgi` veya `/api/info` - Proje bilgileri

### Ayarlar
- `POST /ayarlar-guncelle` - Profil bilgilerini güncelle
- `POST /ubah-sifre` - Şifre değiştir

## 📱 Sayfalar

- `/` - Ana sayfa (login ise dashboard, değilse giriş)
- `/giris` veya `/login` - Giriş sayfası
- `/dashboard` - Ana kontrol paneli (login gerekli)
- `/user` - Kullanıcı yönetimi (login gerekli)
- `/role` - Rol yönetimi (login gerekli)
- `/settings` - Profil ayarları (login gerekli)
- `/profile` - Profil sayfası (login gerekli)
- `/sifremi-unuttum` - Şifre sıfırlama

## 🔧 Apache ile Çalıştırma

### 1. Virtual Host Oluştur

Apache `httpd-vhosts.conf` dosyasına ekle:

```apache
<VirtualHost *:80>
    ServerName swix.local
    DocumentRoot "C:/Users/Muhammed Güç/OneDrive/Masaüstü/swix"
    
    <Directory "C:/Users/Muhammed Güç/OneDrive/Masaüstü/swix">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2. hosts Dosyasını Düzenle

`C:\Windows\System32\drivers\etc\hosts` dosyasına ekle:

```
127.0.0.1    swix.local
```

### 3. Apache'yi Yeniden Başlat

```bash
httpd -k restart
```

### 4. Tarayıcıda Aç

```
http://swix.local
```

## 🔍 Veritabanı Test

Veritabanı bağlantısını test et:

```bash
php veritabani/sql_baglantisi.php
```

Çıktı:
```
BAŞARILI: MySQL 8.0.x
```

## 📊 Python vs PHP Karşılaştırması

| Özellik | Python (Flask) | PHP |
|---------|---------------|-----|
| **Framework** | Flask | Native PHP (routing manuel) |
| **Session** | Flask session | PHP $_SESSION |
| **Routing** | @app.route() | sunucu.php switch/case |
| **Database** | mysql-connector-python | MySQLi |
| **Server** | flask run | php -S veya Apache |
| **Template** | Jinja2 | Native PHP / HTML |
| **JSON** | jsonify() | json_encode() |

## 🛠️ Önemli Dosyalar ve Fonksiyonlar

### 1. `başlat.php`
Ana başlatıcı dosya. `sunucu/sunucu.php`'yi çağırır.

### 2. `sunucu/sunucu.php`
- Tüm routing mantığı
- API endpoints
- Session kontrolü
- Authentication

### 3. `veritabani/sql_baglantisi.php`
```php
mysql_baglan()        // MySQL bağlantısı oluştur
vt_sorgu($sql, $params)     // SELECT sorguları
vt_guncelle($sql, $params)  // INSERT/UPDATE/DELETE
vt_test()            // Bağlantı testi
```

### 4. `veritabani/kullanicilar.php`
```php
login_kontrol($email, $password)  // Login kontrolü
```

### 5. `genel/yardımcılar.php`
```php
tarih_formatlama()           // Tarih formatla
api_yaniti($success, $data)  // API response
log_formati($level, $msg)    // Log formatla
json_yanit($data, $code)     // JSON response gönder
yonlendir($url)             // Redirect
```

### 6. `yapılandırma/ayarlar.php`
```php
ayarlar_yukle()  // Tüm ayarları döndür
```

## ⚙️ Session Yönetimi

Session otomatik olarak 7 gün boyunca aktif kalır:

```php
// Session başlat
session_start();

// Kullanıcı login mi?
if (isset($_SESSION['kullanici_id'])) {
    // Login
}

// Kullanıcı bilgileri
$user = [
    'kullanici_id' => $_SESSION['kullanici_id'],
    'kullanici_adi' => $_SESSION['kullanici_adi'],
    'isim' => $_SESSION['isim'],
    'role' => $_SESSION['role']
];
```

## 🔐 Güvenlik Özellikleri

✅ **Prepared Statements** - SQL injection koruması  
✅ **Session Security** - HttpOnly, SameSite cookies  
✅ **Password Hashing** - SHA-256 hash  
✅ **XSS Protection** - HTML escaping  
✅ **CSRF Protection** - Session token  
✅ **.env Protection** - .htaccess ile korumalı  

## 🐛 Hata Ayıklama

### Error Logging

PHP hatalarını görmek için `sunucu/sunucu.php` başında:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### MySQL Hataları

Veritabanı hatalarını kontrol et:

```php
echo log_formati('ERROR', $e->getMessage());
```

### Session Sorunları

Session'ı kontrol et:

```php
print_r($_SESSION);
```

## 📝 Notlar

1. **Dosya İsimleri**: Python dosyalarıyla aynı (`.py` yerine `.php`)
2. **Fonksiyon İsimleri**: Python'dakilerle aynı (snake_case)
3. **API Responses**: Python'dakiyle aynı format
4. **HTML Dosyaları**: Hiç değişmedi, aynen kullanılıyor

## 🎨 Python Kodlarının Durumu

**Python dosyaları hala mevcut ve çalışıyor!** PHP versiyonu Python'un yanında duruyor.

- `başlat.py` → Python server'ı başlatır
- `başlat.php` → PHP server'ı başlatır

İkisi de aynı veritabanını kullanır ve aynı HTML dosyalarını sunar.

## 🚀 Production Deployment

### 1. .htaccess HTTPS'i Aktif Et

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 2. PHP Error Reporting'i Kapat

```php
error_reporting(0);
ini_set('display_errors', 0);
```

### 3. .env Dosyasını Güvenli Tut

`.env` dosyası asla git'e commit edilmemeli.

## 📞 Destek

Herhangi bir sorun yaşarsanız:

1. Veritabanı bağlantısını test edin
2. PHP error log'larını kontrol edin
3. Session'ların aktif olduğundan emin olun
4. Apache mod_rewrite'ın aktif olduğundan emin olun

## 📜 Lisans

MIT License - Mamirace © 2026

---

**🎉 Tebrikler!** Python projeniz artık tamamen PHP'de çalışıyor!

**Test komutları:**
```bash
# PHP server başlat
php -S localhost:3000 başlat.php

# Veritabanı test
php veritabani/sql_baglantisi.php

# Tarayıcıda aç
start http://localhost:3000
```
