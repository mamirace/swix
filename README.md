# 🐍 Swix Dashboard - Python Flask Edition

Modern ve profesyonel CRM dashboard uygulaması. **Vuexy Bootstrap 5** teması ile **Python Flask** backend entegrasyonu.

## � Teknoloji Yığını

- **Backend**: Python 3.13+ & Flask 🐍
- **Frontend**: Vuexy Admin Template (Bootstrap 5)
- **Theme**: Professional CRM Dashboard
- **Database**: SQLite (gelecek güncellemelerde)

## 📋 Migration Durumu

- ✅ **JavaScript → Python** backend geçişi tamamlandı
- ✅ **Flask sunucusu** tüm route'larla çalışıyor
- ✅ **Frontend tema dosyaları** korundu
- ✅ **API endpoint'leri** aynı çalışıyor
- ✅ **Türkçe lokalizasyon** korundu

## 🗂️ Kurumsal Klasör Yapısı

```
swix/
├── 📁 genel/                    # Yardımcı fonksiyonlar
│   └── yardımcılar.py          # Python utilities
├── 📁 sayfalar/                # HTML sayfaları (tema dosyaları)
│   ├── giris.html             # Giriş sayfası
│   ├── dashboard.html         # CRM Dashboard
│   └── sifremi-unuttum.html   # Şifre sıfırlama
├── 📁 sunucu/                  # Backend sunucu
│   ├── sunucu.js              # Eski Node.js sunucu
│   └── sunucu.py              # Yeni Python Flask sunucusu 🐍
├── 📁 varlıklar/               # Tema dosyaları (CSS, JS, resim)
├── 📁 yapılandırma/           # Konfigürasyon
│   ├── ayarlar.js             # Eski Node.js ayarları
│   └── ayarlar.py             # Yeni Python ayarları 🐍
├── başlat.py                  # Python sunucu başlatıcı 🐍
├── requirements.txt           # Python bağımlılıkları 🐍
├── 📁 sayfalar/              # HTML sayfaları
│   └── giris.html            # Vuexy giriş sayfası (eski: login.html)
├── 📁 varlıklar/             # Tema dosyaları (eski: assets/)
│   ├── css/                  # Theme CSS dosyaları
│   ├── js/                   # Theme JS dosyaları 
│   ├── fonts/                # Icon fonts
│   └── img/                  # Theme görselleri
├── 📁 yapılandırma/          # Konfigürasyon dosyaları
│   └── ayarlar.js           # Ana yapılandırma ayarları
├── 📁 genel/                 # Genel yardımcı fonksiyonlar
│   └── yardımcılar.js       # Utility functions
├── package.json              # Node.js proje ayarları
└── README.md                 # Bu dosya
```

## 🚀 Kurulum ve Çalıştırma

### Türkçe Komutlar (Önerilen):
```bash
npm install
npm run başlat          # Sunucuyu başlat
# VEYA
npm run geliştirme     # Development modunda çalıştır
```

### English Commands (Backward compatibility):
```bash
npm install
npm start               # Start server
# OR  
npm run dev            # Development mode
```

**Sunucu Adresi:** http://localhost:3000

## 🔗 API Endpoint'leri

### 🇹🇷 Türkçe API'lar (Yeni):
- `GET /` - Giriş sayfası (ana sayfa)
- `GET /giris` - Giriş sayfası (alternatif)
- `POST /dashboard` - Giriş form işleme
- `GET /api/saglik` - Sunucu sağlık kontrolü (Türkçe)
- `GET /api/bilgi` - Proje bilgileri (Türkçe)

### 🇺🇸 English APIs (Geriye Uyumluluk):
- `GET /login` - Login page (redirects to /giris)
- `GET /api/health` - Server health check  
- `GET /api/info` - Project information

## 🎨 Tema Özellikleri

### Vuexy Template Features:
- **Professional Admin Dashboard** design
- **Bootstrap 5.3+** framework
- **Responsive & Mobile-First** approach
- **Modern UI/UX** components
- **Icon Fonts** integration
- **jQuery 3.6+** functionality

### Türkçe Lokalizasyon:
- Form labels ve butonlar Türkçe
- Error mesajları Türkçe
- API response'ları Türkçe
- Log mesajları Türkçe

## 🔧 Teknik Detaylar

### Teknoloji Stack:
```json
{
  "backend": "Node.js 16+ (ES Modules)",
  "framework": "Express.js 4.18+",
  "frontend": "Vuexy Bootstrap 5 Template",
  "styling": "Bootstrap 5.3 + Custom CSS",
  "javascript": "jQuery 3.6 + Vanilla JS",
  "architecture": "Enterprise Folder Structure"
}
```

### Yapılandırma Sistemi:
- **[yapılandırma/ayarlar.js](yapılandırma/ayarlar.js)** - Tüm sistem ayarları
- **[genel/yardımcılar.js](genel/yardımcılar.js)** - Utility functions
- **Environment variables** desteği
- **Production/Development** mod ayrımı

### Önemli Özellikler:
- ✅ **Graceful shutdown** handling
- ✅ **Error middleware** ve logging
- ✅ **Static file serving** optimization
- ✅ **Security headers** ve best practices
- ✅ **API rate limiting** ready
- ✅ **HTTPS production** ready

## 🌐 Deployment

### Hostinger Business Hosting:
```bash
# 1. Dosyaları upload edin
# 2. Node.js application olarak configure edin
# 3. Entry point: sunucu/sunucu.js
# 4. npm install && npm start
```

### GitHub Pages & Hosting:
- **Repository:** https://github.com/mamirace/swix
- **GitHub Pages:** Static serving
- **Hostinger/Railway/Heroku:** Full Node.js support

### Production Deployment:
```bash
export NODE_ENV=production
export PORT=80
npm run başlat
```

## 👨‍💻 Geliştirme

### Yeni Sayfa Ekleme:
1. [sayfalar/](sayfalar/) klasörüne yeni HTML dosyası ekleyin
2. [sunucu/sunucu.js](sunucu/sunucu.js) dosyasına yeni route ekleyin
3. Gerekirse [varlıklar/](varlıklar/) klasörüne assets ekleyin

### API Endpoint Ekleme:
1. [sunucu/sunucu.js](sunucu/sunucu.js) dosyasında yeni API route tanımlayın
2. [genel/yardımcılar.js](genel/yardımcılar.js) dosyasından utility fonksiyonları kullanın
3. [yapılandırma/ayarlar.js](yapılandırma/ayarlar.js) dosyasından config ayarlarını alın

### Konfigürasyon Değişikliği:
- Tüm ayarlar [yapılandırma/ayarlar.js](yapılandırma/ayarlar.js) dosyasında merkezileştirilmiştir
- Environment variables ile override edilebilir
- Production/development ayrımı otomatik

## 📝 Lisans

MIT License - Ticari kullanım için uygundur.

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b yeni-özellik`)  
3. Commit yapın (`git commit -am 'Yeni özellik eklendi'`)
4. Push yapın (`git push origin yeni-özellik`)
5. Pull Request açın

## 📞 İletişim

- **GitHub:** [@mamirace](https://github.com/mamirace)
- **Repository:** [swix](https://github.com/mamirace/swix)
- **Issues:** [GitHub Issues](https://github.com/mamirace/swix/issues)

---

> 🎉 **Swix Dashboard** - Türkiye'nin ilk kurumsal Vuexy Node.js template'i!
- **Vercel:** Zero-config deployment

**GitHub Repository:** https://github.com/mamirace/swix

## 🚀 Hostinger'a Yükleme

1. **Dosyaları Hazırla:**
   - Tüm dosyaları seçin ve GitHub'a yükleyin
   - ZIP olarak sıkıştırın

2. **Hostinger File Manager:**
   - Hostinger kontrolpanelinize giriş yapın
   - "File Manager"ı açın
   - `public_html` klasörüne gidin
   - ZIP dosyasını yükleyin ve çıkarın

3. **Manuel FTP Yükleme:**
   - FTP client (FileZilla) kullanın
   - Hostinger FTP bilgilerinizi girin
   - Dosyaları `public_html` klasörüne yükleyin

## 🎯 Özellikler ve Kullanım

### 1. Ana Sayfa Mesajı
- Büyük "Merhaba!" başlığı ile ziyaretçileri karşılar
- "Başlayın" butonuna tıklayınca özel mesaj gösterir

### 2. İnteraktif Öğeler
- **Hover Efektleri:** Kartların üzerine gelince animasyon
- **Parti Efekti:** Butona tıklayınca renkli partikül animasyonu
- **Smooth Scroll:** Menü linklerine tıklayınca yumuşak geçiş

### 3. Gizli Özellikler (Easter Eggs)
- Logo'ya 5 kez tıklayın - sürpriz efekt!
- `Ctrl + M` - Hoşgeldin mesajı
- `Ctrl + P` - Parti efekti

### 4. Responsive Tasarım
- Desktop, tablet, mobil uyumlu
- Hamburger menü (mobilde)
- Touch-friendly butonlar

## 🎨 Renk Teması

```css
Primary: #7367f0    (Mor)
Success: #28c76f    (Yeşil)
Info: #00cfe8       (Mavi)
Warning: #ff9f43    (Turuncu)
Danger: #ea5455     (Kırmızı)
```

## ⚙️ Özelleştirme

### Başlığı Değiştirmek
```html
<!-- index.html içinde -->
<h1 class="hero-title">Merhaba! 👋</h1>
```

### Renkleri Değiştirmek
```css
/* style.css içinde :root bölümünde */
:root {
    --primary-color: #yeni-renk;
}
```

### Logo Değiştirmek
```html
<!-- Navbar içinde -->
<div class="nav-logo">
    <h2>YeniLogoAdi</h2>
</div>
```

## 📱 Sosyal Medya Linkleri

Footer bölümünde sosyal medya linklerinizi güncelleyebilirsiniz:

```html
<div class="social-links">
    <a href="https://facebook.com/sizin-sayfa"><i class="fab fa-facebook"></i></a>
    <a href="https://twitter.com/sizin-hesap"><i class="fab fa-twitter"></i></a>
    <a href="https://instagram.com/sizin-hesap"><i class="fab fa-instagram"></i></a>
</div>
```

## 🔧 Geliştirme

### Yeni Sayfa Eklemek
1. Yeni HTML dosyası oluşturun
2. `style.css` ve `script.js` dosyalarını link edin
3. Navbar'a yeni menü öğesi ekleyin

### İletişim Formu Eklemek
```html
<form class="contact-form">
    <input type="text" placeholder="Adınız" required>
    <input type="email" placeholder="E-mail" required>
    <textarea placeholder="Mesajınız" required></textarea>
    <button type="submit">Gönder</button>
</form>
```

## 📈 SEO İçin Öneriler

1. **Meta Tags Ekleyin:**
```html
<meta name="description" content="Web sitenizin açıklaması">
<meta name="keywords" content="anahtar, kelimeler">
<meta property="og:title" content="Sayfa Başlığı">
<meta property="og:description" content="Açıklama">
```

2. **Google Analytics:**
```html
<!-- Head bölümüne ekleyin -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
```

## 🐛 Sorun Giderme

### CSS Yüklenmiyorsa:
- Dosya yollarını kontrol edin
- Browser cache'i temizleyin
- `F12` ile Developer Tools'da hataları kontrol edin

### JavaScript Çalışmıyorsa:
- Console'da (F12) hata mesajlarını kontrol edin
- Script dosyasının yüklendiğinden emin olun

## 📞 Destek

- Hostinger Support: [support.hostinger.com](https://support.hostinger.com)
- Web Development: Geliştirici ile iletişime geçin

## 🎉 Tamamlandı!

Web siteniz artık hazır! Ana sayfada "Merhaba" mesajı ile ziyaretçilerinizi karşılayabilirsiniz. 

**Sonraki Adımlar:**
1. Domain adınızı bağlayın
2. SSL sertifikasını aktive edin
3. İçeriğinizi özelleştirin
4. SEO optimizasyonu yapın

**İyi kodlamalar! 🚀**