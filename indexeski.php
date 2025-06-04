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

    // Kategori bazlı satışlar için veri
    $kategori_satis = "SELECT k.kategori_ad, SUM(u.urun_fiyat) as toplam_satis, COUNT(u.urun_id) as urun_sayisi
                      FROM kategori k 
                      LEFT JOIN urun u ON k.kategori_id = u.kategori_id 
                      GROUP BY k.kategori_id, k.kategori_ad";
    $kategori_result = $connection->query($kategori_satis);

    // Aylık satış trendi için veri
    $aylik_satis = "SELECT 
                        YEAR(urun_tarih) as yil,
                        MONTH(urun_tarih) as ay,
                        SUM(urun_fiyat) as aylik_gelir,
                        COUNT(urun_id) as aylik_urun_sayisi
                    FROM urun 
                    WHERE urun_tarih >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY YEAR(urun_tarih), MONTH(urun_tarih)
                    ORDER BY yil, ay";
    $aylik_result = $connection->query($aylik_satis);

    // Firma bazlı performans
    $firma_performans = "SELECT 
                            l.firma_ad,
                            COUNT(u.urun_id) as urun_sayisi,
                            SUM(u.urun_fiyat) as toplam_gelir,
                            l.arac_sayisi
                         FROM location l
                         LEFT JOIN urun u ON l.firma_id = u.firma_id
                         GROUP BY l.firma_id, l.firma_ad, l.arac_sayisi
                         ORDER BY toplam_gelir DESC
                         LIMIT 10";
    $firma_result = $connection->query($firma_performans);

    // Yıllık karşılaştırma
    $yillik_karsilastirma = "SELECT 
                                YEAR(urun_tarih) as yil,
                                SUM(urun_fiyat) as yillik_gelir,
                                COUNT(urun_id) as yillik_urun_sayisi
                             FROM urun 
                             WHERE YEAR(urun_tarih) IN (2022, 2023, 2024, 2025)
                             GROUP BY YEAR(urun_tarih)
                             ORDER BY yil";
    $yillik_result = $connection->query($yillik_karsilastirma);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Swiss Hotel Tedarik Zinciri | Dashboard</title>

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
/* Mevcut stillerinize ek olarak gelişmiş grafik stilleri */
.chart-container {
    position: relative;
    height: 450px;
    margin: 20px 0;
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 
        0 20px 40px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.6);
    border: 1px solid rgba(255,255,255,0.2);
}

.chart-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 25px;
    padding: 35px;
    box-shadow: 
        0 25px 50px rgba(0,0,0,0.08),
        0 0 0 1px rgba(255,255,255,0.05);
    margin-bottom: 35px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.chart-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, 
        #667eea 0%, 
        #764ba2 25%, 
        #f093fb 50%, 
        #f5576c 75%, 
        #4facfe 100%);
    border-radius: 25px 25px 0 0;
}

.chart-card:hover {
    transform: translateY(-8px);
    box-shadow: 
        0 35px 70px rgba(0,0,0,0.12),
        0 0 0 1px rgba(255,255,255,0.1);
}

.chart-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    position: relative;
}

.chart-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
}

.chart-title i {
    margin-right: 15px;
    padding: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px;
    font-size: 18px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.chart-metrics {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.metric-item {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 15px 20px;
    border-radius: 15px;
    border: 1px solid rgba(0,0,0,0.05);
    min-width: 120px;
    text-align: center;
}

.metric-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.metric-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.metric-change {
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 5px;
}

.metric-change.positive {
    color: #27ae60;
}

.metric-change.negative {
    color: #e74c3c;
}

/* Özel Chart Stilleri */
.executive-chart-container {
    position: relative;
    height: 400px;
    background: rgba(255,255,255,0.5);
    border-radius: 15px;
    padding: 15px;
    backdrop-filter: blur(10px);
}

.chart-annotation {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid rgba(102, 126, 234, 0.2);
}

.data-insights {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.insights-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 1rem;
}

.insights-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.insights-list li {
    padding: 8px 0;
    color: #495057;
    font-size: 0.9rem;
    position: relative;
    padding-left: 20px;
}

.insights-list li::before {
    content: '▶';
    color: #667eea;
    font-weight: bold;
    position: absolute;
    left: 0;
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
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Grafikler
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/charts/chart.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>ChartJS</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Tablolar
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/tables/data.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>DataTables</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-map-marker-alt"></i>
              <p>
                Lokasyon Bazlı Firma
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/map/map.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>MAP</p>
                </a>
              </li>
            </ul>
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
            <h1 class="m-0">Dashboard</h1>
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
        <!-- Statistics boxes -->
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
                <p>Toplam Ürün Sayısı</p>
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
                <p>Ortalama Satış Oranı</p>
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
                <p>Toplam Tedarikçi Sayısı</p>
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
                $sql = "SELECT SUM(CAST(urun_fiyat AS DECIMAL(10,2))) as toplam_gelir FROM urun WHERE YEAR(urun_tarih) = YEAR(CURDATE())";
                $result = $connection->query($sql);
                $toplam_gelir = ($result->num_rows > 0) ? number_format($result->fetch_assoc()['toplam_gelir'], 0, ',', '.') : 0;
                ?>
                <h1><?php echo $toplam_gelir; ?> ₺</h1>
                <p>Bu Yılki Toplam Gelir</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Gelişmiş Grafikler -->
        <div class="row">
          <!-- Kategori Bazlı Satışlar Pie Chart -->
          <div class="col-lg-6">
            <div class="chart-card">
              <div class="chart-title">
                <i class="fas fa-chart-pie"></i>
                Kategori Bazlı Satış Dağılımı
              </div>
              <div class="chart-container">
                <canvas id="categoryPieChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Aylık Satış Trendi Line Chart -->
          <div class="col-lg-6">
            <div class="chart-card">
              <div class="chart-title">
                <i class="fas fa-chart-line"></i>
                Aylık Satış Trendi
              </div>
              <div class="chart-container">
                <canvas id="monthlyTrendChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Top 10 Firma Performansı Bar Chart -->
          <div class="col-lg-8">
            <div class="chart-card">
              <div class="chart-title">
                <i class="fas fa-chart-bar"></i>
                Top 10 Firma Performansı
              </div>
              <div class="chart-container">
                <canvas id="firmaPerformansChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Yıllık Karşılaştırma Doughnut Chart -->
          <div class="col-lg-4">
            <div class="chart-card">
              <div class="chart-title">
                <i class="fas fa-chart-area"></i>
                Yıllık Gelir Karşılaştırması
              </div>
              <div class="chart-container">
                <canvas id="yillikKarsilastirmaChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Activity List - Existing Code -->
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
      <strong>FATIMA ZEYNEP KAYA &copy; 2023-2024</strong>
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
    // Kategori Bazlı Satışlar Pie Chart
    const categoryData = {
        labels: [
            <?php 
            $kategori_result->data_seek(0);
            $labels = [];
            while($row = $kategori_result->fetch_assoc()) {
                $labels[] = "'" . $row['kategori_ad'] . "'";
            }
            echo implode(',', $labels);
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                $kategori_result->data_seek(0);
                $data = [];
                while($row = $kategori_result->fetch_assoc()) {
                    $data[] = $row['toplam_satis'] ?: 0;
                }
                echo implode(',', $data);
                ?>
            ],
            backgroundColor: [
                '#FF6384',
                '#36A2EB', 
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };

    new Chart(document.getElementById('categoryPieChart'), {
        type: 'pie',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₺' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Aylık Satış Trendi Line Chart
    const monthlyTrendData = {
        labels: [
            <?php 
            $aylik_result->data_seek(0);
            $months = [];
            $monthNames = ['', 'Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
            while($row = $aylik_result->fetch_assoc()) {
                $months[] = "'" . $monthNames[$row['ay']] . " " . $row['yil'] . "'";
            }
            echo implode(',', $months);
            ?>
        ],
        datasets: [{
            label: 'Aylık Gelir (₺)',
            data: [
                <?php 
                $aylik_result->data_seek(0);
                $revenues = [];
                while($row = $aylik_result->fetch_assoc()) {
                    $revenues[] = $row['aylik_gelir'] ?: 0;
                }
                echo implode(',', $revenues);
                ?>
            ],
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#36A2EB',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    };

    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: monthlyTrendData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₺' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Top 10 Firma Performansı Bar Chart
    const firmaData = {
        labels: [
            <?php 
            $firma_result->data_seek(0);
            $firma_labels = [];
            while($row = $firma_result->fetch_assoc()) {
                $firma_labels[] = "'" . substr($row['firma_ad'], 0, 15) . "'";
            }
            echo implode(',', $firma_labels);
            ?>
        ],
        datasets: [{
            label: 'Toplam Gelir (₺)',
            data: [
                <?php 
                $firma_result->data_seek(0);
                $firma_revenues = [];
                while($row = $firma_result->fetch_assoc()) {
                    $firma_revenues[] = $row['toplam_gelir'] ?: 0;
                }
                echo implode(',', $firma_revenues);
                ?>
            ],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
            ],
            borderColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
            ],
            borderWidth: 2
        }]
    };

    new Chart(document.getElementById('firmaPerformansChart'), {
        type: 'bar',
        data: firmaData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₺' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45
                    }
                }
            }
        }
    });

    // Yıllık Karşılaştırma Doughnut Chart
    const yillikData = {
        labels: [
            <?php 
            $yillik_result->data_seek(0);
            $year_labels = [];
            while($row = $yillik_result->fetch_assoc()) {
                $year_labels[] = "'" . $row['yil'] . "'";
            }
            echo implode(',', $year_labels);
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                $yillik_result->data_seek(0);
                $year_data = [];
                while($row = $yillik_result->fetch_assoc()) {
                    $year_data[] = $row['yillik_gelir'] ?: 0;
                }
                echo implode(',', $year_data);
                ?>
            ],
            backgroundColor: [
                '#FF6384',
                '#36A2EB', 
                '#FFCE56',
                '#4BC0C0'
            ],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    };

    new Chart(document.getElementById('yillikKarsilastirmaChart'), {
        type: 'doughnut',
        data: yillikData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₺' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

</body>
</html>

<?php 
$connection->close();
include("footer.php"); 
?>