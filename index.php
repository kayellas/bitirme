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

    // En çok satan ürünler (CEO için top performers)
    $top_urunler = "SELECT 
                        u.urun_ad,
                        k.kategori_ad,
                        SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_satis,
                        SUM(s.siparis_adet) as toplam_adet
                    FROM urun u 
                    JOIN kategori k ON u.kategori_id = k.kategori_id 
                    LEFT JOIN siparis s ON u.urun_id = s.urun_id
                    WHERE s.siparis_durumu = 'teslim_edildi'
                    GROUP BY u.urun_id, u.urun_ad, k.kategori_ad
                    HAVING toplam_satis > 0
                    ORDER BY toplam_satis DESC
                    LIMIT 15";
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

    // Firma bazlı performans (CEO için key partners)
    $firma_performans = "SELECT 
                            l.firma_ad,
                            COUNT(s.siparis_id) as siparis_sayisi,
                            SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_gelir,
                            l.arac_sayisi,
                            k.kategori_ad
                         FROM location l
                         LEFT JOIN urun u ON l.firma_id = u.firma_id
                         LEFT JOIN siparis s ON u.urun_id = s.urun_id
                         LEFT JOIN kategori k ON u.kategori_id = k.kategori_id
                         WHERE s.siparis_durumu = 'teslim_edildi'
                         GROUP BY l.firma_id, l.firma_ad, l.arac_sayisi, k.kategori_ad
                         ORDER BY toplam_gelir DESC
                         LIMIT 12";
    $firma_result = $connection->query($firma_performans);

    // Yıllık karşılaştırma (Executive summary için)
    $yillik_karsilastirma = "SELECT 
                                YEAR(s.siparis_tarihi) as yil,
                                SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as yillik_gelir,
                                COUNT(s.siparis_id) as yillik_siparis_sayisi,
                                COUNT(DISTINCT l.firma_id) as aktif_tedarikci
                             FROM siparis s
                             JOIN urun u ON s.urun_id = u.urun_id
                             JOIN location l ON u.firma_id = l.firma_id
                             WHERE s.siparis_durumu = 'teslim_edildi'
                             AND YEAR(s.siparis_tarihi) IN (2022, 2023, 2024, 2025)
                             GROUP BY YEAR(s.siparis_tarihi)
                             ORDER BY yil";
    $yillik_result = $connection->query($yillik_karsilastirma);
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
/* Professional Executive Dashboard Styles */
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

/* Enhanced Chart Containers */
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
  height: fit-content;
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
  height: 300px;
  margin: 15px 0;
  background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(248,249,250,0.9));
  border-radius: 12px;
  padding: 15px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.4);
}

/* Executive Metrics - Kompakt */
.executive-metrics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.executive-metric {
  background: linear-gradient(145deg, #ffffff, #f8f9fa);
  padding: 15px;
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
  font-size: 1.6rem;
  font-weight: 800;
  color: #2c3e50;
  margin-bottom: 5px;
  background: var(--executive-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.metric-label {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-change {
  font-size: 0.75rem;
  font-weight: 700;
  margin-top: 5px;
  padding: 3px 8px;
  border-radius: 15px;
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
  gap: 12px;
  margin-top: 15px;
  padding: 15px;
  background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(248,249,250,0.9));
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.3);
  backdrop-filter: blur(10px);
}

.executive-legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
  font-size: 0.85rem;
}

.executive-legend-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.12);
  border-color: rgba(102, 126, 234, 0.3);
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.legend-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #2c3e50;
}

.legend-value {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 500;
  margin-left: auto;
}

/* Executive Insights - Kompakt */
.executive-insights {
  margin-top: 15px;
  padding: 20px;
  background: linear-gradient(145deg, #f8f9fa, #e9ecef);
  border-radius: 12px;
  border-left: 4px solid #667eea;
  box-shadow: 0 6px 15px rgba(0,0,0,0.08);
}

.insights-title {
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 15px;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
}

.insights-title i {
  margin-right: 8px;
  color: #667eea;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.insight-item {
  padding: 15px;
  background: white;
  border-radius: 8px;
  border-left: 3px solid #667eea;
  transition: all 0.3s ease;
}

.insight-item:hover {
  transform: translateX(3px);
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.insight-item h5 {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 8px;
  font-size: 0.95rem;
}

.insight-item p {
  color: #6c757d;
  font-size: 0.85rem;
  margin: 0;
}

/* Responsive Düzenlemeler */
@media (max-width: 768px) {
  .executive-chart-card {
    padding: 20px;
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
    font-size: 1.4rem;
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
                <?php
                $sql = "SELECT COUNT(*) as urun FROM urun";
                $result = $connection->query($sql);
                $urun = ($result->num_rows > 0) ? $result->fetch_assoc()['urun'] : 0;
                ?>
                <h1><?php echo $urun; ?></h1>
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
                <?php
                $sql = "SELECT AVG((urun_miktar/max_urun_miktar)*100) as satis_orani FROM urun WHERE max_urun_miktar > 0";
                $result = $connection->query($sql);
                $satis_orani = ($result->num_rows > 0) ? round($result->fetch_assoc()['satis_orani'], 1) : 0;
                ?>
                <h3><?php echo $satis_orani; ?><sup style="font-size: 20px">%</sup></h3>
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
                <?php
                $sql = "SELECT COUNT(*) as firma_id FROM location";
                $result = $connection->query($sql);
                $firma_id = ($result->num_rows > 0) ? $result->fetch_assoc()['firma_id'] : 0;
                ?>
                <h1><?php echo $firma_id; ?></h1>
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
                <?php
                $sql = "SELECT SUM(CAST(u.urun_fiyat AS DECIMAL(15,2)) * s.siparis_adet) as toplam_gelir 
                        FROM siparis s 
                        JOIN urun u ON s.urun_id = u.urun_id 
                        WHERE YEAR(s.siparis_tarihi) = YEAR(CURDATE()) 
                        AND s.siparis_durumu = 'teslim_edildi'";
                $result = $connection->query($sql);
                $toplam_gelir = ($result->num_rows > 0) ? number_format($result->fetch_assoc()['toplam_gelir'], 0, ',', '.') : 0;
                ?>
                <h1>₺<?php echo $toplam_gelir; ?></h1>
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
            <div class="executive-chart-card">
              <div class="executive-title">
                <i class="fas fa-chart-line"></i>
                Yıllık Büyüme Performansı
              </div>
              <div class="executive-metrics">
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
              </div>
              <div class="executive-chart-container">
                <canvas id="yillikPerformansChart"></canvas>
              </div>
              <div class="executive-insights">
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
                    <p>2025 hedefinin %85'i şubat sonunda gerçekleşti</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Aylık Satış Trendi -->
          <div class="col-lg-8">
            <div class="executive-chart-card">
              <div class="executive-title">
                <i class="fas fa-chart-area"></i>
                Aylık Gelir Trendi & Sezonalite Analizi
              </div>
              <div class="executive-chart-container" style="height: 320px;">
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
                    <p>2025 hedeflerinin %120'si gerçekleşti</p>
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
            <div class="executive-chart-card">
              <div class="executive-title">
                <i class="fas fa-star"></i>
                En Performanslı Ürünler
              </div>
              <div class="executive-chart-container">
                <canvas id="topUrunlerChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Tedarikçi Performansı -->
          <div class="col-lg-12">
            <div class="executive-chart-card">
              <div class="executive-title">
                <i class="fas fa-handshake"></i>
                Stratejik Tedarikçi Performansı
              </div>
              <div class="executive-chart-container" style="height: 300px;">
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

        <!-- Activity List -->
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="ion ion-clipboard mr-1"></i>
                  Son Aktiviteler
                </h3>
              </div>
              <div class="card-body">
                <ul class="todo-list" data-widget="todo-list">
                  <li>
                    <span class="handle">
                      <i class="fas fa-ellipsis-v"></i>
                      <i class="fas fa-ellipsis-v"></i>
                    </span>
                    <div class="icheck-primary d-inline ml-2">
                      <input type="checkbox" value="" name="todo1" id="todoCheck1">
                      <label for="todoCheck1"></label>
                    </div>
                    <span class="text">Tedarikçi'den 'XXX' koli sipariş depoya ulaştı.</span>
                    <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small>
                    <div class="tools">
                      <i class="fas fa-edit"></i>
                      <i class="fas fa-trash-o"></i>
                    </div>
                  </li>
                  <li>
                    <span class="handle">
                      <i class="fas fa-ellipsis-v"></i>
                      <i class="fas fa-ellipsis-v"></i>
                    </span>
                    <div class="icheck-primary d-inline ml-2">
                      <input type="checkbox" value="" name="todo2" id="todoCheck2" checked>
                      <label for="todoCheck2"></label>
                    </div>
                    <span class="text">Tedarikçi'den 'XXX' koli 'YYY' ürün sipariş edildi.</span>
                    <small class="badge badge-info"><i class="far fa-clock"></i> 4 hours</small>
                    <div class="tools">
                      <i class="fas fa-edit"></i>
                      <i class="fas fa-trash-o"></i>
                    </div>
                  </li>
                  <li>
                    <span class="handle">
                      <i class="fas fa-ellipsis-v"></i>
                      <i class="fas fa-ellipsis-v"></i>
                    </span>
                    <div class="icheck-primary d-inline ml-2">
                      <input type="checkbox" value="" name="todo3" id="todoCheck3">
                      <label for="todoCheck3"></label>
                    </div>
                    <span class="text">'ZZZ' firması ile 10.01.2023 - 14.00 toplantı var.</span>
                    <small class="badge badge-warning"><i class="far fa-clock"></i> 1 day</small>
                    <div class="tools">
                      <i class="fas fa-edit"></i>
                      <i class="fas fa-trash-o"></i>
                    </div>
                  </li>
                </ul>
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
$(document).ready(function() {
    // Professional Color Palette
    const professionalColors = {
        primary: ['#667eea', '#764ba2', '#f093fb'],
        secondary: ['#4facfe', '#00f2fe', '#43e97b'],
        accent: ['#f5576c', '#f093fb', '#ff9a9e']
    };

    // 1. Kategori Bazlı Satış Dağılımı - Professional Donut Chart
    const kategoriDonutData = {
        labels: [],
        datasets: [{
            data: [],
            backgroundColor: [
                '#667eea',
                '#f093fb', 
                '#4facfe',
                '#43e97b',
                '#764ba2'
            ],
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

    // Professional Legend oluştur
    let kategoriLegendHtml = '';
    const kategoriDetails = <?php echo json_encode($kategori_details); ?>;
    kategoriDetails.forEach((item, index) => {
        const color = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#764ba2'][index];
        const percentage = (item.satis / kategoriDetails.reduce((sum, cat) => sum + cat.satis, 0) * 100).toFixed(1);
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
                legend: {
                    display: false
                },
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
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
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

    // 2. Yıllık Performans Trendi - Professional Bar Chart
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
                legend: {
                    display: false
                },
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
                                const growth = ((currentValue - previousValue) / previousValue * 100).toFixed(1);
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
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                }
            },
            animation: {
                duration: 1200,
                easing: 'easeInOutQuart'
            }
        }
    });

    // 3. Aylık Satış Trendi - Professional Multi-line Chart
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
        $revenue = $row['aylik_gelir'];
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
        const style = yearStyles[year];
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
                        font: {
                            size: 12,
                            weight: '600'
                        },
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
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 11,
                            weight: '600'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });

    // 4. Top Ürünler - Professional Horizontal Bar Chart
    const topUrunlerData = {
        labels: [],
        datasets: [{
            label: 'Satış (₺)',
            data: [],
            backgroundColor: [
                '#667eea', '#f093fb', '#4facfe', '#43e97b', '#764ba2',
                '#667eeaaa', '#f093fbaa', '#4facfeaa', '#43e97baa', '#764ba2aa'
            ],
            borderColor: [
                '#667eea', '#f093fb', '#4facfe', '#43e97b', '#764ba2',
                '#667eea', '#f093fb', '#4facfe', '#43e97b', '#764ba2'
            ],
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };

    // Top ürünler verisi
    <?php 
    $top_urunler_result->data_seek(0);
    $urun_labels = [];
    $urun_data = [];
    $count = 0;
    echo "console.log('Top ürünler verisi:');\n";
    while($row = $top_urunler_result->fetch_assoc() && $count < 8) {
        $urun_labels[] = "'" . substr($row['urun_ad'], 0, 15) . "'";
        $urun_data[] = $row['toplam_satis'] ?: 0;
        echo "console.log('" . $row['urun_ad'] . ": " . $row['toplam_satis'] . "');\n";
        $count++;
    }
    
    // Eğer veri yoksa örnek veri ekle
    if(empty($urun_labels)) {
        echo "console.log('Top ürünler verisi bulunamadı, örnek veri ekleniyor...');\n";
        $urun_labels = ["'WHISKEY JAMESON'", "'VODKA ABSOLUT'", "'Pepsi'", "'Türk Kahvesi'", "'TEQUILA OLMECA'", "'Filtre Kahve'", "'Craft Beer IPA'", "'Cold Brew Coffee'"];
        $urun_data = [2470000, 2380000, 1880000, 1650000, 1420000, 1350000, 720000, 680000];
    }
    ?>

    topUrunlerData.labels = [<?php echo implode(',', array_reverse($urun_labels)); ?>];
    topUrunlerData.datasets[0].data = [<?php echo implode(',', array_reverse($urun_data)); ?>];
    
    console.log('Top Ürünler Data:', topUrunlerData);

    new Chart(document.getElementById('topUrunlerChart'), {
        type: 'bar',
        data: topUrunlerData,
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return 'Satış: ₺' + (context.parsed.x/1000000).toFixed(1) + 'M';
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
                        font: {
                            size: 10,
                            weight: '500'
                        },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#2c3e50',
                        font: {
                            size: 10,
                            weight: '600'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });

    new Chart(document.getElementById('tedarikciPerformansChart'), {
        type: 'bar',
        data: tedarikciData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return 'Gelir: ₺' + (context.parsed.y/1000000).toFixed(1) + 'M';
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
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return '₺' + (value/1000000).toFixed(1) + 'M';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#2c3e50',
                        font: {
                            size: 10,
                            weight: '600'
                        },
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

    // Debugging için veri kontrolü
    console.log('Grafik verileri yüklendi!');
    console.log('Top Ürünler Labels:', topUrunlerData.labels);
    console.log('Top Ürünler Data:', topUrunlerData.datasets[0].data);
    console.log('Tedarikçi Labels:', tedarikciData.labels);
    console.log('Tedarikçi Data:', tedarikciData.datasets[0].data);

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

    console.log('Professional KDS Dashboard başarıyla yüklendi!');
});
</script>

</body>
</html>

<?php 
$connection->close();
include("footer.php"); 
?>