<?php
// 🗄️ Swix Dashboard - Veritabanı Modülü
// Python __init__.py dosyasının PHP karşılığı

/**
 * Swix Dashboard MySQL Veritabanı Modülü
 * 
 * Bu modül şunları içerir:
 * - MySQL bağlantı yönetimi
 * - Sorgu çalıştırma fonksiyonları
 * - Kullanicilar tablosu CRUD işlemleri
 * - Bağlantı test fonksiyonu
 * 
 * @version 1.0.0
 * @author mamirace
 */

// Temel SQL bağlantı fonksiyonları
require_once __DIR__ . '/sql_baglantisi.php';

// Kullanicilar login kontrol
require_once __DIR__ . '/kullanicilar.php';

define('VERITABANI_VERSION', '1.0.0');
define('VERITABANI_AUTHOR', 'mamirace');
