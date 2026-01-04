# 🚀 Swix Dashboard - Python Flask Ana Sunucusu
# JavaScript/Node.js'den Python Flask'a çevrilmiş modern CRM dashboard

from flask import Flask, render_template, send_file, request, redirect, url_for, jsonify, send_from_directory, session, flash
from datetime import datetime
from functools import wraps
import os
import sys
import psutil
from pathlib import Path
from jinja2 import Environment, FileSystemLoader

# Proje kök dizinini bul
proje_kok = Path(__file__).parent.parent
sys.path.append(str(proje_kok))

from yapılandırma.ayarlar import ayarlar_yukle
from genel.yardımcılar import tarih_formatlama, api_yaniti, log_formati
from veritabani.sql_baglantisi import vt_test
from veritabani.kullanicilar import login_kontrol

# Flask uygulaması oluştur
app = Flask(__name__)
app.secret_key = 'swix-dashboard-secret-2026'
app.template_folder = str(proje_kok / 'sayfalar')
app.static_folder = str(proje_kok / 'varlıklar')

# Session ayarları - Development için (7 gün persistent session)
from datetime import timedelta
app.config['SESSION_COOKIE_SECURE'] = False  # HTTP için (HTTPS'de True yap)
app.config['SESSION_COOKIE_HTTPONLY'] = True  # JavaScript'ten erişilemesin
app.config['SESSION_COOKIE_SAMESITE'] = 'Lax'  # CSRF koruması
app.config['PERMANENT_SESSION_LIFETIME'] = timedelta(days=7)

# Ayarları yükle
ayarlar = ayarlar_yukle()
PORT = ayarlar['sunucu_ayarlari']['port']

# Her request'te session'ı persistent yap
@app.before_request
def make_session_permanent():
    session.permanent = True

# Login decorator - korumalı sayfalar için
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'kullanici_id' not in session:
            return redirect('/giris')
        return f(*args, **kwargs)
    return decorated_function

print(log_formati('INFO', f"🚀 {ayarlar['uygulama_ayarlari']['ad']} başlatılıyor..."))

# Veritabanı bağlantı testi
print(log_formati('INFO', 'MySQL veritabanı bağlantısı kontrol ediliyor...'))
success, message = vt_test()
if success:
    print(log_formati('SUCCESS', f'✅ {message}'))
else:
    print(log_formati('ERROR', f'❌ Veritabanı hatası: {message}'))

# Statik dosya servisi - varlıklar klasörü (Vuexy tema dosyaları)
@app.route('/varlıklar/<path:filename>')
def varlıklar_servis(filename):
    return send_from_directory(proje_kok / 'varlıklar', filename)

# Assets alias (geriye uyumluluk için)
@app.route('/assets/<path:filename>')
def assets_servis(filename):
    return send_from_directory(proje_kok / 'varlıklar', filename)

# Ana sayfa route - direkt giriş sayfası veya dashboard
@app.route('/')
def ana_sayfa():
    # Eğer session'da kullanıcı varsa, dashboard'a git
    if 'kullanici_id' in session:
        return redirect('/dashboard')
    # Yoksa giriş sayfasına git
    return send_file(proje_kok / 'sayfalar' / 'giris.html')

# Giriş sayfası route - Vuexy login
@app.route('/giris')
def giris():
    return send_file(proje_kok / 'sayfalar' / 'giris.html')

# Login alias (geriye uyumluluk için)
@app.route('/login')
def login():
    return send_file(proje_kok / 'sayfalar' / 'giris.html')

# Dashboard (ana sayfa) route - GET ve POST handler
@app.route('/dashboard', methods=['GET', 'POST'])
def dashboard():
    # POST isteği = login denemesi
    if request.method == 'POST':
        email_username = request.form.get('email-username')
        password = request.form.get('password')
        
        print(log_formati('INFO', f'Giriş denemesi: {email_username}'))
        
        # Veritabanından kontrol et
        kullanici = login_kontrol(email_username, password)
        
        if kullanici:
            print(log_formati('SUCCESS', f'Başarılı giriş: {kullanici[1]} ({kullanici[5]})'))
            # Session'a kullanıcı bilgilerini kaydet
            session['kullanici_id'] = kullanici[0]
            session['kullanici_adi'] = kullanici[1]
            session['isim'] = kullanici[2]
            session['soyisim'] = kullanici[3]
            session['mail'] = kullanici[4]
            session['role'] = kullanici[5]
            session['firma'] = kullanici[6]
            session['organization'] = kullanici[7]
            return redirect('/dashboard?login=success')
        else:
            print(log_formati('WARN', f'Başarısız giriş denemesi: {email_username}'))
            return redirect('/giris?error=invalid_credentials')
    
    # GET isteği = dashboard sayfasını göster (LOGIN ZORUNLU)
    if 'kullanici_id' not in session:
        print(log_formati('WARN', 'Yetkisiz dashboard erişim denemesi - giriş sayfasına yönlendir'))
        return redirect('/giris')
    
    return send_file(proje_kok / 'sayfalar' / 'dashboard.html')

# Ana sayfa alias
@app.route('/anasayfa')
@login_required
def anasayfa():
    return send_file(proje_kok / 'sayfalar' / 'dashboard.html')

# Şifremi unuttum sayfası route - Türkçe
@app.route('/sifremi-unuttum')
def sifremi_unuttum():
    return send_file(proje_kok / 'sayfalar' / 'sifremi-unuttum.html')

# Forgot password alias (geriye uyumluluk için)
@app.route('/forgot-password')
def forgot_password():
    return send_file(proje_kok / 'sayfalar' / 'sifremi-unuttum.html')

# Şifre sıfırlama POST handler
@app.route('/reset-password', methods=['POST'])
def reset_password():
    email = request.form.get('email')
    
    print(log_formati('INFO', f'Şifre sıfırlama talebi: {email}'))
    
    if email:
        # Burada normalde e-posta gönderme işlemi olur
        print(log_formati('INFO', f'Şifre sıfırlama e-postası gönderildi: {email}'))
        return redirect('/sifremi-unuttum?success=true')
    else:
        print(log_formati('WARN', 'Geçersiz şifre sıfırlama talebi'))
        return redirect('/sifremi-unuttum?error=invalid_email')

# Register sayfası route (gelecekte eklenebilir)
@app.route('/register')
def register():
    return jsonify({
        'message': 'Register sayfası henüz hazırlanmadı',
        'redirect': '/login'
    })

# Logout route
@app.route('/logout')
def logout():
    kullanici_adi = session.get('kullanici_adi', 'bilinmeyen')
    print(log_formati('INFO', f'Kullanıcı çıkış yaptı: {kullanici_adi}'))
    session.clear()
    return redirect('/giris')

# Profil sayfası
@app.route('/profile')
@login_required
def profile():
    return render_template('profil.html', 
        user={
            'kullanici_id': session.get('kullanici_id'),
            'kullanici_adi': session.get('kullanici_adi'),
            'isim': session.get('isim'),
            'soyisim': session.get('soyisim'),
            'mail': session.get('mail'),
            'role': session.get('role'),
            'firma': session.get('firma'),
            'organization': session.get('organization')
        }
    )

# Helper - Kullanıcı verisini template'e gönder
def get_user_data(**kwargs):
    """Session'dan kullanıcı verisini al ve template parametreleri oluştur"""
    return {
        'user': {
            'kullanici_id': session.get('kullanici_id'),
            'kullanici_adi': session.get('kullanici_adi'),
            'isim': session.get('isim'),
            'soyisim': session.get('soyisim'),
            'mail': session.get('mail'),
            'role': session.get('role'),
            'firma': session.get('firma'),
            'organization': session.get('organization')
        },
        **kwargs
    }

# Ayarlar sayfası
@app.route('/settings')
@login_required
def settings():
    return render_template('profil-ayarlari.html', **get_user_data())

# Kullanıcılar sayfası
@app.route('/user')
@login_required
def users():
    return render_template('kullanicilar.html', **get_user_data())

# Roller sayfası
@app.route('/role')
@login_required
def roles():
    return render_template('roller.html', **get_user_data())

# API - Tüm kullanıcıları listele
@app.route('/api/kullanicilar', methods=['GET'])
@login_required
def api_kullanicilar():
    """Veritabanından tüm kullanıcıları döner"""
    try:
        from veritabani.sql_baglantisi import vt_sorgu
        
        sorgu = """
        SELECT id, kullanici_adi, isim, soyisim, mail, role, organization, active
        FROM Kullanicilar
        ORDER BY isim ASC
        """
        
        sonuc = vt_sorgu(sorgu)
        
        print(log_formati('INFO', f'Kullanıcılar API - Sorgu sonucu: {len(sonuc) if sonuc else 0} kayıt bulundu'))
        
        if sonuc:
            kullanicilar = []
            for i, row in enumerate(sonuc, 1):
                status_map = {0: 'Inactive', 1: 'Active'}
                # Avatar - güvenli baş harfleri al
                isim = str(row[2] or '').strip()
                soyisim = str(row[3] or '').strip()
                avatar = ''
                if isim and soyisim:
                    avatar = (isim[0] + soyisim[0]).upper()
                elif isim:
                    avatar = isim[0].upper()
                else:
                    avatar = '?'
                
                kullanicilar.append({
                    'id': row[0],
                    'full_name': f"{isim} {soyisim}".strip(),
                    'email': row[4],
                    'username': row[1],
                    'role': row[5],
                    'organization': row[6],
                    'status': status_map.get(row[7], 'Inactive'),
                    'avatar': avatar
                })
            
            print(log_formati('SUCCESS', f'Kullanıcılar API - {len(kullanicilar)} kullanıcı hazırlandı'))
            return jsonify({'data': kullanicilar})
        
        print(log_formati('WARN', 'Kullanıcılar API - Veritabanında kayıt bulunamadı'))
        return jsonify({'data': []})
    
    except Exception as e:
        print(log_formati('ERROR', f'Kullanıcılar API hatası: {str(e)}'))
        return jsonify({'error': str(e)}), 500

# API - Kullanıcı durumunu değiştir (Active/Inactive)
@app.route('/api/kullanicilar/<int:kullanici_id>/toggle-status', methods=['POST'])
@login_required
def api_toggle_status(kullanici_id):
    """Kullanıcı aktif/pasif durumunu değiştir"""
    try:
        from veritabani.sql_baglantisi import vt_sorgu, vt_guncelle
        
        # Mevcut durumu öğren
        sorgu_check = "SELECT active FROM Kullanicilar WHERE id = %s"
        result = vt_sorgu(sorgu_check, (kullanici_id,))
        
        if not result:
            return jsonify({'error': 'Kullanıcı bulunamadı'}), 404
        
        mevcut_durum = result[0][0]
        yeni_durum = 0 if mevcut_durum == 1 else 1  # Toggle
        
        # Durumu güncelle
        sorgu_update = "UPDATE Kullanicilar SET active = %s WHERE id = %s"
        vt_guncelle(sorgu_update, (yeni_durum, kullanici_id))
        
        status_text = 'Active' if yeni_durum == 1 else 'Inactive'
        print(log_formati('SUCCESS', f'Kullanıcı {kullanici_id} durumu {status_text} olarak değiştirildi'))
        
        return jsonify({'status': status_text, 'active': yeni_durum})
    
    except Exception as e:
        print(log_formati('ERROR', f'Status değiştirme hatası: {str(e)}'))
        return jsonify({'error': str(e)}), 500

# API - Yeni kullanıcı ekle
@app.route('/api/kullanicilar/ekle', methods=['POST'])
@login_required
def api_kullanici_ekle():
    """Veritabanına yeni kullanıcı ekle"""
    try:
        from veritabani.sql_baglantisi import vt_guncelle
        from hashlib import sha256
        
        print("\n🔵 ========== API İsteği Başladı: /api/kullanicilar/ekle ==========")
        
        data = request.get_json()
        print(f"📥 Alınan JSON veriler: {data}")
        
        # Gerekli alanları kontrol et
        gerekli = ['isim', 'soyisim', 'mail', 'kullanici_adi', 'sifre', 'role', 'organization']
        eksik = [alan for alan in gerekli if not data.get(alan)]
        
        if eksik:
            print(f"❌ Eksik alanlar: {eksik}")
            return jsonify({'error': f'Eksik alanlar: {", ".join(eksik)}'}), 400
        
        isim = data.get('isim', '').strip()
        soyisim = data.get('soyisim', '').strip()
        mail = data.get('mail', '').strip()
        kullanici_adi = data.get('kullanici_adi', '').strip()
        role = data.get('role', 'User').strip()
        organization = data.get('organization', '').strip()
        sifre = data.get('sifre', '').strip()
        firma = data.get('firma', '').strip()  # Firma alanı
        
        print(f"✏️ Temizlenen veriler: isim={isim}, soyisim={soyisim}, mail={mail}, kullanici_adi={kullanici_adi}")
        
        # Şifreyi hash'le
        sifre_hash = sha256(sifre.encode()).hexdigest()
        print(f"🔐 Şifre hash'lenmiş: {sifre_hash[:10]}...")
        
        # Sorgu
        sorgu = """
        INSERT INTO Kullanicilar (kullanici_adi, isim, soyisim, mail, sifre, role, organization, firma, active)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 1)
        """
        
        params = (kullanici_adi, isim, soyisim, mail, sifre_hash, role, organization, firma)
        print(f"🔄 Çalıştırılacak sorgu parametreleri: {params}")
        
        result = vt_guncelle(sorgu, params)
        print(f"✅ vt_guncelle sonucu: {result} (etkilenen satır)")
        
        if result > 0:
            print(log_formati('SUCCESS', f'Yeni kullanıcı eklendi: {kullanici_adi}'))
            print("🟢 ========== İşlem Başarılı ==========\n")
            return jsonify({'message': 'Kullanıcı başarıyla eklendi', 'status': 'success'}), 201
        else:
            print(log_formati('ERROR', 'Kullanıcı eklenirken hata oluştu'))
            print("🔴 ========== İşlem Başarısız ==========\n")
            return jsonify({'error': 'Kullanıcı eklenirken hata oluştu', 'status': 'error'}), 400
    
    except Exception as e:
        print(f"❌ Hata: {str(e)}")
        print(log_formati('ERROR', f'Kullanıcı ekleme hatası: {str(e)}'))
        print("🔴 ========== İşlem Hata İle Sona Erdi ==========\n")
        return jsonify({'error': str(e), 'status': 'error'}), 400

@app.route('/api/kullanicilar/sil/<int:kullanici_id>', methods=['DELETE'])
@login_required
def api_kullanici_sil(kullanici_id):
    """Veritabanından kullanıcı sil"""
    try:
        from veritabani.sql_baglantisi import vt_guncelle
        
        print("\n🔵 ========== DELETE İsteği: /api/kullanicilar/sil ==========")
        print(f"📥 Silinecek Kullanıcı ID: {kullanici_id}")
        
        # Sorgu
        sorgu = "DELETE FROM Kullanicilar WHERE id = %s"
        params = (kullanici_id,)
        
        print(f"🔄 Çalıştırılacak sorgu: {sorgu}")
        print(f"📋 Parametreler: {params}")
        
        result = vt_guncelle(sorgu, params)
        print(f"✅ Silme sonucu: {result} (etkilenen satır)")
        
        if result > 0:
            print(log_formati('SUCCESS', f'Kullanıcı silindi: ID={kullanici_id}'))
            print("🟢 ========== Silme Başarılı ==========\n")
            return jsonify({'message': 'Kullanıcı başarıyla silindi', 'status': 'success'}), 200
        else:
            print(log_formati('ERROR', f'Kullanıcı bulunamadı: ID={kullanici_id}'))
            print("🔴 ========== Silme Başarısız ==========\n")
            return jsonify({'error': 'Kullanıcı bulunamadı', 'status': 'error'}), 404
    
    except Exception as e:
        print(f"❌ Hata: {str(e)}")
        print(log_formati('ERROR', f'Kullanıcı silme hatası: {str(e)}'))
        print("🔴 ========== Silme Hata İle Sona Erdi ==========\n")
        return jsonify({'error': str(e), 'status': 'error'}), 400

@app.route('/api/kullanicilar/guncelle/<int:kullanici_id>', methods=['PUT'])
@login_required
def api_kullanici_guncelle(kullanici_id):
    """Veritabanında kullanıcı bilgilerini güncelle"""
    try:
        from veritabani.sql_baglantisi import vt_guncelle
        
        print("\n🔵 ========== PUT İsteği: /api/kullanicilar/guncelle ==========")
        
        data = request.get_json()
        print(f"📥 Güncellenecek Kullanıcı ID: {kullanici_id}")
        print(f"📥 Alınan veriler: {data}")
        
        isim = data.get('isim', '').strip()
        soyisim = data.get('soyisim', '').strip()
        email = data.get('email', '').strip()
        username = data.get('username', '').strip()
        role = data.get('role', '').strip()
        organization = data.get('organization', '').strip()
        
        # Gerekli alanları kontrol et
        gerekli = {'isim': isim, 'soyisim': soyisim, 'email': email, 'username': username, 'role': role, 'organization': organization}
        eksik = [alan for alan, deger in gerekli.items() if not deger]
        
        if eksik:
            print(f"❌ Eksik alanlar: {eksik}")
            return jsonify({'error': f'Eksik alanlar: {", ".join(eksik)}', 'status': 'error'}), 400
        
        # Sorgu
        sorgu = """
        UPDATE Kullanicilar 
        SET isim = %s, soyisim = %s, mail = %s, kullanici_adi = %s, role = %s, organization = %s
        WHERE id = %s
        """
        
        params = (isim, soyisim, email, username, role, organization, kullanici_id)
        print(f"🔄 Çalıştırılacak sorgu parametreleri: {params}")
        
        result = vt_guncelle(sorgu, params)
        print(f"✅ vt_guncelle sonucu: {result} (etkilenen satır)")
        
        if result > 0:
            print(log_formati('SUCCESS', f'Kullanıcı güncellendi: ID={kullanici_id}'))
            print("🟢 ========== Güncelleme Başarılı ==========\n")
            return jsonify({'message': 'Kullanıcı başarıyla güncellendi', 'status': 'success'}), 200
        else:
            print(log_formati('ERROR', f'Kullanıcı bulunamadı: ID={kullanici_id}'))
            print("🔴 ========== Güncelleme Başarısız ==========\n")
            return jsonify({'error': 'Kullanıcı bulunamadı', 'status': 'error'}), 404
    
    except Exception as e:
        print(f"❌ Hata: {str(e)}")
        print(log_formati('ERROR', f'Kullanıcı güncelleme hatası: {str(e)}'))
        print("🔴 ========== Güncelleme Hata İle Sona Erdi ==========\n")
        return jsonify({'error': str(e), 'status': 'error'}), 400

# Ayarları güncelle - form submit
@app.route('/ayarlar-guncelle', methods=['POST'])
@login_required
def ayarlar_guncelle():
    """Kullanıcı ayarlarını güncelle"""
    try:
        from veritabani.sql_baglantisi import vt_guncelle
        
        # Form verilerini al
        isim = request.form.get('firstName', '').strip()
        soyisim = request.form.get('lastName', '').strip()
        mail = request.form.get('email', '').strip()
        organization = request.form.get('organization', '').strip()
        kullanici_id = session.get('kullanici_id')
        
        # Validasyon
        if not all([isim, soyisim, mail]):
            flash('İsim, soyisim ve e-mail alanları zorunludur!', 'error')
            return redirect(url_for('settings'))
        
        # SQL güncelleme
        sorgu = "UPDATE Kullanicilar SET isim = %s, soyisim = %s, mail = %s, organization = %s WHERE id = %s"
        etkilenen = vt_guncelle(sorgu, (isim, soyisim, mail, organization, kullanici_id))
        
        if etkilenen > 0:
            # Session'ı güncelle
            session.update({
                'isim': isim,
                'soyisim': soyisim,
                'mail': mail,
                'organization': organization
            })
            print(log_formati('SUCCESS', f'✅ Ayarlar güncellendi: {kullanici_id}'))
            flash('Ayarlarınız başarıyla kaydedildi!', 'success')
            return redirect(url_for('settings'))
        else:
            flash('Güncelleme başarısız oldu!', 'error')
            return redirect(url_for('settings'))
            
    except Exception as e:
        print(log_formati('ERROR', f'Ayarlar güncelleme hatası: {str(e)}'))
        flash(f'Sunucu hatası: {str(e)}', 'error')
        return redirect(url_for('settings'))

# Şifre değiştir
@app.route('/ubah-sifre', methods=['POST'])
@login_required
def ubah_sifre():
    """Kullanıcı şifresini değiştir"""
    try:
        from veritabani.sql_baglantisi import vt_sorgu, vt_guncelle
        from hashlib import sha256
        
        # Form verilerini al
        sifre_eski = request.form.get('currentPassword', '').strip()
        sifre_yeni = request.form.get('newPassword', '').strip()
        sifre_dogrula = request.form.get('confirmPassword', '').strip()
        kullanici_id = session.get('kullanici_id')
        
        # Validasyon
        if not all([sifre_eski, sifre_yeni, sifre_dogrula]):
            flash('Tüm alanlar zorunludur!', 'error')
            return redirect(url_for('settings'))
        
        if sifre_yeni != sifre_dogrula:
            flash('Yeni şifreler eşleşmiyor!', 'error')
            return redirect(url_for('settings'))
        
        if len(sifre_yeni) < 6:
            flash('Şifre en az 6 karakter olmalıdır!', 'error')
            return redirect(url_for('settings'))
        
        # Eski şifreyi kontrol et
        sorgu = "SELECT sifre FROM Kullanicilar WHERE id = %s"
        sonuc = vt_sorgu(sorgu, (kullanici_id,))
        
        if not sonuc or sonuc[0][0] != sifre_eski:
            flash('Mevcut şifre yanlış!', 'error')
            return redirect(url_for('settings'))
        
        # Yeni şifreyi hash et ve kaydet
        sifre_hash = sha256(sifre_yeni.encode()).hexdigest()
        sorgu_guncelle = "UPDATE Kullanicilar SET sifre = %s WHERE id = %s"
        etkilenen = vt_guncelle(sorgu_guncelle, (sifre_hash, kullanici_id))
        
        if etkilenen > 0:
            print(log_formati('SUCCESS', f'✅ Şifre değiştirildi: {kullanici_id}'))
            flash('Şifreniz başarıyla değiştirildi!', 'success')
            return redirect(url_for('settings'))
        else:
            flash('Şifre değişimi başarısız oldu!', 'error')
            return redirect(url_for('settings'))
            
    except Exception as e:
        print(log_formati('ERROR', f'Şifre değişimi hatası: {str(e)}'))
        flash(f'Sunucu hatası: {str(e)}', 'error')
        return redirect(url_for('settings'))



# API endpoint - Sağlık kontrolü
@app.route('/api/saglik')
def api_saglik():
    import time
    start_time = getattr(app, 'start_time', time.time())
    uptime = time.time() - start_time
    
    memory_info = psutil.virtual_memory()
    
    return jsonify(api_yaniti(True, {
        'durum': 'ÇALIŞIYOR',
        'sunucu': ayarlar['uygulama_ayarlari']['ad'],
        'versiyon': ayarlar['uygulama_ayarlari']['versiyon'],
        'çalışma_süresi': uptime,
        'bellek': {
            'toplam': memory_info.total,
            'kullanılan': memory_info.used,
            'müsait': memory_info.available,
            'yüzde': memory_info.percent
        },
        'ortam': ayarlar['sunucu_ayarlari']['ortam']
    }, 'Sunucu başarıyla çalışıyor! 🐍'))

# API endpoint - Health alias (geriye uyumluluk)
@app.route('/api/health')
def api_health():
    return jsonify({
        'status': 'OK',
        'message': 'Swix Dashboard Python Flask sunucusu çalışıyor! 🐍',
        'timestamp': datetime.now().isoformat(),
        'version': ayarlar['uygulama_ayarlari']['versiyon']
    })

# API endpoint - Proje bilgileri
@app.route('/api/bilgi')
def api_bilgi():
    return jsonify(api_yaniti(True, {
        'ad': ayarlar['uygulama_ayarlari']['ad'],
        'versiyon': ayarlar['uygulama_ayarlari']['versiyon'],
        'aciklama': ayarlar['uygulama_ayarlari']['aciklama'],
        'yazar': ayarlar['uygulama_ayarlari']['yazar'],
        'teknolojiler': ['Python', 'Flask', 'Vuexy Bootstrap 5', 'Modern Backend'],
        'özellikler': ['Türkçe Dil Desteği', 'Responsive Tasarım', 'Modern UI/UX']
    }, 'Proje bilgileri başarıyla alındı'))

# API endpoint - Info alias (geriye uyumluluk)
@app.route('/api/info')
def api_info():
    return jsonify({
        'name': ayarlar['uygulama_ayarlari']['ad'],
        'description': ayarlar['uygulama_ayarlari']['aciklama'],
        'tech': ['Python', 'Flask', 'Vuexy Bootstrap 5'],
        'features': [
            'Vuexy Login Sayfası',
            'Professional UI',
            'Responsive Design',
            'Bootstrap 5',
            'Modern Authentication'
        ],
        'author': ayarlar['uygulama_ayarlari']['yazar'],
        'github': 'https://github.com/mamirace/swix'
    })

# Error handler
@app.errorhandler(404)
def not_found(error):
    # API routes'u hariç tut
    if request.path.startswith('/api/'):
        return jsonify(api_yaniti(False, None, 
            f'API endpoint bulunamadı: {request.path}', {
                'mevcut_endpoints': ['/api/saglik', '/api/bilgi', '/api/health', '/api/info']
            })), 404
    
    # Diğer sayfalar - Dashboard'a yönlendir
    print(log_formati('WARN', f'404 - Bulunamayan sayfa dashboard\'a yönlendirildi: {request.path}'))
    return redirect('/dashboard')

# Error handler - Server errors
@app.errorhandler(500)
def server_error(error):
    print(log_formati('ERROR', f'Server hatası: {str(error)}'))
    return jsonify(api_yaniti(False, None, 
        str(error) if ayarlar['sunucu_ayarlari']['ortam'] == 'development' else 'Sunucu hatası oluştu!'
    )), 500

if __name__ == '__main__':
    import time
    app.start_time = time.time()
    
    başlangıç_zamanı = tarih_formatlama()
    print(f'''
🚀 {ayarlar['uygulama_ayarlari']['ad']} sunucusu başlatıldı!
📍 Port: {PORT}
🌐 Yerel: http://localhost:{PORT}
🔐 Giriş: http://localhost:{PORT}/giris
🔗 API Sağlık: http://localhost:{PORT}/api/saglik
🔗 API Bilgi: http://localhost:{PORT}/api/bilgi
📱 Ortam: {ayarlar['sunucu_ayarlari']['ortam']}
⏰ Başlatma zamanı: {başlangıç_zamanı}

🎨 Vuexy teması entegre edildi!
🐍 Python Flask sunucusu aktif!
🗂️ Kurumsal klasör yapısı korundu!''')
    
    app.run(
        host=ayarlar['sunucu_ayarlari']['host'],
        port=PORT,
        debug=ayarlar['sunucu_ayarlari']['ortam'] == 'development'
    )