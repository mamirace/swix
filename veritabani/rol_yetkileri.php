<?php
// 🔐 Swixx Dashboard - Rol Yetkileri Yönetimi
require_once __DIR__ . '/sql_baglantisi.php';

/**
 * Belirli rol için tüm yetkileri getir
 */
function rol_yetkileri_getir($rol_id) {
    $sorgu = "SELECT sayfa, okuma, yazma, duzenleme, silme FROM yetkiler WHERE rol_id = ? ORDER BY sayfa";
    $sonuc = vt_sorgu($sorgu, [$rol_id]);
    
    if (!$sonuc) return [];
    
    $yetkiler = [];
    foreach ($sonuc as $satir) {
        $yetkiler[$satir[0]] = [
            'okuma' => (bool)$satir[1],
            'yazma' => (bool)$satir[2],
            'duzenleme' => (bool)$satir[3],
            'silme' => (bool)$satir[4]
        ];
    }
    return $yetkiler;
}

/**
 * Rol yetkisini güncelle
 */
function rol_yetkisi_guncelle($rol_id, $sayfa, $yetkiler) {
    $sorgu = "INSERT INTO yetkiler (rol_id, sayfa, okuma, yazma, duzenleme, silme)
              VALUES (?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE okuma = VALUES(okuma), yazma = VALUES(yazma), 
              duzenleme = VALUES(duzenleme), silme = VALUES(silme)";
    
    $params = [
        $rol_id, 
        $sayfa,
        isset($yetkiler['okuma']) && $yetkiler['okuma'] ? 1 : 0,
        isset($yetkiler['yazma']) && $yetkiler['yazma'] ? 1 : 0,
        isset($yetkiler['duzenleme']) && $yetkiler['duzenleme'] ? 1 : 0,
        isset($yetkiler['silme']) && $yetkiler['silme'] ? 1 : 0
    ];
    
    return vt_guncelle($sorgu, $params) >= 0;
}

/**
 * Rol için tüm yetkileri toplu güncelle
 */
function rol_yetkilerini_toplu_guncelle($rol_id, $tum_yetkiler) {
    foreach ($tum_yetkiler as $sayfa => $yetkiler) {
        rol_yetkisi_guncelle($rol_id, $sayfa, $yetkiler);
    }
    return true;
}

/**
 * Yetki kontrol
 */
function yetki_kontrol($rol_id, $sayfa, $yetki_turu = 'okuma') {
    $izinli_turleri = ['okuma', 'yazma', 'duzenleme', 'silme'];
    if (!in_array($yetki_turu, $izinli_turleri)) return false;
    
    $sorgu = "SELECT $yetki_turu FROM yetkiler WHERE rol_id = ? AND sayfa = ?";
    $sonuc = vt_sorgu($sorgu, [$rol_id, $sayfa]);
    
    return ($sonuc && count($sonuc) > 0) ? (bool)$sonuc[0][0] : false;
}
?>
