/**
 * Roller Sayfası - Yetki Yönetimi
 * Modal açıldığında SQL'den verileri çekip checkbox'ları doldur
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let aktifRolId = null;
  let aktifRolAdi = null;

  // Edit Role butonlarına tıklandığında
  document.querySelectorAll('.role-edit-modal').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Rol ID'sini al
      aktifRolId = parseInt(this.getAttribute('data-role-id'));
      aktifRolAdi = this.closest('.role-heading').querySelector('h5').textContent;
      
      console.log('Rol düzenleme açıldı:', aktifRolId, aktifRolAdi);
      
      // Modal başlığını güncelle
      document.querySelector('.role-title').textContent = aktifRolAdi + ' Rolünü Düzenle';
      
      // Rol adı input'unu doldur
      document.getElementById('modalRoleName').value = aktifRolAdi;
      
      // Yetkileri API'den çek ve doldur
      yukleYetkileri(aktifRolId);
    });
  });
  
  /**
   * API'den yetkileri çek ve tabloyu dinamik oluştur
   */
  async function yukleYetkileri(rolId) {
    try {
      const response = await fetch(`/api/rol-yetkileri?rol_id=${rolId}`);
      const data = await response.json();
      
      if (!data.basarili) {
        console.error('Yetkiler yüklenemedi:', data);
        return;
      }
      
      console.log('Yetkiler yüklendi:', data);
      
      // Tabloyu dinamik oluştur
      const tbody = document.querySelector('#addRoleForm table tbody');
      if (!tbody) {
        console.error('Tablo tbody bulunamadı');
        return;
      }
      
      // Tüm satırları temizle
      tbody.innerHTML = '';
      
      // İlk olarak "Select All" satırını oluştur
      const selectAllTr = document.createElement('tr');
      selectAllTr.innerHTML = `
        <td class="text-nowrap fw-medium">
          Yönetici Erişimi
          <i class="icon-base ti tabler-info-circle icon-xs" data-bs-toggle="tooltip" data-bs-placement="top" title="Sisteme tam erişim sağlar"></i>
        </td>
        <td>
          <div class="d-flex justify-content-end">
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="selectAll" />
              <label class="form-check-label" for="selectAll"> Tümünü Seç </label>
            </div>
          </div>
        </td>
      `;
      tbody.appendChild(selectAllTr);
      
      // Sayfa-input eşleştirmesi
      const sayfaMapping = {
        'user': 'user'
      };
      
      // Her sayfa için satır oluştur
      data.yetkiler.forEach(yetki => {
        const prefix = sayfaMapping[yetki.sayfa] || yetki.sayfa;
        console.log('Satır oluşturuluyor:', yetki.sayfa, prefix);
        
        const tr = document.createElement('tr');
        
        // Sayfa adı sütunu
        const tdSayfa = document.createElement('td');
        tdSayfa.className = 'text-nowrap fw-medium text-heading';
        tdSayfa.textContent = yetki.sayfa_adi;
        
        // Yetkiler sütunu
        const tdYetkiler = document.createElement('td');
        const divYetkiler = document.createElement('div');
        divYetkiler.className = 'd-flex justify-content-end';
        
        // Okuma checkbox
        const divOkuma = document.createElement('div');
        divOkuma.className = 'form-check mb-0 me-4 me-lg-12';
        divOkuma.innerHTML = `
          <input class="form-check-input" type="checkbox" id="${prefix}Read" ${yetki.okuma ? 'checked' : ''} />
          <label class="form-check-label" for="${prefix}Read"> Okuma </label>
        `;
        
        // Yazma checkbox
        const divYazma = document.createElement('div');
        divYazma.className = 'form-check mb-0 me-4 me-lg-12';
        divYazma.innerHTML = `
          <input class="form-check-input" type="checkbox" id="${prefix}Write" ${yetki.yazma ? 'checked' : ''} />
          <label class="form-check-label" for="${prefix}Write"> Yazma </label>
        `;
        
        // Düzenleme checkbox
        const divDuzenleme = document.createElement('div');
        divDuzenleme.className = 'form-check mb-0 me-4 me-lg-12';
        divDuzenleme.innerHTML = `
          <input class="form-check-input" type="checkbox" id="${prefix}Update" ${yetki.duzenleme ? 'checked' : ''} />
          <label class="form-check-label" for="${prefix}Update"> Düzenleme </label>
        `;
        
        // Silme checkbox
        const divSilme = document.createElement('div');
        divSilme.className = 'form-check mb-0';
        divSilme.innerHTML = `
          <input class="form-check-input" type="checkbox" id="${prefix}Delete" ${yetki.silme ? 'checked' : ''} />
          <label class="form-check-label" for="${prefix}Delete"> Silme </label>
        `;
        
        divYetkiler.appendChild(divOkuma);
        divYetkiler.appendChild(divYazma);
        divYetkiler.appendChild(divDuzenleme);
        divYetkiler.appendChild(divSilme);
        
        tdYetkiler.appendChild(divYetkiler);
        
        tr.appendChild(tdSayfa);
        tr.appendChild(tdYetkiler);
        tbody.appendChild(tr);
      });
      
      // Select All durumunu kontrol et
      kontrolSelectAll();
      
    } catch (error) {
      console.error('Yetkiler yüklenirken hata:', error);
      alert('Yetkiler yüklenirken bir hata oluştu!');
    }
  }
  
  /**
   * Form submit - Yetkileri kaydet
   */
  document.addEventListener('click', async function(e) {
    // Gönder butonuna tıklandığında
    if (e.target.closest('#addRoleForm button[type="submit"]')) {
      e.preventDefault();
      
      console.log('Form submit başladı');
      console.log('Aktif rol ID:', aktifRolId);
      
      if (!aktifRolId) {
        alert('Rol seçili değil!');
        return;
      }
      
      // Form verilerini topla
      const sayfaMapping = {
        'user': 'user'
      };
      
      const yetkiler = {};
      
      Object.entries(sayfaMapping).forEach(([prefix, sayfaAdi]) => {
        const readCheckbox = document.getElementById(prefix + 'Read');
        const writeCheckbox = document.getElementById(prefix + 'Write');
        const updateCheckbox = document.getElementById(prefix + 'Update');
        const deleteCheckbox = document.getElementById(prefix + 'Delete');
        
        console.log('Checkbox bulundu mu?', {
          read: !!readCheckbox,
          write: !!writeCheckbox,
          update: !!updateCheckbox,
          delete: !!deleteCheckbox
        });
        
        yetkiler[sayfaAdi] = {
          okuma: readCheckbox?.checked || false,
          yazma: writeCheckbox?.checked || false,
          duzenleme: updateCheckbox?.checked || false,
          silme: deleteCheckbox?.checked || false
        };
      });
      
      console.log('Kaydedilecek yetkiler:', yetkiler);
      
      try {
        const response = await fetch('/api/rol-yetkileri', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            rol_id: aktifRolId,
            yetkiler: yetkiler
          })
        });
        
        const data = await response.json();
        
        if (data.basarili) {
          // Modal'ı kapat
          const modal = bootstrap.Modal.getInstance(document.getElementById('addRoleModal'));
          if (modal) modal.hide();
        } else {
          console.error('Yetkiler kaydedilemedi:', data.mesaj || 'Bilinmeyen hata');
        }
        
      } catch (error) {
        console.error('Yetkiler kaydedilirken hata:', error);
      }
    }
  });
  
  /**
   * Select All checkbox işlevi - Event delegation kullan
   */
  document.querySelector('#addRoleForm table tbody').addEventListener('change', function(e) {
    if (e.target.id === 'selectAll') {
      const checked = e.target.checked;
      document.querySelectorAll('#addRoleForm input[type="checkbox"]').forEach(cb => {
        if (cb.id !== 'selectAll') {
          cb.checked = checked;
        }
      });
    } else if (e.target.type === 'checkbox' && e.target.id !== 'selectAll') {
      kontrolSelectAll();
    }
  });
  
  /**
   * Tüm checkbox'lar işaretliyse Select All'ı işaretle
   */
  function kontrolSelectAll() {
    const tumCheckboxlar = Array.from(document.querySelectorAll('#addRoleForm input[type="checkbox"]'))
      .filter(cb => cb.id !== 'selectAll');
    
    const hepsiIsaretli = tumCheckboxlar.length > 0 && tumCheckboxlar.every(cb => cb.checked);
    
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
      selectAllCheckbox.checked = hepsiIsaretli;
    }
  }
});
