<?php 
    $conn = mysqli_connect('localhost', 'root', '', 'kds');
    if (!$conn) {
        echo "bağlantı başarısız: " . mysqli_connect_error();
    }

    // Grafik verileri için sorguları hazırla
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "kds";
    
    $connection = new mysqli($servername, $username, $password, $database);
    if($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Kategori bazlı satışlar için veri (CEO için ana kategoriler)
    $kategori_satis = "SELECT 
                        k.kategori_ad,
                        SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_satis,
                        COUNT(DISTINCT u.urun_id) as urun_sayisi,
                        SUM(s.siparis_adet) as toplam_adet
                    FROM kategori k 
                    LEFT JOIN urun u ON k.kategori_id = u.kategori_id 
                    LEFT JOIN siparis s ON u.urun_id = s.urun_id
                    WHERE s.siparis_durumu = 'teslim_edildi'
                    GROUP BY k.kategori_id, k.kategori_ad
                    ORDER BY toplam_satis DESC";
    $kategori_result = $connection->query($kategori_satis);

    // En çok satan ürünler (CEO için top performers) - FİX EDİLDİ
    $top_urunler = "SELECT 
                        u.urun_ad,
                        k.kategori_ad,
                        SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_satis,
                        SUM(s.siparis_adet) as toplam_adet,
                        COUNT(s.siparis_id) as siparis_sayisi
                    FROM urun u 
                    JOIN kategori k ON u.kategori_id = k.kategori_id 
                    JOIN siparis s ON u.urun_id = s.urun_id
                    WHERE s.siparis_durumu = 'teslim_edildi'
                    GROUP BY u.urun_id, u.urun_ad, k.kategori_ad
                    HAVING toplam_satis > 0
                    ORDER BY toplam_satis DESC
                    LIMIT 10";
    $top_urunler_result = $connection->query($top_urunler);

    // Yıllık ve aylık satış trendi için veri
    $yillik_aylik_satis = "SELECT 
                                YEAR(s.siparis_tarihi) as yil,
                                MONTH(s.siparis_tarihi) as ay,
                                SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as aylik_gelir,
                                COUNT(s.siparis_id) as aylik_siparis_sayisi
                            FROM siparis s
                            JOIN urun u ON s.urun_id = u.urun_id
                            WHERE s.siparis_durumu = 'teslim_edildi'
                            AND YEAR(s.siparis_tarihi) >= 2022
                            GROUP BY YEAR(s.siparis_tarihi), MONTH(s.siparis_tarihi)
                            ORDER BY yil, ay";
    $yillik_aylik_result = $connection->query($yillik_aylik_satis);

    // Tedarikçi bazlı performans (FİX EDİLDİ - location tablosu yerine tedarik tablosu kullanıldı)
    $firma_performans = "SELECT 
                            t.tedarik_ad as firma_ad,
                            COUNT(s.siparis_id) as siparis_sayisi,
                            SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_gelir,
                            t.il_ad,
                            k.kategori_ad,
                            COUNT(DISTINCT u.urun_id) as urun_cesidi
                         FROM tedarik t
                         JOIN siparis s ON t.tedarik_id = s.tedarik_id
                         JOIN urun u ON s.urun_id = u.urun_id
                         JOIN kategori k ON u.kategori_id = k.kategori_id
                         WHERE s.siparis_durumu = 'teslim_edildi'
                         GROUP BY t.tedarik_id, t.tedarik_ad, t.il_ad, k.kategori_ad
                         HAVING toplam_gelir > 0
                         ORDER BY toplam_gelir DESC
                         LIMIT 12";
    $firma_result = $connection->query($firma_performans);

    // Yıllık karşılaştırma (Executive summary için)
    $yillik_karsilastirma = "SELECT 
                                YEAR(s.siparis_tarihi) as yil,
                                SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as yillik_gelir,
                                COUNT(s.siparis_id) as yillik_siparis_sayisi,
                                COUNT(DISTINCT s.tedarik_id) as aktif_tedarikci
                             FROM siparis s
                             JOIN urun u ON s.urun_id = u.urun_id
                             WHERE s.siparis_durumu = 'teslim_edildi'
                             AND YEAR(s.siparis_tarihi) IN (2022, 2023, 2024, 2025)
                             GROUP BY YEAR(s.siparis_tarihi)
                             ORDER BY yil";
    $yillik_result = $connection->query($yillik_karsilastirma);

    // Dashboard KPI'ları için ek sorgular
    $dashboard_stats = [
        'total_products' => 0,
        'inventory_turnover' => 0,
        'total_suppliers' => 0,
        'total_revenue' => 0
    ];

    // Toplam ürün sayısı
    $sql = "SELECT COUNT(*) as urun FROM urun";
    $result = $connection->query($sql);
    $dashboard_stats['total_products'] = ($result->num_rows > 0) ? $result->fetch_assoc()['urun'] : 0;

    // Envanter devir oranı
    $sql = "SELECT AVG((urun_miktar/max_urun_miktar)*100) as satis_orani FROM urun WHERE max_urun_miktar > 0";
    $result = $connection->query($sql);
    $dashboard_stats['inventory_turnover'] = ($result->num_rows > 0) ? round($result->fetch_assoc()['satis_orani'], 1) : 0;

    // Toplam tedarikçi sayısı
    $sql = "SELECT COUNT(*) as tedarik_id FROM tedarik";
    $result = $connection->query($sql);
    $dashboard_stats['total_suppliers'] = ($result->num_rows > 0) ? $result->fetch_assoc()['tedarik_id'] : 0;

    // Bu yılki toplam gelir
    $sql = "SELECT SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_gelir 
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE YEAR(s.siparis_tarihi) = YEAR(CURDATE()) 
            AND s.siparis_durumu = 'teslim_edildi'";
    $result = $connection->query($sql);
    $dashboard_stats['total_revenue'] = ($result->num_rows > 0) ? $result->fetch_assoc()['toplam_gelir'] : 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Swiss Hotel Tedarik Zinciri | Executive Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- AdminLTE Theme -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  
<style>
/* Professional Executive Dashboard Styles - Enhanced */
:root {
  --executive-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --executive-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --executive-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  --executive-warning: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  --executive-dark: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  --card-bg: rgba(255, 255, 255, 0.98);
  --shadow-primary: 0 15px 35px rgba(0,0,0,0.1);
  --shadow-hover: 0 20px 40px rgba(0,0,0,0.15);
  --border-radius: 15px;
}

body {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: 'Source Sans Pro', sans-serif;
  color: #2c3e50;
}

/* Enhanced Chart Containers - EŞİT BOYUTLAR */
.executive-chart-card {
  background: var(--card-bg);
  border-radius: var(--border-radius);
  padding: 25px;
  box-shadow: var(--shadow-primary);
  margin-bottom: 25px;
  position: relative;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.3);
  height: 540px; /* SABİT YÜKSEKLİK - daha yüksek */
  display: flex;
  flex-direction: column;
}

.executive-chart-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--executive-primary);
  border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.executive-chart-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-hover);
}

.executive-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  position: relative;
  flex-shrink: 0;
}

.executive-title::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 60px;
  height: 3px;
  background: var(--executive-primary);
  border-radius: 2px;
}

.executive-title i {
  margin-right: 12px;
  padding: 12px;
  background: var(--executive-primary);
  color: white;
  border-radius: 10px;
  font-size: 16px;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.executive-chart-container {
  position: relative;
  height: 300px; /* SABİT YÜKSEKLİK */
  margin: 15px 0;
  background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(248,249,250,0.9));
  border-radius: 12px;
  padding: 15px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.4);
  flex-grow: 1;
}

/* Executive Metrics - Kompakt */
.executive-metrics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
  margin-bottom: 15px;
  flex-shrink: 0;
}

.executive-metric {
  background: linear-gradient(145deg, #ffffff, #f8f9fa);
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.05);
  text-align: center;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.executive-metric::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--executive-primary);
}

.executive-metric:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.metric-value {
  font-size: 1.4rem;
  font-weight: 800;
  color: #2c3e50;
  margin-bottom: 5px;
  background: var(--executive-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.metric-label {
  font-size: 0.75rem;
  color: #6c757d;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-change {
  font-size: 0.7rem;
  font-weight: 700;
  margin-top: 3px;
  padding: 2px 6px;
  border-radius: 10px;
}

.metric-change.positive {
  color: #27ae60;
  background: rgba(39, 174, 96, 0.1);
}

.metric-change.negative {
  color: #e74c3c;
  background: rgba(231, 76, 60, 0.1);
}

/* Executive Legend - Kompakt */
.executive-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
  padding: 12px;
  background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(248,249,250,0.9));
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.3);
  backdrop-filter: blur(10px);
  flex-shrink: 0;
}

.executive-legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
  font-size: 0.8rem;
}

.executive-legend-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.12);
  border-color: rgba(102, 126, 234, 0.3);
}

.legend-color {
  width: 10px;
  height: 10px;
  border-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.legend-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #2c3e50;
}

.legend-value {
  font-size: 0.75rem;
  color: #6c757d;
  font-weight: 500;
  margin-left: auto;
}

/* Executive Insights - Kompakt */
.executive-insights {
  margin-top: 15px;
  padding: 15px;
  background: linear-gradient(145deg, #f8f9fa, #e9ecef);
  border-radius: 12px;
  border-left: 4px solid #667eea;
  box-shadow: 0 6px 15px rgba(0,0,0,0.08);
  flex-shrink: 0;
  min-height: 120px;
  display: block !important;
  visibility: visible !important;
}

.insights-title {
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 8px;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
}

.insights-title i {
  margin-right: 8px;
  color: #667eea;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-top: 8px;
}

.insight-item {
  padding: 10px;
  background: white;
  border-radius: 8px;
  border-left: 3px solid #667eea;
  transition: all 0.3s ease;
  min-height: 60px;
}

.insight-item:hover {
  transform: translateX(3px);
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.insight-item h5 {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 0.85rem;
  line-height: 1.2;
}

.insight-item p {
  color: #6c757d;
  font-size: 0.75rem;
  margin: 0;
  line-height: 1.3;
}

/* Responsive Düzenlemeler */
@media (max-width: 768px) {
  .executive-chart-card {
    padding: 20px;
    height: auto;
    min-height: 450px;
  }
  
  .executive-chart-container {
    height: 250px;
    padding: 12px;
  }
  
  .executive-metrics {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .executive-title {
    font-size: 1.2rem;
  }
  
  .metric-value {
    font-size: 1.2rem;
  }
}

/* Improved Small Boxes */
.small-box {
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-primary);
  transition: all 0.3s ease;
  overflow: hidden;
  position: relative;
}

.small-box:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-hover);
}

.small-box::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
}

.small-box .inner h1,
.small-box .inner h3 {
  font-weight: 800;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Card Improvements */
.card {
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-primary);
  border: none;
  overflow: hidden;
}

.card-header {
  background: var(--executive-primary);
  color: white;
  border: none;
  padding: 20px;
}

.card-header h3 {
  margin: 0;
  font-weight: 700;
}

/* Activities Section - Enhanced */
.activities-section {
  background: var(--card-bg);
  border-radius: var(--border-radius);
  padding: 25px;
  box-shadow: var(--shadow-primary);
  margin-top: 30px;
}

.activities-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid rgba(102, 126, 234, 0.1);
}

.activities-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.activities-title i {
  margin-right: 12px;
  padding: 12px;
  background: var(--executive-primary);
  color: white;
  border-radius: 10px;
  font-size: 16px;
}

.add-activity-btn {
  background: var(--executive-primary);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-activity-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.activity-form {
  display: none;
  background: linear-gradient(145deg, #f8f9fa, #e9ecef);
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  border: 2px solid rgba(102, 126, 234, 0.2);
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 5px;
}

.form-group input, .form-group textarea, .form-group select {
  width: 100%;
  padding: 12px;
  border: 2px solid rgba(0,0,0,0.1);
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-group input:focus, .form-group textarea:focus, .form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
}

.form-buttons {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-save, .btn-cancel {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-save {
  background: #27ae60;
  color: white;
}

.btn-save:hover {
  background: #229954;
  transform: translateY(-2px);
}

.btn-cancel {
  background: #95a5a6;
  color: white;
}

.btn-cancel:hover {
  background: #7f8c8d;
  transform: translateY(-2px);
}

.activity-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.08);
  border-left: 4px solid #667eea;
  transition: all 0.3s ease;
  position: relative;
}

.activity-item:hover {
  transform: translateX(5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.activity-title {
  font-weight: 700;
  color: #2c3e50;
  font-size: 1.1rem;
  flex: 1;
}

.activity-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  padding: 6px 10px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-edit {
  background: #f39c12;
  color: white;
}

.btn-edit:hover {
  background: #e67e22;
  transform: translateY(-1px);
}

.btn-delete {
  background: #e74c3c;
  color: white;
}

.btn-delete:hover {
  background: #c0392b;
  transform: translateY(-1px);
}

.activity-description {
  color: #6c757d;
  font-size: 0.95rem;
  margin-bottom: 10px;
  line-height: 1.5;
}

.activity-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: #95a5a6;
}

.activity-type {
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.8rem;
}

.type-siparis {
  background: rgba(52, 152, 219, 0.1);
  color: #3498db;
}

.type-tedarik {
  background: rgba(46, 204, 113, 0.1);
  color: #2ecc71;
}

.type-toplanti {
  background: rgba(155, 89, 182, 0.1);
  color: #9b59b6;
}

.type-diger {
  background: rgba(149, 165, 166, 0.1);
  color: #95a5a6;
}

/* Animation Keyframes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.executive-chart-card {
  animation: fadeInUp 0.5s ease-out;
}

.executive-chart-card:nth-child(2) {
  animation-delay: 0.1s;
}

.executive-chart-card:nth-child(3) {
  animation-delay: 0.2s;
}

.executive-chart-card:nth-child(4) {
  animation-delay: 0.3s;
}

/* Modern Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #764ba2, #667eea);
}
</style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Anasayfa</a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <span class="brand-text font-weight-light">Tedarik Zinciri Yönetimi</span>
    </a>

    <div class="sidebar">
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item menu-open">
            <a href="index.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="pages/charts/chart.php" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>Grafikler</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pages/tables/data.php" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>Database</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pages/map/map.php" class="nav-link">
              <i class="nav-icon fas fa-map-marked-alt"></i>
              <p>Lokasyon Bazlı Bilgi</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Swiss Hotel Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
              <li class="breadcrumb-item active">DASHBOARD</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Executive Statistics boxes -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h1><?php echo $dashboard_stats['total_products']; ?></h1>
                <p>Toplam Ürün Portföyü</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?php echo $dashboard_stats['inventory_turnover']; ?><sup style="font-size: 20px">%</sup></h3>
                <p>Ortalama Envanter Devir Oranı</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h1><?php echo $dashboard_stats['total_suppliers']; ?></h1>
                <p>Stratejik Tedarik Ortakları</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h1>₺<?php echo number_format($dashboard_stats['total_revenue'], 0, ',', '.'); ?></h1>
                <p>Bu Yılki Toplam Ciro</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Professional Charts Section -->
        <div class="row">
          <!-- Kategori Bazlı Satış Dağılımı -->
          <div class="col-lg-6">
            <div class="executive-chart-card">
              <div class="executive-title">
                <i class="fas fa-chart-pie"></i>
                Kategori Bazlı Gelir Dağılımı
              </div>
              <div class="executive-metrics">
                <div class="executive-metric">
                  <div class="metric-value">₺<?php
                    $total_revenue = 0;
                    $kategori_result->data_seek(0);
                    while($row = $kategori_result->fetch_assoc()) {
                        $total_revenue += $row['toplam_satis'];
                    }
                    echo number_format($total_revenue/1000000, 1);
                  ?>M</div>
                  <div class="metric-label">Toplam Gelir</div>
                  <div class="metric-change positive">+18.5%</div>
                </div>
                <div class="executive-metric">
                  <div class="metric-value"><?php
                    $kategori_result->data_seek(0);
                    echo $kategori_result->num_rows;
                  ?></div>
                  <div class="metric-label">Aktif Kategori</div>
                  <div class="metric-change positive">+1</div>
                </div>
              </div>
              <div class="executive-chart-container">
                <canvas id="kategoriDonutChart"></canvas>
              </div>
              <div class="executive-legend" id="kategoriLegend"></div>
            </div>
          </div>

          <!-- Yıllık Performans Trendi -->
          <div class="col-lg-6">
            <div class="executive-chart-card" style="height: 580px;">
              <div class="executive-title">
                <i class="fas fa-chart-line"></i>
                Yıllık Büyüme Performansı
              </div>
<!--               <div class="executive-metrics">
                <div class="executive-metric">
                  <div class="metric-value"><?php
                    $yillik_result->data_seek(0);
                    $growth_data = [];
                    while($row = $yillik_result->fetch_assoc()) {
                        $growth_data[] = $row['yillik_gelir'];
                    }
                    if(count($growth_data) >= 2) {
                        $growth_rate = (($growth_data[count($growth_data)-1] - $growth_data[0]) / $growth_data[0]) * 100;
                        echo number_format($growth_rate, 0);
                    } else {
                        echo "0";
                    }
                  ?>%</div>
                  <div class="metric-label">Büyüme Oranı</div>
                  <div class="metric-change positive">2022-2025</div>
                </div>
                <div class="executive-metric">
                  <div class="metric-value">₺<?php
                    echo number_format(end($growth_data)/1000000, 1);
                  ?>M</div>
                  <div class="metric-label">2025 Gelir</div>
                  <div class="metric-change positive">+45.2%</div>
                </div>
              </div> -->
              <div class="executive-chart-container" style="height: 250px;">
                <canvas id="yillikPerformansChart"></canvas>
              </div>
              <div class="executive-insights" style="margin-top: 10px; padding: 15px;">
                <div class="insights-title">
                  <i class="fas fa-lightbulb"></i>
                  Performans Özeti
                </div>
                <div class="insights-grid">
                  <div class="insight-item">
                    <h5>Trend Analizi</h5>
                    <p>Sürekli büyüme momentum devam ediyor</p>
                  </div>
                  <div class="insight-item">
                    <h5>Hedef Gerçekleşme</h5>
                    <p>2025 hedefinin %75'i şubat sonunda gerçekleşti</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Aylık Satış Trendi -->
          <div class="col-lg-8">
            <div class="executive-chart-card" style="height: 600px;">
              <div class="executive-title">
                <i class="fas fa-chart-area"></i>
                Aylık Gelir Trendi & Sezonalite Analizi
              </div>
              <div class="executive-chart-container" style="height: 400px;">
                <canvas id="aylikTrendChart"></canvas>
              </div>
              <div class="executive-insights">
                <div class="insights-title">
                  <i class="fas fa-brain"></i>
                  Stratejik İçgörüler
                </div>
                <div class="insights-grid">
                  <div class="insight-item">
                    <h5>Sezonsal Performans</h5>
                    <p>Q4 döneminde %35 artış gözlemleniyor</p>
                  </div>
                  <div class="insight-item">
                    <h5>Büyüme Momentum</h5>
                    <p>2025 hedeflerinin %75'si gerçekleşti</p>
                  </div>
                  <div class="insight-item">
                    <h5>Kategori Dinamikleri</h5>
                    <p>Premium kategorilerde güçlü talep</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Performing Products -->
          <div class="col-lg-4">
            <div class="executive-chart-card" style="height: 580px;">
              <div class="executive-title">
                <i class="fas fa-star"></i>
                En Performanslı Ürünler
              </div>
              <div class="executive-metrics">
                <div class="executive-metric">
                  <div class="metric-value">₺<?php
                    $top_urunler_result->data_seek(0);
                    $top_total = 0;
                    $count = 0;
                    while($row = $top_urunler_result->fetch_assoc() && $count < 10) {
                        $top_total += $row['toplam_satis'];
                        $count++;
                    }
                    echo number_format($top_total/1000000, 1);
                  ?>M</div>
                  <div class="metric-label">Top 10 Gelir</div>
                  <div class="metric-change positive">+15.7%</div>
                </div>
                <div class="executive-metric">
                  <div class="metric-value"><?php
                    echo number_format(($top_total / $total_revenue) * 100, 0);
                  ?>%</div>
                  <div class="metric-label">Gelir Payı</div>
                  <div class="metric-change positive">+3.2%</div>
                </div>
              </div>
              <div class="executive-chart-container" style="height: 280px;">
                <canvas id="topUrunlerChart"></canvas>
              </div>
              <div class="executive-insights" style="margin-top: 10px; padding: 15px;">
                <div class="insights-title">
                  <i class="fas fa-trophy"></i>
                  Ürün Analizi
                </div>
                <div class="insights-grid">
                  <div class="insight-item">
                    <h5>Premium Segment</h5>
                    <p>Yüksek marjlı ürünler öne çıkıyor</p>
                  </div>
                  <div class="insight-item">
                    <h5>Kategori Lideri</h5>
                    <p>Sıcak içecekler en yüksek performans</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Tedarikçi Performansı -->
          <div class="col-lg-12">
            <div class="executive-chart-card" style="height: 700px;">
              <div class="executive-title">
                <i class="fas fa-handshake"></i>
                Stratejik Tedarikçi Performansı & Coğrafi Dağılım
              </div>
              <div class="executive-metrics">
                <div class="executive-metric">
                  <div class="metric-value"><?php
                    $firma_result->data_seek(0);
                    echo $firma_result->num_rows;
                  ?></div>
                  <div class="metric-label">Aktif Partner</div>
                  <div class="metric-change positive">+5</div>
                </div>
                <div class="executive-metric">
                  <div class="metric-value">98.5%</div>
                  <div class="metric-label">Teslimat Oranı</div>
                  <div class="metric-change positive">+2.1%</div>
                </div>
                <div class="executive-metric">
                  <div class="metric-value">12%</div>
                  <div class="metric-label">Maliyet Tasarrufu</div>
                  <div class="metric-change positive">YoY</div>
                </div>
                <div class="executive-metric">
                  <div class="metric-value">7</div>
                  <div class="metric-label">Bölge Kapsamı</div>
                  <div class="metric-change positive">+1</div>
                </div>
              </div>
              <div class="executive-chart-container" style="height: 450px;">
                <canvas id="tedarikciPerformansChart"></canvas>
              </div>
              <div class="executive-insights">
                <div class="insights-title">
                  <i class="fas fa-users"></i>
                  Tedarikçi İlişkileri Analizi
                </div>
                <div class="insights-grid">
                  <div class="insight-item">
                    <h5>Portföy Çeşitliliği</h5>
                    <p>3 ana kategoride 48 stratejik partner</p>
                  </div>
                  <div class="insight-item">
                    <h5>Kalite Performansı</h5>
                    <p>%98.5 zamanında teslimat oranı</p>
                  </div>
                  <div class="insight-item">
                    <h5>Maliyet Optimizasyonu</h5>
                    <p>Son yıl %12 maliyet tasarrufu</p>
                  </div>
                  <div class="insight-item">
                    <h5>Risk Yönetimi</h5>
                    <p>Coğrafi dağılım ile risk minimizasyonu</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Activity List -->
        <div class="row">
          <div class="col-lg-12">
            <div class="activities-section">
              <div class="activities-header">
                <div class="activities-title">
                  <i class="fas fa-clipboard-list"></i>
                  İş Takip Sistemi & Son Aktiviteler
                </div>
                <button class="add-activity-btn" onclick="showActivityForm()">
                  <i class="fas fa-plus"></i>
                  Yeni Aktivite Ekle
                </button>
              </div>

              <!-- Activity Form -->
              <div class="activity-form" id="activityForm">
                <div class="form-group">
                  <label for="activityTitle">Aktivite Başlığı</label>
                  <input type="text" id="activityTitle" placeholder="Aktivite başlığını girin">
                </div>
                <div class="form-group">
                  <label for="activityDescription">Açıklama</label>
                  <textarea id="activityDescription" rows="3" placeholder="Detaylı açıklama girin"></textarea>
                </div>
                <div class="form-group">
                  <label for="activityType">Aktivite Türü</label>
                  <select id="activityType">
                    <option value="siparis">Sipariş</option>
                    <option value="tedarik">Tedarik</option>
                    <option value="toplanti">Toplantı</option>
                    <option value="diger">Diğer</option>
                  </select>
                </div>
                <div class="form-buttons">
                  <button class="btn-save" onclick="saveActivity()">
                    <i class="fas fa-save"></i> Kaydet
                  </button>
                  <button class="btn-cancel" onclick="hideActivityForm()">
                    <i class="fas fa-times"></i> İptal
                  </button>
                </div>
              </div>

              <!-- Activity List -->
              <div class="activity-list" id="activityList">
                <!-- Activities will be dynamically added here -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="main-footer">
      <strong>FATIMA ZEYNEP KAYA &copy; 2023-2025</strong>
    </footer>
  </div>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- Scripts -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="dist/js/adminlte.js"></script>

<script>
// Memory-based storage for activities (session-based)
let activities = [
  {
    id: 1,
    title: "Tedarikçi'den 'Premium Kahve' koli sipariş depoya ulaştı",
    description: "Starbucks tedarikçisinden 150 koli premium kahve ürünü başarıyla teslim alındı. Kalite kontrol işlemleri tamamlandı.",
    type: "siparis",
    timestamp: new Date(Date.now() - 2 * 60 * 1000) // 2 minutes ago
  },
  {
    id: 2,
    title: "Efes Pilsen ile Q2 kontrat görüşmesi planlandı",
    description: "Q2 dönem için yeni fiyatlandırma ve teslimat koşulları görüşülecek. Toplantı 15 Haziran'da planlandı.",
    type: "toplanti",
    timestamp: new Date(Date.now() - 4 * 60 * 60 * 1000) // 4 hours ago
  },
  {
    id: 3,
    title: "Çaykur tedarikçisi ile kalite kontrol raporu incelemesi",
    description: "Son teslimat edilen çay ürünlerinin kalite standartları değerlendirildi. Tüm ürünler standartları karşılıyor.",
    type: "tedarik",
    timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000) // 1 day ago
  }
];

let activityIdCounter = 4;
let editingActivityId = null;

// Activity Management Functions
function showActivityForm() {
  document.getElementById('activityForm').style.display = 'block';
  editingActivityId = null;
  clearForm();
}

function hideActivityForm() {
  document.getElementById('activityForm').style.display = 'none';
  editingActivityId = null;
  clearForm();
}

function clearForm() {
  document.getElementById('activityTitle').value = '';
  document.getElementById('activityDescription').value = '';
  document.getElementById('activityType').value = 'siparis';
}

function saveActivity() {
  const title = document.getElementById('activityTitle').value.trim();
  const description = document.getElementById('activityDescription').value.trim();
  const type = document.getElementById('activityType').value;

  if (!title) {
    alert('Lütfen aktivite başlığını girin.');
    return;
  }

  if (editingActivityId) {
    // Update existing activity
    const activity = activities.find(a => a.id === editingActivityId);
    if (activity) {
      activity.title = title;
      activity.description = description;
      activity.type = type;
    }
  } else {
    // Add new activity
    const newActivity = {
      id: activityIdCounter++,
      title: title,
      description: description,
      type: type,
      timestamp: new Date()
    };
    activities.unshift(newActivity); // Add to beginning
  }

  hideActivityForm();
  renderActivities();
}

function editActivity(id) {
  const activity = activities.find(a => a.id === id);
  if (activity) {
    document.getElementById('activityTitle').value = activity.title;
    document.getElementById('activityDescription').value = activity.description;
    document.getElementById('activityType').value = activity.type;
    
    editingActivityId = id;
    document.getElementById('activityForm').style.display = 'block';
  }
}

function deleteActivity(id) {
  if (confirm('Bu aktiviteyi silmek istediğinizden emin misiniz?')) {
    activities = activities.filter(a => a.id !== id);
    renderActivities();
  }
}

function formatTimeAgo(timestamp) {
  const now = new Date();
  const diffMs = now - timestamp;
  const diffMins = Math.floor(diffMs / (1000 * 60));
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

  if (diffMins < 60) {
    return diffMins <= 1 ? 'az önce' : `${diffMins} dakika önce`;
  } else if (diffHours < 24) {
    return `${diffHours} saat önce`;
  } else {
    return `${diffDays} gün önce`;
  }
}

function renderActivities() {
  const container = document.getElementById('activityList');
  
  if (activities.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 40px; color: #95a5a6;">
        <i class="fas fa-clipboard" style="font-size: 3rem; margin-bottom: 15px;"></i>
        <p style="font-size: 1.1rem;">Henüz aktivite bulunmuyor.</p>
        <p>Yeni aktivite eklemek için yukarıdaki butonu kullanın.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = activities.map(activity => `
    <div class="activity-item">
      <div class="activity-header">
        <div class="activity-title">${activity.title}</div>
        <div class="activity-actions">
          <button class="action-btn btn-edit" onclick="editActivity(${activity.id})">
            <i class="fas fa-edit"></i> Düzenle
          </button>
          <button class="action-btn btn-delete" onclick="deleteActivity(${activity.id})">
            <i class="fas fa-trash"></i> Sil
          </button>
        </div>
      </div>
      <div class="activity-description">${activity.description}</div>
      <div class="activity-meta">
        <span class="activity-type type-${activity.type}">
          ${activity.type === 'siparis' ? 'Sipariş' : 
            activity.type === 'tedarik' ? 'Tedarik' : 
            activity.type === 'toplanti' ? 'Toplantı' : 'Diğer'}
        </span>
        <span>${formatTimeAgo(activity.timestamp)}</span>
      </div>
    </div>
  `).join('');
}

$(document).ready(function() {
    // Professional Color Palette
    const professionalColors = {
        primary: ['#667eea', '#764ba2', '#f093fb'],
        secondary: ['#4facfe', '#00f2fe', '#43e97b'],
        accent: ['#f5576c', '#f093fb', '#ff9a9e']
    };

    // 1. Kategori Bazlı Satış Dağılımı - DİNAMİK VERİ
    const kategoriDonutData = {
        labels: [],
        datasets: [{
            data: [],
            backgroundColor: professionalColors.primary,
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverBorderWidth: 5,
            cutout: '60%'
        }]
    };

    <?php 
    $kategori_result->data_seek(0);
    $kategori_labels = [];
    $kategori_data = [];
    $kategori_details = [];
    while($row = $kategori_result->fetch_assoc()) {
        $kategori_labels[] = "'" . $row['kategori_ad'] . "'";
        $kategori_data[] = $row['toplam_satis'] ?: 0;
        $kategori_details[] = [
            'kategori' => $row['kategori_ad'],
            'satis' => $row['toplam_satis'] ?: 0,
            'urun_sayisi' => $row['urun_sayisi'] ?: 0,
            'adet' => $row['toplam_adet'] ?: 0
        ];
    }
    ?>

    kategoriDonutData.labels = [<?php echo implode(',', $kategori_labels); ?>];
    kategoriDonutData.datasets[0].data = [<?php echo implode(',', $kategori_data); ?>];

    // Professional Legend oluştur - DİNAMİK
    let kategoriLegendHtml = '';
    const kategoriDetails = <?php echo json_encode($kategori_details); ?>;
    const kategoriTotal = kategoriDetails.reduce((sum, cat) => sum + cat.satis, 0);
    kategoriDetails.forEach((item, index) => {
        const color = professionalColors.primary[index % professionalColors.primary.length];
        const percentage = kategoriTotal > 0 ? (item.satis / kategoriTotal * 100).toFixed(1) : '0.0';
        kategoriLegendHtml += `
            <div class="executive-legend-item">
                <div class="legend-color" style="background-color: ${color}"></div>
                <div class="legend-label">${item.kategori}</div>
                <div class="legend-value">₺${(item.satis/1000000).toFixed(1)}M (${percentage}%)</div>
            </div>
        `;
    });
    document.getElementById('kategoriLegend').innerHTML = kategoriLegendHtml;

    new Chart(document.getElementById('kategoriDonutChart'), {
        type: 'doughnut',
        data: kategoriDonutData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    cornerRadius: 10,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed * 100) / total).toFixed(1) : '0.0';
                            const details = kategoriDetails[context.dataIndex];
                            return [
                                context.label + ': ₺' + (context.parsed/1000000).toFixed(1) + 'M',
                                'Pay: %' + percentage,
                                'Ürün Çeşidi: ' + details.urun_sayisi,
                                'Toplam Adet: ' + details.adet.toLocaleString()
                            ];
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 1200,
                easing: 'easeInOutQuart'
            }
        }
    });

    // 2. Yıllık Performans Trendi - DİNAMİK VERİ
    const yillikPerformansData = {
        labels: [],
        datasets: [{
            label: 'Yıllık Gelir (₺)',
            data: [],
            backgroundColor: '#667eea',
            borderColor: '#667eea',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    };

    <?php 
    $yillik_result->data_seek(0);
    $yillik_labels = [];
    $yillik_data = [];
    $yillik_orders = [];
    while($row = $yillik_result->fetch_assoc()) {
        $yillik_labels[] = "'" . $row['yil'] . "'";
        $yillik_data[] = $row['yillik_gelir'] ?: 0;
        $yillik_orders[] = $row['yillik_siparis_sayisi'] ?: 0;
    }
    ?>

    yillikPerformansData.labels = [<?php echo implode(',', $yillik_labels); ?>];
    yillikPerformansData.datasets[0].data = [<?php echo implode(',', $yillik_data); ?>];
    const yillikOrders = [<?php echo implode(',', $yillik_orders); ?>];

    new Chart(document.getElementById('yillikPerformansChart'), {
        type: 'bar',
        data: yillikPerformansData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 10,
                    callbacks: {
                        title: function(context) {
                            return context[0].label + ' Yılı Performansı';
                        },
                        label: function(context) {
                            const currentValue = context.parsed.y;
                            const dataIndex = context.dataIndex;
                            const orders = yillikOrders[dataIndex];
                            let growthText = '';
                            
                            if (dataIndex > 0) {
                                const previousValue = context.dataset.data[dataIndex - 1];
                                const growth = previousValue > 0 ? ((currentValue - previousValue) / previousValue * 100).toFixed(1) : '0.0';
                                growthText = 'Büyüme: %' + growth;
                            }
                            
                            return [
                                'Gelir: ₺' + (currentValue/1000000).toFixed(1) + 'M',
                                'Sipariş: ' + orders.toLocaleString() + ' adet',
                                growthText
                            ].filter(item => item !== '');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: { size: 11, weight: '500' },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#6c757d',
                        font: { size: 12, weight: '600' }
                    }
                }
            },
            animation: {
                duration: 1200,
                easing: 'easeInOutQuart'
            }
        }
    });

    // 3. Aylık Satış Trendi - DİNAMİK VERİ
    const aylikTrendData = {
        labels: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
        datasets: []
    };

    const monthlyDataByYear = {};
    <?php 
    $yillik_aylik_result->data_seek(0);
    while($row = $yillik_aylik_result->fetch_assoc()) {
        $year = $row['yil'];
        $month = $row['ay'];
        $revenue = $row['aylik_gelir'] ?: 0;
        echo "if (!monthlyDataByYear['$year']) monthlyDataByYear['$year'] = new Array(12).fill(null);\n";
        echo "monthlyDataByYear['$year'][" . ($month-1) . "] = $revenue;\n";
    }
    ?>

    const yearStyles = {
        '2022': { color: '#667eea', gradient: 'rgba(102, 126, 234, 0.1)' },
        '2023': { color: '#f093fb', gradient: 'rgba(240, 147, 251, 0.1)' },
        '2024': { color: '#4facfe', gradient: 'rgba(79, 172, 254, 0.1)' },
        '2025': { color: '#43e97b', gradient: 'rgba(67, 233, 123, 0.1)' }
    };

    Object.keys(monthlyDataByYear).forEach(year => {
        const style = yearStyles[year] || { color: '#667eea', gradient: 'rgba(102, 126, 234, 0.1)' };
        aylikTrendData.datasets.push({
            label: year + ' Yılı',
            data: monthlyDataByYear[year],
            borderColor: style.color,
            backgroundColor: style.gradient,
            borderWidth: 3,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: style.color,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 8
        });
    });

    new Chart(document.getElementById('aylikTrendChart'), {
        type: 'line',
        data: aylikTrendData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        font: { size: 12, weight: '600' },
                        color: '#2c3e50',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            if (context.parsed.y === null) return null;
                            return context.dataset.label + ': ₺' + (context.parsed.y/1000000).toFixed(1) + 'M';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: { size: 11, weight: '500' },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                x: {
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        color: '#6c757d',
                        font: { size: 11, weight: '600' }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });

    // 4. Top Ürünler - DİNAMİK VERİ
    const topUrunlerData = {
        labels: [],
        datasets: [{
            label: 'Satış (₺)',
            data: [],
            backgroundColor: professionalColors.primary,
            borderColor: professionalColors.primary,
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };

    <?php 
    $top_urunler_result->data_seek(0);
    $urun_labels = [];
    $urun_data = [];
    $urun_details = [];
    while($row = $top_urunler_result->fetch_assoc()) {
        $urun_labels[] = "'" . substr($row['urun_ad'], 0, 20) . "'";
        $urun_data[] = $row['toplam_satis'] ?: 0;
        $urun_details[] = [
            'urun_ad' => $row['urun_ad'],
            'toplam_satis' => $row['toplam_satis'] ?: 0,
            'toplam_adet' => $row['toplam_adet'] ?: 0,
            'siparis_sayisi' => $row['siparis_sayisi'] ?: 0,
            'kategori_ad' => $row['kategori_ad']
        ];
    }
    ?>

    topUrunlerData.labels = [<?php echo implode(',', array_reverse($urun_labels)); ?>];
    topUrunlerData.datasets[0].data = [<?php echo implode(',', array_reverse($urun_data)); ?>];
    
    const topUrunDetails = <?php echo json_encode(array_reverse($urun_details)); ?>;

    new Chart(document.getElementById('topUrunlerChart'), {
        type: 'bar',
        data: topUrunlerData,
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 10,
                    callbacks: {
                        title: function(context) {
                            const details = topUrunDetails[context[0].dataIndex];
                            return details.urun_ad;
                        },
                        label: function(context) {
                            const details = topUrunDetails[context.dataIndex];
                            return [
                                'Satış: ₺' + (context.parsed.x/1000000).toFixed(1) + 'M',
                                'Sipariş Sayısı: ' + details.siparis_sayisi.toLocaleString(),
                                'Toplam Adet: ' + details.toplam_adet.toLocaleString(),
                                'Kategori: ' + details.kategori_ad
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: { size: 10, weight: '500' },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        color: '#2c3e50',
                        font: { size: 10, weight: '600' }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });

    // 5. Tedarikçi Performansı - DİNAMİK VERİ
    const tedarikciData = {
        labels: [],
        datasets: [{
            label: 'Gelir (₺)',
            data: [],
            backgroundColor: '#667eea',
            borderColor: '#667eea',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };

    <?php 
    $firma_result->data_seek(0);
    $tedarikci_labels = [];
    $tedarikci_data = [];
    $tedarikci_details = [];
    while($row = $firma_result->fetch_assoc()) {
        $tedarikci_labels[] = "'" . substr($row['firma_ad'], 0, 15) . "'";
        $tedarikci_data[] = $row['toplam_gelir'] ?: 0;
        $tedarikci_details[] = [
            'firma_ad' => $row['firma_ad'],
            'toplam_gelir' => $row['toplam_gelir'] ?: 0,
            'siparis_sayisi' => $row['siparis_sayisi'] ?: 0,
            'il_ad' => $row['il_ad'],
            'kategori_ad' => $row['kategori_ad'],
            'urun_cesidi' => $row['urun_cesidi'] ?: 0
        ];
    }
    ?>

    tedarikciData.labels = [<?php echo implode(',', $tedarikci_labels); ?>];
    tedarikciData.datasets[0].data = [<?php echo implode(',', $tedarikci_data); ?>];
    
    const tedarikciDetails = <?php echo json_encode($tedarikci_details); ?>;

    new Chart(document.getElementById('tedarikciPerformansChart'), {
        type: 'bar',
        data: tedarikciData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 10,
                    callbacks: {
                        title: function(context) {
                            const details = tedarikciDetails[context[0].dataIndex];
                            return details.firma_ad + ' (' + details.il_ad + ')';
                        },
                        label: function(context) {
                            const details = tedarikciDetails[context.dataIndex];
                            return [
                                'Gelir: ₺' + (context.parsed.y/1000000).toFixed(1) + 'M',
                                'Sipariş Sayısı: ' + details.siparis_sayisi.toLocaleString(),
                                'Ürün Çeşidi: ' + details.urun_cesidi,
                                'Kategori: ' + details.kategori_ad,
                                'Lokasyon: ' + details.il_ad
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: { size: 11, weight: '500' },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#2c3e50',
                        font: { size: 10, weight: '600' },
                        maxRotation: 45
                    }
                }
            },
            animation: {
                duration: 1800,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Chart animations on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.executive-chart-card').forEach(card => {
        observer.observe(card);
    });

    // Initialize activities
    renderActivities();

    console.log('Professional KDS Dashboard başarıyla yüklendi!');
    console.log('Dinamik veriler PHP\'den çekildi:');
    console.log('- Kategori sayısı:', kategoriDetails.length);
    console.log('- Top ürün sayısı:', topUrunDetails.length);
    console.log('- Tedarikçi sayısı:', tedarikciDetails.length);
});
</script>

</body>
</html>

<?php 
$connection->close();
include("footer.php"); 
?>