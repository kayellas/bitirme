<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tedarik Zincir Yönetimi - Harita ve Raporlar</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- AdminLTE Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <!-- Datatables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" />

  <style>
    /* Tam ekran için özel stiller */
    body {
      margin: 0;
      padding: 0;
      background-color: #f4f6f9;
    }
    
    .wrapper {
      min-height: 100vh;
    }
    
    .content-wrapper {
      margin-left: 250px;
      padding: 0;
      min-height: 100vh;
    }
    
    .main-content {
      padding: 20px;
    }
        
    .top-container {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        align-items: stretch;
    }
    
    .data-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .data-container h3 {
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
        margin-top: 0;
    }
    
    .map-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .table-container {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    #map {
        flex: 1;
        min-height: 400px;
        width: 100%;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    /* Harita Bilgi Etiketi */
    .map-info-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 1000;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 18px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        animation: fadeInDown 0.8s ease-out;
    }
    
    .map-info-badge i {
        margin-right: 8px;
        font-size: 16px;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Sekme Yapısı */
    .tab-container {
        margin-bottom: 15px;
    }
    
    .tab-buttons {
        display: flex;
        border-bottom: 2px solid #e0e0e0;
        margin-bottom: 0;
    }
    
    .tab-button {
        background: none;
        border: none;
        padding: 12px 24px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #666;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .tab-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s;
    }
    
    .tab-button:hover::before {
        left: 100%;
    }
    
    .tab-button.active {
        color: #007bff;
        border-bottom-color: #007bff;
        background: rgba(0,123,255,0.05);
    }
    
    .tab-button:hover {
        background: rgba(0,123,255,0.1);
        color: #007bff;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .table-wrapper {
        flex: 1;
        overflow-y: auto;
        max-height: 400px;
    }
    
    .bottom-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    
    th, td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }
    
    th {
        background-color: #f8f9fa;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .data-row:hover {
        background-color: #e3f2fd !important;
        cursor: pointer;
    }
    
    /* Filtreler ve Export Butonları */
    .filters-and-export-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin: 20px 0;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        gap: 20px;
    }
    
    .report-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
        flex: 1;
    }
    
    .export-buttons {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-shrink: 0;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 150px;
    }
    
    .filter-group label {
        font-weight: bold;
        font-size: 12px;
        color: #666;
    }
    
    .filter-group select, .filter-group input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .export-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
        font-size: 14px;
        white-space: nowrap;
    }
    
    .export-btn.pdf {
        background-color: rgb(216, 104, 12);
        color: white;
    }
    
    .export-btn.excel {
        background-color: rgb(73, 85, 243);
        color: white;
    }
    
    .export-btn:hover {
        opacity: 0.8;
        transform: translateY(-2px);
    }
    
    h3 {
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
    }

    /* Legend stilleri - küçültülmüş */
    .legend {
        background: white;
        padding: 10px 12px;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        font-size: 11px;
        line-height: 1.4;
        max-width: 200px;
    }

    .legend h4 {
        margin: 0 0 8px 0;
        font-size: 12px;
        color: #2c3e50;
        border-bottom: 1px solid #3498db;
        padding-bottom: 4px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
        padding: 2px 0;
    }

    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin-right: 6px;
        border: 1px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        flex-shrink: 0;
    }

    .legend-text {
        font-weight: 500;
        color: #34495e;
        font-size: 10px;
    }

    /* Responsive düzenlemeler */
    @media (max-width: 1200px) {
        .filters-and-export-container {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        
        .export-buttons {
            justify-content: flex-end;
            width: 100%;
        }
        
        .report-filters {
            justify-content: flex-start;
        }
    }
    
    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
        }
        
        .top-container {
            flex-direction: column;
        }
        
        .data-container {
            margin-bottom: 20px;
        }
        
        #map {
            min-height: 300px;
        }
        
        .table-wrapper {
            max-height: 300px;
        }
        
        .report-filters {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            min-width: auto;
            width: 100%;
        }
        
        .export-buttons {
            flex-direction: column;
            width: 100%;
        }
        
        .export-btn {
            width: 100%;
        }
        
        .tab-buttons {
            flex-wrap: wrap;
        }
        
        .tab-button {
            flex: 1;
            min-width: 120px;
        }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../../index.php" class="nav-link">Dashboard</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
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
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../../index.php" class="brand-link">
      <span class="brand-text font-weight-light">Tedarik Zincir Yönetimi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- SidebarSearch Form -->
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

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="../../index.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../charts/chart.php" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>Grafikler</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../tables/data.php" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>Database</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-map-marked-alt"></i>
              <p>Lokasyon Bazlı Bilgi</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="main-content">
        
        <!-- Üst Kısım (Harita ve Firma/Otel Bilgileri) -->
        <div class="top-container">
            <div class="data-container map-container">
                <h3><i class="fas fa-map-marked-alt"></i> Lokasyon Bilgileri</h3>
                <!-- <div class="map-info-badge">
                    <i class="fas fa-info-circle"></i>
                    Tedarikçiler ve Swissôtel Otelleri
                </div> -->
                <div id="map"></div>
            </div>
            <div class="data-container table-container">
                <h3><i class="fas fa-building"></i> Lokasyon Verileri</h3>
                
                <!-- Sekme Yapısı -->
                <div class="tab-container">
                    <div class="tab-buttons">
                        <button class="tab-button active" onclick="switchTab('firmaTab', this)">
                            <i class="fas fa-industry"></i> Tedarikçi Firmalar
                        </button>
                        <button class="tab-button" onclick="switchTab('otelTab', this)">
                            <i class="fas fa-hotel"></i> Swissôtel Otelleri
                        </button>
                    </div>
                </div>
                
                <!-- Firma Bilgileri Sekmesi -->
                <div id="firmaTab" class="tab-content active">
                    <div class="table-wrapper">
                        <table id="location-table">
                            <thead>
                                <tr>
                                    <th>Tedarik Firma Adı</th>
                                    <th>İl</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    function turkishTitleCase($string) {
                                        $string = mb_strtolower($string, 'UTF-8');
                                        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
                                    }
                                    
                                    $servername = "localhost";
                                    $username = "root";
                                    $password = "";
                                    $dbname = "kds";
                                    $conn = new mysqli($servername, $username, $password, $dbname);

                                    if ($conn->connect_error) {
                                        die("Bağlantı hatası: " . $conn->connect_error);
                                    }

                                    $locationData = array();

                                    $sql = "SELECT l.firma_ad, l.il_ad, l.lat, l.lng, l.kategori_ad, l.arac_sayisi, l.type 
                                           FROM location l 
                                           WHERE l.lat IS NOT NULL AND l.lng IS NOT NULL";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr class='data-row' data-lat='{$row['lat']}' data-lng='{$row['lng']}' data-kategori='{$row['kategori_ad']}'>";
                                            echo "<td>" . htmlspecialchars($row['firma_ad']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['il_ad']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['kategori_ad']) . "</td>";
                                            echo "</tr>";
                                            
                                            $locationData[] = array(
                                                'firma_ad' => $row['firma_ad'],
                                                'il_ad' => $row['il_ad'],
                                                'lat' => (float)$row['lat'],
                                                'lng' => (float)$row['lng'],
                                                'kategori_ad' => $row['kategori_ad'],
                                                'arac_sayisi' => $row['arac_sayisi'],
                                                'type' => $row['type']
                                            );
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>Veri bulunamadı.</td></tr>";
                                    }

                                    $otelData = array();
                                    $sql_otel = "SELECT otel_ad, il_ad, lat, lng FROM otel WHERE lat IS NOT NULL AND lng IS NOT NULL";
                                    $result_otel = $conn->query($sql_otel);
                                    if ($result_otel->num_rows > 0) {
                                        while($row = $result_otel->fetch_assoc()) {
                                            $otelData[] = array(
                                                'otel_ad' => $row['otel_ad'],
                                                'il_ad' => $row['il_ad'],
                                                'lat' => (float)$row['lat'],
                                                'lng' => (float)$row['lng']
                                            );
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Otel Bilgileri Sekmesi -->
                <div id="otelTab" class="tab-content">
                    <div class="table-wrapper">
                        <table id="otel-table">
                            <thead>
                                <tr>
                                    <th>Otel Adı</th>
                                    <th>İl</th>
                                    <th>Tür</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $result_otel = $conn->query($sql_otel);
                                    if ($result_otel->num_rows > 0) {
                                        while($row = $result_otel->fetch_assoc()) {
                                            echo "<tr class='data-row' data-lat='{$row['lat']}' data-lng='{$row['lng']}' data-kategori='otel'>";
                                            echo "<td>" . htmlspecialchars($row['otel_ad']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['il_ad']) . "</td>";
                                            echo "<td><span style='color: #f39c12; font-weight: bold;'><i class='fas fa-star'></i> Swissôtel</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>Otel verisi bulunamadı.</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alt Kısım (Stok Tablosu) -->
        <div class="bottom-container">
            <h3><i class="fas fa-chart-bar"></i> Firma Stok Durumu ve Fiyat Bilgileri</h3>
            
            <div class="filters-and-export-container">
                <div class="report-filters">
                    <div class="filter-group">
                        <label>İl Filtresi:</label>
                        <select id="il-filter">
                            <option value="">Tüm İller</option>
                            <?php
                                $sql_il = "SELECT DISTINCT il_ad FROM location WHERE il_ad IS NOT NULL ORDER BY il_ad";
                                $result_il = $conn->query($sql_il);
                                if ($result_il->num_rows > 0) {
                                    while($row_il = $result_il->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row_il['il_ad']) . "'>" . htmlspecialchars($row_il['il_ad']) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Kategori Filtresi:</label>
                        <select id="kategori-filter">
                            <option value="">Tüm Kategoriler</option>
                            <?php
                                $sql_kat = "SELECT DISTINCT k.kategori_ad FROM kategori k ORDER BY k.kategori_ad";
                                $result_kat = $conn->query($sql_kat);
                                if ($result_kat->num_rows > 0) {
                                    while($row_kat = $result_kat->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row_kat['kategori_ad']) . "'>" . htmlspecialchars($row_kat['kategori_ad']) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Firma Filtresi:</label>
                        <select id="firma-filter">
                            <option value="">Tüm Firmalar</option>
                            <?php
                                $sql_firma = "SELECT DISTINCT firma_ad FROM location WHERE firma_ad IS NOT NULL ORDER BY firma_ad";
                                $result_firma = $conn->query($sql_firma);
                                if ($result_firma->num_rows > 0) {
                                    while($row_firma = $result_firma->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row_firma['firma_ad']) . "'>" . htmlspecialchars($row_firma['firma_ad']) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Rapor Türü:</label>
                        <select id="rapor-filter">
                            <option value="detay">Detaylı Rapor</option>
                            <option value="ozet">Özet Rapor</option>
                        </select>
                    </div>
                </div>

                <div class="export-buttons">
                    <button class="export-btn pdf" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> PDF Dışa Aktar
                    </button>
                    <button class="export-btn excel" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Excel Dışa Aktar
                    </button>
                </div>
            </div>
       
            <table id="stok-table">
                <thead>
                    <tr>
                        <th>Tedarik Firma Adı</th>
                        <th>İl</th>
                        <th>Kategori</th>
                        <th>Ürün Sayısı</th>
                        <th>Toplam Stok</th>
                        <th>Stok Farkı</th>
                    </tr>
                </thead>
                <tbody id="stok-table-body">
                    <?php
                        $sql = "SELECT 
                                    l.firma_ad, 
                                    l.il_ad,  
                                    k.kategori_ad,
                                    COUNT(u.urun_id) AS urun_sayisi, 
                                    SUM(u.urun_miktar) AS toplam_stok, 
                                    SUM(u.max_urun_miktar - u.urun_miktar) AS toplam_stok_farki
                                FROM location l
                                JOIN urun u ON l.kategori_id = u.kategori_id
                                JOIN kategori k ON l.kategori_id = k.kategori_id
                                GROUP BY l.firma_ad, l.il_ad, l.kategori_id, k.kategori_ad
                                ORDER BY l.il_ad, l.firma_ad";
                        
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-firma='" . htmlspecialchars($row['firma_ad']) . "' data-il='" . htmlspecialchars($row['il_ad']) . "' data-kategori='" . htmlspecialchars($row['kategori_ad']) . "'>";
                                echo "<td>" . htmlspecialchars($row['firma_ad']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['il_ad']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['kategori_ad']) . "</td>";
                                echo "<td>" . number_format($row['urun_sayisi']) . "</td>";
                                echo "<td>" . number_format($row['toplam_stok']) . "</td>";
                                
                                $stok_farki = $row['toplam_stok_farki'];
                                $bg_color = '';
                                $text_color = '';

                                if ($stok_farki < 20000) {
                                    $bg_color = '#ffcdd2';
                                    $text_color = '#c62828';
                                } elseif ($stok_farki > 100000) {
                                    $bg_color = '#c8e6c9';
                                    $text_color = '#2e7d32';
                                }

                                echo "<td" . (!empty($bg_color) ? " style='background-color:$bg_color; color:$text_color;'" : "") . ">";
                                echo number_format($stok_farki);
                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Stok bilgisi bulunamadı.</td></tr>";
                        }

                        $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <!-- JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

        <script>
            // Sekme değiştirme fonksiyonu
            function switchTab(tabId, buttonElement) {
                // Tüm sekmeleri gizle
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Tüm butonlardan active sınıfını kaldır
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Seçilen sekmeyi göster
                document.getElementById(tabId).classList.add('active');
                buttonElement.classList.add('active');
                
                // Tabloyu yeniden boyutlandır
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize();
                    }
                }, 100);
            }

            // Konum verileri PHP'den JavaScript'e aktarılıyor
            const locationData = <?php echo json_encode($locationData); ?>;
            const otelData = <?php echo json_encode($otelData); ?>;
            
            let map;
            let markers = [];
            let layerGroups = {
                softDrinks: L.layerGroup(),
                hotDrinks: L.layerGroup(),
                alcohol: L.layerGroup(),
                hotels: L.layerGroup(),
                all: L.layerGroup()
            };

            const icons = {
                softDrinks: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                }),
                hotDrinks: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                }),
                alcohol: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                }),
                hotel: L.divIcon({
                    html: '<div style="background-color: #f39c12; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-star" style="color: white; font-size: 16px;"></i></div>',
                    className: 'custom-div-icon',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15],
                    popupAnchor: [0, -15]
                }),
                default: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-grey.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            };

            function initMap() {
                map = L.map('map').setView([39.9334, 32.8597], 6);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                let counts = {
                    softDrinks: 0,
                    hotDrinks: 0,
                    alcohol: 0,
                    hotels: 0,
                    total: 0
                };
                
                // Location markerlarını ekle
                locationData.forEach(function(location) {
                    if (location.lat && location.lng) {
                        let icon = icons.default;
                        let layerGroup = layerGroups.all;
                        
                        switch(location.kategori_ad) {
                            case 'Soft Drinks':
                                icon = icons.softDrinks;
                                layerGroup = layerGroups.softDrinks;
                                counts.softDrinks++;
                                break;
                            case 'Sıcak İçecekler':
                                icon = icons.hotDrinks;
                                layerGroup = layerGroups.hotDrinks;
                                counts.hotDrinks++;
                                break;
                            case 'Alkollü':
                                icon = icons.alcohol;
                                layerGroup = layerGroups.alcohol;
                                counts.alcohol++;
                                break;
                        }
                        
                        const popupContent = `
                            <div style="min-width:250px; font-family: Arial, sans-serif;">
                                <h3 style="color: #2c3e50; margin-bottom: 10px; border-bottom: 2px solid #007bff; padding-bottom: 5px;">
                                    ${location.firma_ad}
                                </h3>
                                <div style="display: grid; gap: 5px;">
                                    <div><strong>📍 İl:</strong> ${location.il_ad}</div>
                                    <div><strong>📦 Kategori:</strong> ${location.kategori_ad}</div>
                                    <div><strong>🚛 Araç Sayısı:</strong> ${location.arac_sayisi || 'Belirtilmemiş'}</div>
                                    <div><strong>🏢 Tip:</strong> ${location.type || 'Tedarikçi'}</div>
                                </div>
                            </div>
                        `;
                        
                        const marker = L.marker([location.lat, location.lng], {icon: icon})
                            .bindPopup(popupContent);
                        
                        layerGroup.addLayer(marker);
                        layerGroups.all.addLayer(marker);
                        markers.push(marker);
                        counts.total++;
                    }
                });

                // Otel markerlarını ekle
                otelData.forEach(function(otel) {
                    if (otel.lat && otel.lng) {
                        const popupContent = `
                            <div style="min-width:250px; font-family: Arial, sans-serif;">
                                <h3 style="color: #2c3e50; margin-bottom: 10px; border-bottom: 2px solid #f39c12; padding-bottom: 5px;">
                                    ⭐ ${otel.otel_ad}
                                </h3>
                                <div style="display: grid; gap: 5px;">
                                    <div><strong>📍 İl:</strong> ${otel.il_ad}</div>
                                    <div><strong>🏨 Tip:</strong> Otel</div>
                                    <div><strong>🏢 Zincir:</strong> Swissôtel</div>
                                </div>
                            </div>
                        `;
                        
                        const marker = L.marker([otel.lat, otel.lng], {icon: icons.hotel})
                            .bindPopup(popupContent);
                        
                        layerGroups.hotels.addLayer(marker);
                        layerGroups.all.addLayer(marker);
                        markers.push(marker);
                        counts.hotels++;
                        counts.total++;
                    }
                });

                // Tüm markerları haritaya ekle
                map.addLayer(layerGroups.all);

                // Geliştirilmiş Legend ekle
                const legend = L.control({position: 'bottomright'});
                legend.onAdd = function (map) {
                    const div = L.DomUtil.create('div', 'legend');
                    div.innerHTML = `
                        <h4><i class="fas fa-info-circle"></i> Harita Rehberi</h4>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #3388ff;"></div>
                            <span class="legend-text">Soft Drinks Tedarikçileri (${counts.softDrinks})</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ff3333;"></div>
                            <span class="legend-text">Sıcak İçecek Tedarikçileri (${counts.hotDrinks})</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #33ff33;"></div>
                            <span class="legend-text">Alkollü İçecek Tedarikçileri (${counts.alcohol})</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #f39c12; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-star" style="color: white; font-size: 12px;"></i>
                            </div>
                            <span class="legend-text">Swissôtel Otelleri (${counts.hotels})</span>
                        </div>
                    `;
                    return div;
                };
                legend.addTo(map);

/*                 // Harita üzerinde bilgi kutusu
                const info = L.control({position: 'topleft'});
                info.onAdd = function (map) {
                    const div = L.DomUtil.create('div', 'legend');
                    div.innerHTML = `
                        <h4><i class="fas fa-chart-pie"></i> Harita İstatistikleri</h4>
                        <div><strong>📊 Toplam Tedarikçi:</strong> ${locationData.length}</div>
                        <div><strong>⭐ Toplam Otel:</strong> ${otelData.length}</div>
                        <div><strong>🌍 Toplam Lokasyon:</strong> ${counts.total}</div>
                        <div><strong>🥤 Soft Drinks:</strong> ${counts.softDrinks}</div>
                        <div><strong>☕ Sıcak İçecekler:</strong> ${counts.hotDrinks}</div>
                        <div><strong>🍺 Alkollü İçecekler:</strong> ${counts.alcohol}</div>
                    `;
                    return div;
                };
                info.addTo(map); */

                // Haritayı tüm markerları kapsayacak şekilde ayarlama
                setTimeout(function() {
                    if (markers.length > 0) {
                        const group = new L.featureGroup(markers);
                        map.fitBounds(group.getBounds().pad(0.1));
                    }
                }, 1000);

                // Harita boyutlandırma sorunu çözümü
                setTimeout(function() {
                    map.invalidateSize();
                }, 100);
            }

            // Sayfa yüklendiğinde haritayı başlat
            document.addEventListener('DOMContentLoaded', function() {
                initMap();
                
                // Tablo satırlarına tıklama olayı - Hem firma hem otel tabloları için
                function addTableClickEvents() {
                    document.querySelectorAll('.data-row').forEach(function(row) {
                        row.addEventListener('click', function() {
                            const lat = parseFloat(this.getAttribute('data-lat'));
                            const lng = parseFloat(this.getAttribute('data-lng'));
                            const kategori = this.getAttribute('data-kategori');
                            
                            if (lat && lng) {
                                map.setView([lat, lng], 12);
                                
                                // İlgili marker'ı bul ve popup'ını aç
                                markers.forEach(function(marker) {
                                    const markerPos = marker.getLatLng();
                                    if (Math.abs(markerPos.lat - lat) < 0.001 && Math.abs(markerPos.lng - lng) < 0.001) {
                                        marker.openPopup();
                                    }
                                });

                                // Kategori layer'ını aktif et
                                map.eachLayer(function(layer) {
                                    if (layer instanceof L.LayerGroup) {
                                        map.removeLayer(layer);
                                    }
                                });

                                // İlgili kategori layer'ını ekle
                                if (kategori === 'otel') {
                                    map.addLayer(layerGroups.hotels);
                                } else {
                                    switch(kategori) {
                                        case 'Soft Drinks':
                                            map.addLayer(layerGroups.softDrinks);
                                            break;
                                        case 'Sıcak İçecekler':
                                            map.addLayer(layerGroups.hotDrinks);
                                            break;
                                        case 'Alkollü':
                                            map.addLayer(layerGroups.alcohol);
                                            break;
                                        default:
                                            map.addLayer(layerGroups.all);
                                    }
                                }
                            }
                        });
                    });
                }
                
                // İlk kez tablo click eventlerini ekle
                addTableClickEvents();
                
                // Sekme değiştiğinde yeniden event ekle
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.addEventListener('click', () => {
                        setTimeout(() => {
                            addTableClickEvents();
                        }, 100);
                    });
                });
            });

            // Filtreleme fonksiyonu
            function applyFilters() {
                const ilFilter = document.getElementById('il-filter').value.toLowerCase();
                const kategoriFilter = document.getElementById('kategori-filter').value.toLowerCase();
                const firmaFilter = document.getElementById('firma-filter').value.toLowerCase();
                const rows = document.querySelectorAll('#stok-table-body tr');

                rows.forEach(row => {
                    const firma = (row.getAttribute('data-firma') || '').toLowerCase();
                    const il = (row.getAttribute('data-il') || '').toLowerCase();
                    const kategori = (row.getAttribute('data-kategori') || '').toLowerCase();
                    
                    let showRow = true;
                    
                    if (ilFilter && !il.includes(ilFilter)) {
                        showRow = false;
                    }
                    
                    if (kategoriFilter && !kategori.includes(kategoriFilter)) {
                        showRow = false;
                    }
                    
                    if (firmaFilter && !firma.includes(firmaFilter)) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });

                // Harita filtrelerini de uygula
                applyMapFilters();
            }

            // Harita filtreleme fonksiyonu
            function applyMapFilters() {
                const ilFilter = document.getElementById('il-filter').value;
                const kategoriFilter = document.getElementById('kategori-filter').value;
                const firmaFilter = document.getElementById('firma-filter').value;

                // Tüm layer'ları temizle
                Object.values(layerGroups).forEach(group => {
                    group.clearLayers();
                });

                // Sayaçları sıfırla
                let counts = {
                    softDrinks: 0,
                    hotDrinks: 0,
                    alcohol: 0,
                    hotels: 0,
                    total: 0
                };

                // Filtrelenmiş markerları ekle
                locationData.forEach(function(location) {
                    if (location.lat && location.lng) {
                        let showMarker = true;
                        
                        if (ilFilter && location.il_ad !== ilFilter) {
                            showMarker = false;
                        }
                        
                        if (kategoriFilter && location.kategori_ad !== kategoriFilter) {
                            showMarker = false;
                        }
                        
                        if (firmaFilter && location.firma_ad !== firmaFilter) {
                            showMarker = false;
                        }

                        if (showMarker) {
                            let icon = icons.default;
                            let layerGroup = layerGroups.all;
                            
                            switch(location.kategori_ad) {
                                case 'Soft Drinks':
                                    icon = icons.softDrinks;
                                    layerGroup = layerGroups.softDrinks;
                                    counts.softDrinks++;
                                    break;
                                case 'Sıcak İçecekler':
                                    icon = icons.hotDrinks;
                                    layerGroup = layerGroups.hotDrinks;
                                    counts.hotDrinks++;
                                    break;
                                case 'Alkollü':
                                    icon = icons.alcohol;
                                    layerGroup = layerGroups.alcohol;
                                    counts.alcohol++;
                                    break;
                            }
                            
                            const popupContent = `
                                <div style="min-width:250px; font-family: Arial, sans-serif;">
                                    <h3 style="color: #2c3e50; margin-bottom: 10px; border-bottom: 2px solid #007bff; padding-bottom: 5px;">
                                        ${location.firma_ad}
                                    </h3>
                                    <div style="display: grid; gap: 5px;">
                                        <div><strong>📍 İl:</strong> ${location.il_ad}</div>
                                        <div><strong>📦 Kategori:</strong> ${location.kategori_ad}</div>
                                        <div><strong>🚛 Araç Sayısı:</strong> ${location.arac_sayisi || 'Belirtilmemiş'}</div>
                                        <div><strong>🏢 Tip:</strong> ${location.type || 'Tedarikçi'}</div>
                                    </div>
                                </div>
                            `;
                            
                            const marker = L.marker([location.lat, location.lng], {icon: icon})
                                .bindPopup(popupContent);
                            
                            layerGroup.addLayer(marker);
                            layerGroups.all.addLayer(marker);
                            counts.total++;
                        }
                    }
                });

                // Otel markerlarını da filtrele (il filtresine göre)
                otelData.forEach(function(otel) {
                    if (otel.lat && otel.lng) {
                        let showMarker = true;
                        
                        if (ilFilter && otel.il_ad !== ilFilter) {
                            showMarker = false;
                        }

                        if (showMarker) {
                            const popupContent = `
                                <div style="min-width:250px; font-family: Arial, sans-serif;">
                                    <h3 style="color: #2c3e50; margin-bottom: 10px; border-bottom: 2px solid #f39c12; padding-bottom: 5px;">
                                        ⭐ ${otel.otel_ad}
                                    </h3>
                                    <div style="display: grid; gap: 5px;">
                                        <div><strong>📍 İl:</strong> ${otel.il_ad}</div>
                                        <div><strong>🏨 Tip:</strong> Otel</div>
                                        <div><strong>🏢 Zincir:</strong> Swissôtel</div>
                                    </div>
                                </div>
                            `;
                            
                            const marker = L.marker([otel.lat, otel.lng], {icon: icons.hotel})
                                .bindPopup(popupContent);
                            
                            layerGroups.hotels.addLayer(marker);
                            layerGroups.all.addLayer(marker);
                            counts.hotels++;
                            counts.total++;
                        }
                    }
                });

                // Haritayı güncelle
                map.eachLayer(function(layer) {
                    if (layer instanceof L.LayerGroup && layer !== layerGroups.all) {
                        map.removeLayer(layer);
                    }
                });
                
                // Aktif layer'ı tekrar ekle
                if (!map.hasLayer(layerGroups.all)) {
                    map.addLayer(layerGroups.all);
                }

                // Legend'ı güncelle
                updateLegend(counts);
            }

            // Legend güncelleme fonksiyonu
            function updateLegend(counts) {
                const legendElement = document.querySelector('.legend');
                if (legendElement) {
                    legendElement.innerHTML = `
                        <h4><i class="fas fa-info-circle"></i> Harita Rehberi</h4>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #3388ff;"></div>
                            <span class="legend-text">Soft Drinks Tedarikçileri (${counts.softDrinks})</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ff3333;"></div>
                            <span class="legend-text">Sıcak İçecek Tedarikçileri (${counts.hotDrinks})</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #33ff33;"></div>
                            <span class="legend-text">Alkollü İçecek Tedarikçileri (${counts.alcohol})</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #f39c12; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-star" style="color: white; font-size: 12px;"></i>
                            </div>
                            <span class="legend-text">Swissôtel Otelleri (${counts.hotels})</span>
                        </div>
                    `;
                }
            }

            // Filtre event listener'ları
            document.getElementById('il-filter').addEventListener('change', applyFilters);
            document.getElementById('kategori-filter').addEventListener('change', applyFilters);
            document.getElementById('firma-filter').addEventListener('change', applyFilters);
            document.getElementById('rapor-filter').addEventListener('change', toggleReportType);

            // Rapor türü değiştirme fonksiyonu
            function toggleReportType() {
                const raporTuru = document.getElementById('rapor-filter').value;
                const table = document.getElementById('stok-table');
                const thead = table.querySelector('thead tr');
                const tbody = table.querySelectorAll('tbody tr');

                if (raporTuru === 'ozet') {
                    thead.innerHTML = `
                        <th>Tedarik Firma Adı</th>
                        <th>Kategori</th>
                        <th>Toplam Stok</th>
                    `;
                    
                    tbody.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length >= 3) {
                            row.innerHTML = `
                                <td>${cells[0].textContent}</td>
                                <td>${cells[2].textContent}</td>
                                <td>${cells[4].textContent}</td>
                            `;
                        }
                    });
                } else {
                    thead.innerHTML = `
                        <th>Tedarik Firma Adı</th>
                        <th>İl</th>
                        <th>Kategori</th>
                        <th>Ürün Sayısı</th>
                        <th>Toplam Stok</th>
                        <th>Stok Farkı</th>
                    `;
                    
                    location.reload();
                }
            }

            function exportToPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');
                const raporTuru = document.getElementById('rapor-filter').value;
                
                doc.addFont('https://cdn.jsdelivr.net/npm/roboto-font@0.1.0/fonts/Roboto/roboto-regular-webfont.ttf', 'Roboto', 'normal');
                doc.setFont('Roboto');

                doc.setFontSize(16);
                const baslik = raporTuru === 'ozet' ? 'Tedarik Özet Raporu' : 'Tedarik Stok Durumu Raporu';
                doc.text(baslik, 14, 15);
                
                const today = new Date();
                doc.setFontSize(10);
                doc.text('Rapor Tarihi: ' + today.toLocaleDateString('tr-TR'), 14, 25);

                const tableData = [];
                const visibleRows = document.querySelectorAll('#stok-table-body tr:not([style*="display: none"])');
                
                visibleRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        const rowData = [];
                        cells.forEach(cell => {
                            rowData.push(cell.textContent.trim());
                        });
                        tableData.push(rowData);
                    }
                });

                const headers = raporTuru === 'ozet' 
                    ? ['Tedarik Firma Adı', 'Kategori', 'Toplam Stok']
                    : ['Tedarik Firma Adı', 'İl', 'Kategori', 'Ürün Sayısı', 'Toplam Stok', 'Stok Farkı'];

                doc.autoTable({
                    head: [headers],
                    body: tableData,
                    startY: 35,
                    styles: {
                        fontSize: 8,
                        cellPadding: 2,
                        font: 'Roboto',
                        fontStyle: 'normal'
                    },
                    headStyles: {
                        fillColor: [52, 58, 64],
                        textColor: 255,
                        fontSize: 9,
                        font: 'Roboto',
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: {
                        fillColor: [248, 249, 250]
                    },
                    margin: { top: 35, left: 14, right: 14 },
                    didDrawPage: function (data) {
                        doc.setFontSize(8);
                        doc.text('Sayfa ' + data.pageNumber, data.settings.margin.left, doc.internal.pageSize.height - 10);
                    }
                });

                const today_formatted = today.getDate().toString().padStart(2, '0') + '_' + 
                                    (today.getMonth() + 1).toString().padStart(2, '0') + '_' + 
                                    today.getFullYear();
                const dosyaAdi = raporTuru === 'ozet' ? 'ozet_raporu_' : 'stok_raporu_';
                doc.save(dosyaAdi + today_formatted + '.pdf');
            }

            function exportToExcel() {
                const wb = XLSX.utils.book_new();
                const raporTuru = document.getElementById('rapor-filter').value;
                
                const tableData = [];
                const headers = raporTuru === 'ozet' 
                    ? ['Tedarik Firma Adı', 'Kategori', 'Toplam Stok']
                    : ['Tedarik Firma Adı', 'İl', 'Kategori', 'Ürün Sayısı', 'Toplam Stok', 'Stok Farkı'];
                tableData.push(headers);

                const visibleRows = document.querySelectorAll('#stok-table-body tr:not([style*="display: none"])');
                
                visibleRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        const rowData = [];
                        cells.forEach(cell => {
                            rowData.push(cell.textContent.trim());
                        });
                        tableData.push(rowData);
                    }
                });

                const ws = XLSX.utils.aoa_to_sheet(tableData);
                
                const colWidths = raporTuru === 'ozet' 
                    ? [
                        { wch: 25 }, // Firma Adı
                        { wch: 20 }, // Kategori
                        { wch: 15 }  // Toplam Stok
                      ]
                    : [
                        { wch: 25 }, // Firma Adı
                        { wch: 15 }, // İl
                        { wch: 20 }, // Kategori
                        { wch: 12 }, // Ürün Sayısı
                        { wch: 15 }, // Toplam Stok
                        { wch: 12 }  // Stok Farkı
                      ];
                ws['!cols'] = colWidths;

                const sheetName = raporTuru === 'ozet' ? 'Özet Rapor' : 'Stok Raporu';
                XLSX.utils.book_append_sheet(wb, ws, sheetName);
                
                const today = new Date();
                const dosyaAdi = raporTuru === 'ozet' ? 'ozet_raporu_' : 'stok_raporu_';
                XLSX.writeFile(wb, dosyaAdi + today.toISOString().split('T')[0] + '.xlsx');
            }
        </script>
    </div>
  </div>
</div>
</body>
</html>