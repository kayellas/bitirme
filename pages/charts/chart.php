<?php include("header.php"); ?>
<?php 
    $conn = mysqli_connect('localhost', 'root', '', 'kds');
    if (!$conn) {
        die("Bağlantı başarısız: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8");
?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Executive Summary KPI Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary">
                    <div class="card-header border-0">
                        <h3 class="card-title text-white">
                            <i class="fas fa-chart-line mr-2"></i>
                            Swiss Hotel - Tedarik Zinciri Yönetimi
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light" id="lastUpdated">Son Güncelleme: <?php echo date('d.m.Y H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced KPI Cards Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3 id="totalSales" class="counter">-</h3>
                        <p><strong>Toplam Satış Miktarı</strong><br><small>Son 12 Ay</small></p>
                        <div class="progress mb-1" style="height: 3px;">
                            <div class="progress-bar bg-white" id="salesProgress" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="small-box-footer">
                        <span id="salesTrend" class="text-white-50">Trend Hesaplanıyor...</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3 id="totalRevenue" class="counter">-</h3>
                        <p><strong>Toplam Gelir</strong><br><small>₺ (Teslim Edilenler)</small></p>
                        <div class="progress mb-1" style="height: 3px;">
                            <div class="progress-bar bg-white" id="revenueProgress" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="small-box-footer">
                        <span id="revenueTrend" class="text-white-50">Trend Hesaplanıyor...</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-warning">
                    <div class="inner">
                        <h3 id="avgOrderValue" class="counter">-</h3>
                        <p><strong>Ortalama Sipariş Değeri</strong><br><small>₺ per Order</small></p>
                        <div class="progress mb-1" style="height: 3px;">
                            <div class="progress-bar bg-white" id="avgProgress" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="small-box-footer">
                        <span id="avgTrend" class="text-white-50">Trend Hesaplanıyor...</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-danger">
                    <div class="inner">
                        <h3 id="topCategory" class="counter">-</h3>
                        <p><strong>En Performanslı Kategori</strong><br><small>Gelir Bazında</small></p>
                        <div class="progress mb-1" style="height: 3px;">
                            <div class="progress-bar bg-white" id="categoryProgress" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="small-box-footer">
                        <span id="categoryShare" class="text-white-50">Market Payı: -%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary KPIs Row -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-gradient-purple">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Aktif Müşteri</span>
                        <span class="info-box-number" id="activeCustomers">-</span>
                        <div class="progress">
                            <div class="progress-bar" id="customerProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-gradient-teal">
                    <span class="info-box-icon"><i class="fas fa-box"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Toplam Ürün</span>
                        <span class="info-box-number" id="totalProducts">-</span>
                        <div class="progress">
                            <div class="progress-bar" id="productProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-gradient-orange">
                    <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tedarikçi</span>
                        <span class="info-box-number" id="suppliers">-</span>
                        <div class="progress">
                            <div class="progress-bar" id="supplierProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-gradient-pink">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Bekleyen Sipariş</span>
                        <span class="info-box-number" id="pendingOrders">-</span>
                        <div class="progress">
                            <div class="progress-bar" id="pendingProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-gradient-cyan">
                    <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Teslim Oranı</span>
                        <span class="info-box-number" id="deliveryRate">-%</span>
                        <div class="progress">
                            <div class="progress-bar" id="deliveryProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-gradient-lime">
                    <span class="info-box-icon"><i class="fas fa-chart-area"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Büyüme Oranı</span>
                        <span class="info-box-number" id="growthRate">-%</span>
                        <div class="progress">
                            <div class="progress-bar" id="growthProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filter Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter mr-2"></i>
                            Gelişmiş Analiz Filtreleri
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="filterCategory">Kategori:</label>
                                <select id="filterCategory" class="form-control select2" onchange="applyFilters()">
                                    <option value="all">Tüm Kategoriler</option>
                                    <option value="1">Soft Drinks</option>
                                    <option value="2">Sıcak İçecekler</option>
                                    <option value="3">Alkollü İçecekler</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterYear">Yıl:</label>
                                <select id="filterYear" class="form-control select2" onchange="applyFilters()">
                                    <option value="all">Tüm Yıllar</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterQuarter">Çeyrek:</label>
                                <select id="filterQuarter" class="form-control select2" onchange="applyFilters()">
                                    <option value="all">Tüm Çeyrekler</option>
                                    <option value="1">Q1 (Ocak-Mart)</option>
                                    <option value="2">Q2 (Nisan-Haziran)</option>
                                    <option value="3">Q3 (Temmuz-Eylül)</option>
                                    <option value="4">Q4 (Ekim-Aralık)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="startDate">Başlangıç:</label>
                                <input type="date" id="startDate" class="form-control" onchange="applyFilters()">
                            </div>
                            <div class="col-md-2">
                                <label for="endDate">Bitiş:</label>
                                <input type="date" id="endDate" class="form-control" onchange="applyFilters()">
                            </div>
                            <div class="col-md-2">
                                <label for="filterStatus">Sipariş Durumu:</label>
                                <select id="filterStatus" class="form-control select2" onchange="applyFilters()">
                                    <option value="all">Tüm Durumlar</option>
                                    <option value="teslim_edildi">Teslim Edilenler</option>
                                    <option value="kargoda">Kargoda</option>
                                    <option value="onaylandi">Onaylananlar</option>
                                    <option value="beklemede">Bekleyenler</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-primary btn-lg" onclick="applyFilters()">
                                    <i class="fas fa-search"></i> Analiz Et
                                </button>
                                <button class="btn btn-secondary ml-2" onclick="resetFilters()">
                                    <i class="fas fa-undo"></i> Sıfırla
                                </button>
                                <button class="btn btn-success ml-2" onclick="exportData()">
                                    <i class="fas fa-file-excel"></i> Excel Raporu
                                </button>
                                <button class="btn btn-info ml-2" onclick="exportToPDF()">
                                    <i class="fas fa-file-pdf"></i> PDF Raporu
                                </button>
                                <button class="btn btn-warning ml-2" onclick="scheduleReport()">
                                    <i class="fas fa-calendar"></i> Otomatik Rapor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Charts Row 1 -->
        <div class="row mb-4">
            <!-- Revenue & Profitability Trend -->
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-area mr-2"></i>
                            Gelir ve Karlılık Trend Analizi
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-tool dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="changeChartType('salesTrendChart', 'line')">Çizgi Grafik</a>
                                    <a class="dropdown-item" href="#" onclick="changeChartType('salesTrendChart', 'bar')">Çubuk Grafik</a>
                                    <a class="dropdown-item" href="#" onclick="changeChartType('salesTrendChart', 'area')">Alan Grafik</a>
                                </div>
                            </div>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-success">
                                            <i class="fas fa-caret-up"></i> <span id="revenueGrowth">0%</span>
                                        </span>
                                        <h5 class="description-header" id="currentRevenue">₺0</h5>
                                        <span class="description-text">Bu Ay Gelir</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-warning">
                                            <i class="fas fa-chart-line"></i> <span id="profitMargin">0%</span>
                                        </span>
                                        <h5 class="description-header" id="avgMargin">%0</h5>
                                        <span class="description-text">Ortalama Marj</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-info">
                                            <i class="fas fa-users"></i> <span id="customerRetention">0%</span>
                                        </span>
                                        <h5 class="description-header" id="repeatCustomers">0</h5>
                                        <span class="description-text">Tekrar Eden Müşteri</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="description-block">
                                        <span class="description-percentage text-danger">
                                            <i class="fas fa-clock"></i> <span id="avgDeliveryTime">0</span>
                                        </span>
                                        <h5 class="description-header" id="deliveryDays">0 gün</h5>
                                        <span class="description-text">Ort. Teslimat</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Performance -->
            <div class="col-lg-4">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Kategori Performans Analizi
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <div id="categoryLegend" class="category-legend">
                                <!-- Dynamic legend will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Charts Row 2 -->
        <div class="row mb-4">
            <!-- Regional Performance -->
            <div class="col-lg-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Bölgesel Satış Performansı
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="regionalChart" style="height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products Performance -->
            <div class="col-lg-6">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-star mr-2"></i>
                            En Performanslı Ürünler (Top 10)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="topProductsChart" style="height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Charts Row 3 -->
        <div class="row mb-4">
            <!-- Financial Performance -->
            <div class="col-lg-6">
                <div class="card card-purple card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            Finansal Performans Dashboard
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="financialChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Supply Chain Metrics -->
            <div class="col-lg-3">
                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-industry mr-2"></i>
                            Tedarik Zinciri Metrikleri
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="supplyChainChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Compact Performance Table -->
            <div class="col-lg-3">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-2"></i>
                            Performans Özeti
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2" style="height: 400px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table id="performanceTable" class="table table-sm table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="font-size: 0.75rem;">Metrik</th>
                                        <th style="font-size: 0.75rem;">2024</th>
                                        <th style="font-size: 0.75rem;">2025</th>
                                        <th style="font-size: 0.75rem;">Değişim</th>
                                        <th style="font-size: 0.75rem;"></th>
                                    </tr>
                                </thead>
                                <tbody id="performanceTableBody">
                                    <!-- Dynamic data will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Additional KPI Summary Cards -->
                        <div class="mt-3">
                            <div class="small-box bg-gradient-success mb-2" style="border-radius: 8px;">
                                <div class="inner p-2">
                                    <h6 class="mb-1" style="font-size: 0.8rem;">Toplam Büyüme</h6>
                                    <h4 id="totalGrowthRate" style="font-size: 1.2rem;">+12.5%</h4>
                                </div>
                                <div class="icon" style="top: 5px; right: 5px;">
                                    <i class="fas fa-arrow-up" style="font-size: 1rem;"></i>
                                </div>
                            </div>
                            
                            <div class="small-box bg-gradient-info mb-2" style="border-radius: 8px;">
                                <div class="inner p-2">
                                    <h6 class="mb-1" style="font-size: 0.8rem;">En İyi Çeyrek</h6>
                                    <h4 style="font-size: 1.2rem;">Q1 2025</h4>
                                </div>
                                <div class="icon" style="top: 5px; right: 5px;">
                                    <i class="fas fa-trophy" style="font-size: 1rem;"></i>
                                </div>
                            </div>
                            
                            <div class="small-box bg-gradient-warning mb-2" style="border-radius: 8px;">
                                <div class="inner p-2">
                                    <h6 class="mb-1" style="font-size: 0.8rem;">Hedef Başarı</h6>
                                    <h4 style="font-size: 1.2rem;">87%</h4>
                                </div>
                                <div class="icon" style="top: 5px; right: 5px;">
                                    <i class="fas fa-bullseye" style="font-size: 1rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Year-over-Year Comparison -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Yıllık Karşılaştırmalı Performans Analizi (YoY)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="comparativeChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Summary Table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-light">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-2"></i>
                            Executive Summary - Anahtar Performans Göstergeleri
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="executiveTable" class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>KPI</th>
                                        <th>Mevcut Değer</th>
                                        <th>Önceki Dönem</th>
                                        <th>Değişim</th>
                                        <th>Hedef</th>
                                        <th>Performans</th>
                                        <th>Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody id="executiveTableBody">
                                    <!-- Dynamic data will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Yükleniyor...</span>
                </div>
                <h5>Analiz Gerçekleştiriliyor...</h5>
                <p class="text-muted">Lütfen bekleyiniz, veriler işleniyor.</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="loadingProgress" 
                         style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <strong>FATIMA ZEYNEP KAYA &copy; 2023-2025</strong>
    <div class="float-right d-none d-sm-inline-block">
        <b> Dashboard</b> v3.0.0
    </div>
</footer>

<!-- Chart.js and Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<style>
/* Custom CSS for Professional Dashboard */
.small-box .inner h3.counter {
    font-size: 2.2rem;
    font-weight: 700;
}

.info-box {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.chart-container {
    position: relative;
    margin: auto;
}

.category-legend {
    max-height: 200px;
    overflow-y: auto;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
    padding: 3px;
    border-radius: 3px;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.progress {
    height: 4px;
}

.bg-gradient-purple {
    background: linear-gradient(45deg, #6f42c1, #007bff) !important;
}

.bg-gradient-teal {
    background: linear-gradient(45deg, #20c997, #17a2b8) !important;
}

.bg-gradient-orange {
    background: linear-gradient(45deg, #fd7e14, #ffc107) !important;
}

.bg-gradient-pink {
    background: linear-gradient(45deg, #e83e8c, #dc3545) !important;
}

.bg-gradient-cyan {
    background: linear-gradient(45deg, #17a2b8, #20c997) !important;
}

.bg-gradient-lime {
    background: linear-gradient(45deg, #28a745, #20c997) !important;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.trend-up {
    color: #28a745;
}

.trend-down {
    color: #dc3545;
}

.trend-neutral {
    color: #6c757d;
}
</style>

<script>
// Global chart variables
let salesTrendChart, categoryChart, regionalChart, topProductsChart, 
    financialChart, supplyChainChart, comparativeChart;

// Enhanced chart configurations
const advancedChartConfig = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                    size: 12,
                    weight: '500'
                }
            }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleColor: 'white',
            bodyColor: 'white',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
                title: function(context) {
                    return context[0].label || '';
                },
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        if (context.dataset.label.includes('₺') || context.dataset.label.includes('Gelir')) {
                            label += new Intl.NumberFormat('tr-TR', {
                                style: 'currency',
                                currency: 'TRY'
                            }).format(context.parsed.y);
                        } else {
                            label += new Intl.NumberFormat('tr-TR').format(context.parsed.y);
                        }
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: true,
                color: 'rgba(0,0,0,0.05)'
            },
            ticks: {
                font: {
                    size: 11
                }
            }
        },
        y: {
            grid: {
                display: true,
                color: 'rgba(0,0,0,0.05)'
            },
            beginAtZero: true,
            ticks: {
                font: {
                    size: 11
                },
                callback: function(value) {
                    return new Intl.NumberFormat('tr-TR').format(value);
                }
            }
        }
    },
    animation: {
        duration: 1500,
        easing: 'easeOutQuart'
    }
};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');
    initializeAdvancedCharts();
    setDefaultDates();
    loadDashboardData();
    setupAutoRefresh();
});

function setDefaultDates() {
    const today = new Date();
    const yearStart = new Date(today.getFullYear(), 0, 1);
    
    document.getElementById('startDate').value = yearStart.toISOString().split('T')[0];
    document.getElementById('endDate').value = today.toISOString().split('T')[0];
}

function initializeAdvancedCharts() {
    console.log('Initializing charts...');
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }

    // Sales Trend Chart with dual axis
    const salesCtx = document.getElementById('salesTrendChart');
    if (salesCtx) {
        salesTrendChart = new Chart(salesCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Satış Miktarı',
                    data: [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: 'Gelir (₺)',
                    data: [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: 'Ortalama Sipariş Değeri (₺)',
                    data: [],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...advancedChartConfig,
                scales: {
                    ...advancedChartConfig.scales,
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY',
                                    minimumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
        console.log('Sales trend chart created');
    }

    // Enhanced Category Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        categoryChart = new Chart(categoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${new Intl.NumberFormat('tr-TR').format(value)} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
        console.log('Category chart created');
    }

    // Regional Performance Chart
    const regionalCtx = document.getElementById('regionalChart');
    if (regionalCtx) {
        regionalChart = new Chart(regionalCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Satış Miktarı',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }, {
                    label: 'Gelir (₺)',
                    data: [],
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }]
            },
            options: {
                ...advancedChartConfig,
                scales: {
                    ...advancedChartConfig.scales,
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });
        console.log('Regional chart created');
    }

    // Top Products Chart
    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx) {
        topProductsChart = new Chart(topProductsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Satış Miktarı',
                    data: [],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#36A2EB'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                ...advancedChartConfig,
                indexAxis: 'y',
                plugins: {
                    ...advancedChartConfig.plugins,
                    legend: {
                        display: false
                    }
                }
            }
        });
        console.log('Top products chart created');
    }

    // Financial Performance Chart
    const financialCtx = document.getElementById('financialChart');
    if (financialCtx) {
        financialChart = new Chart(financialCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Aylık Gelir (₺)',
                    data: [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Aylık Maliyet (₺)',
                    data: [],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Net Kar (₺)',
                    data: [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: advancedChartConfig
        });
        console.log('Financial chart created');
    }

    // Supply Chain Metrics Chart
    const supplyCtx = document.getElementById('supplyChainChart');
    if (supplyCtx) {
        supplyChainChart = new Chart(supplyCtx.getContext('2d'), {
            type: 'radar',
            data: {
                labels: ['Teslimat Hızı', 'Stok Yönetimi', 'Tedarikçi Güvenilirliği', 'Maliyet Etkinliği', 'Kalite', 'Müşteri Memnuniyeti'],
                datasets: [{
                    label: 'Mevcut Performans',
                    data: [85, 70, 90, 75, 95, 80],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }, {
                    label: 'Hedef',
                    data: [90, 85, 95, 85, 98, 90],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                }
            }
        });
        console.log('Supply chain chart created');
    }

    // Comparative Chart
    const comparativeCtx = document.getElementById('comparativeChart');
    if (comparativeCtx) {
        comparativeChart = new Chart(comparativeCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                ...advancedChartConfig,
                plugins: {
                    ...advancedChartConfig.plugins,
                    title: {
                        display: true,
                        text: 'Yıllık Performans Karşılaştırması',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            }
        });
        console.log('Comparative chart created');
    }
    
    console.log('All charts initialized');
}

// loadDashboardData fonksiyonunu güncelle
function loadDashboardData() {
    console.log('Loading dashboard data...');
    showLoadingWithProgress();
    
    const filters = {
        category: document.getElementById('filterCategory').value,
        year: document.getElementById('filterYear').value,
        quarter: document.getElementById('filterQuarter').value,
        startDate: document.getElementById('startDate').value,
        endDate: document.getElementById('endDate').value,
        status: document.getElementById('filterStatus').value
    };

    console.log('Sending filters:', filters);

    // Simulate progressive loading
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 95) progress = 95;
        updateLoadingProgress(progress);
    }, 200);

    // Fetch data with enhanced error handling
    fetch('dashboard_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filters)
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text(); // First get as text to debug
    })
    .then(text => {
        console.log('Raw response (first 500 chars):', text.substring(0, 500));
        try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            return data;
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Full response:', text);
            throw new Error('Invalid JSON response');
        }
    })
    .then(data => {
        clearInterval(progressInterval);
        updateLoadingProgress(100);
        
        if (data.status === 'error') {
            throw new Error(data.message || 'Unknown error');
        }
        
        setTimeout(() => {
            console.log('Updating charts with data:', data);
            updateAllCharts(data);
            updateKPIs(data.kpis);
            updateExecutiveTable(data.executive);
            updatePerformanceTable(data.performance);
            hideLoading();
            
            // Update last updated time
            document.getElementById('lastUpdated').textContent = 
                'Son Güncelleme: ' + new Date().toLocaleString('tr-TR');
        }, 500);
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Dashboard data loading error:', error);
        hideLoading();
        showErrorMessage('Veri yüklenirken bir hata oluştu: ' + error.message);
    });
}

function showLoadingWithProgress() {
    $('#loadingModal').modal('show');
    updateLoadingProgress(0);
}

function updateLoadingProgress(percentage) {
    const progressBar = document.getElementById('loadingProgress');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

// updateAllCharts fonksiyonunu güncelle
function updateAllCharts(data) {
    console.log('Updating all charts with data:', data);
    
    // Update Sales Trend Chart
    if (data.salesTrend && salesTrendChart) {
        console.log('Updating sales trend chart with:', data.salesTrend);
        salesTrendChart.data.labels = data.salesTrend.labels || [];
        salesTrendChart.data.datasets[0].data = data.salesTrend.sales || [];
        salesTrendChart.data.datasets[1].data = data.salesTrend.revenue || [];
        salesTrendChart.data.datasets[2].data = data.salesTrend.avgOrderValue || [];
        salesTrendChart.update('active');
        console.log('Sales trend chart updated');
    } else {
        console.warn('Sales trend data or chart missing');
    }

    // Update Category Chart
    if (data.categoryDistribution && categoryChart) {
        console.log('Updating category chart with:', data.categoryDistribution);
        categoryChart.data.labels = data.categoryDistribution.labels || [];
        categoryChart.data.datasets[0].data = data.categoryDistribution.data || [];
        categoryChart.update('active');
        updateCategoryLegend(data.categoryDistribution);
        console.log('Category chart updated');
    } else {
        console.warn('Category data or chart missing');
    }

    // Update Regional Chart
    if (data.regionalPerformance && regionalChart) {
        console.log('Updating regional chart with:', data.regionalPerformance);
        regionalChart.data.labels = data.regionalPerformance.labels || [];
        regionalChart.data.datasets[0].data = data.regionalPerformance.sales || [];
        regionalChart.data.datasets[1].data = data.regionalPerformance.revenue || [];
        regionalChart.update('active');
        console.log('Regional chart updated');
    } else {
        console.warn('Regional data or chart missing');
    }

    // Update Top Products Chart
    if (data.topProducts && topProductsChart) {
        console.log('Updating top products chart with:', data.topProducts);
        topProductsChart.data.labels = data.topProducts.labels || [];
        topProductsChart.data.datasets[0].data = data.topProducts.data || [];
        topProductsChart.update('active');
        console.log('Top products chart updated');
    } else {
        console.warn('Top products data or chart missing');
    }

    // Update Financial Chart
    if (data.financial && financialChart) {
        console.log('Updating financial chart with:', data.financial);
        financialChart.data.labels = data.financial.labels || [];
        financialChart.data.datasets[0].data = data.financial.revenue || [];
        financialChart.data.datasets[1].data = data.financial.costs || [];
        financialChart.data.datasets[2].data = data.financial.profit || [];
        financialChart.update('active');
        console.log('Financial chart updated');
    } else {
        console.warn('Financial data or chart missing');
    }

    // Update Comparative Chart
    if (data.comparative && comparativeChart) {
        console.log('Updating comparative chart with:', data.comparative);
        comparativeChart.data.labels = data.comparative.labels || [];
        comparativeChart.data.datasets = data.comparative.datasets || [];
        comparativeChart.update('active');
        console.log('Comparative chart updated');
    } else {
        console.warn('Comparative data or chart missing');
    }
    
    // Update additional summary metrics
    if (data.salesTrend && data.salesTrend.revenue && data.salesTrend.revenue.length > 0) {
        const currentMonthRevenue = data.salesTrend.revenue[data.salesTrend.revenue.length - 1];
        const prevMonthRevenue = data.salesTrend.revenue[data.salesTrend.revenue.length - 2] || currentMonthRevenue;
        const revenueGrowth = prevMonthRevenue > 0 ? ((currentMonthRevenue - prevMonthRevenue) / prevMonthRevenue * 100) : 0;
        
        const currentRevenueEl = document.getElementById('currentRevenue');
        if (currentRevenueEl) {
            currentRevenueEl.textContent = formatCurrency(currentMonthRevenue);
        }
        
        const revenueGrowthEl = document.getElementById('revenueGrowth');
        if (revenueGrowthEl) {
            revenueGrowthEl.textContent = (revenueGrowth > 0 ? '+' : '') + revenueGrowth.toFixed(1) + '%';
        }
    }
    
    console.log('All charts update completed');
}

// updateKPIs fonksiyonunu güncelle
function updateKPIs(kpis) {
    if (!kpis) {
        console.error('No KPI data received');
        return;
    }
    
    console.log('Updating KPIs:', kpis);
    
    // Animate counter updates
    animateCounter('totalSales', kpis.totalSales || 0);
    animateCounter('totalRevenue', kpis.totalRevenue || 0, true);
    animateCounter('avgOrderValue', kpis.avgOrderValue || 0, true);
    
    // Update text content
    const topCategoryElement = document.getElementById('topCategory');
    if (topCategoryElement) {
        topCategoryElement.textContent = kpis.topCategory || '-';
    }
    
    // Update category share
    const categoryShareElement = document.getElementById('categoryShare');
    if (categoryShareElement) {
        categoryShareElement.textContent = `Market Payı: ${kpis.categoryShare || 0}%`;
    }
    
    // Update secondary KPIs
    animateCounter('activeCustomers', kpis.activeCustomers || 0);
    animateCounter('totalProducts', kpis.totalProducts || 0);
    animateCounter('suppliers', kpis.suppliers || 0);
    animateCounter('pendingOrders', kpis.pendingOrders || 0);
    
    // Update delivery metrics
    const deliveryRateElement = document.getElementById('deliveryRate');
    if (deliveryRateElement) {
        deliveryRateElement.textContent = (kpis.deliveryRate || 0).toFixed(1) + '%';
    }
    
    const deliveryDaysElement = document.getElementById('deliveryDays');
    if (deliveryDaysElement) {
        deliveryDaysElement.textContent = (kpis.avgDeliveryDays || 0).toFixed(1) + ' gün';
    }
    
    const growthRateElement = document.getElementById('growthRate');
    if (growthRateElement) {
        growthRateElement.textContent = (kpis.growthRate > 0 ? '+' : '') + (kpis.growthRate || 0).toFixed(1) + '%';
    }
    
    // Update progress bars with animation
    updateProgressBar('salesProgress', kpis.salesProgress || 0);
    updateProgressBar('revenueProgress', kpis.revenueProgress || 0);
    updateProgressBar('avgProgress', kpis.avgProgress || 0);
    updateProgressBar('categoryProgress', kpis.categoryProgress || 0);
    updateProgressBar('customerProgress', (kpis.activeCustomers / 50) * 100 || 0);
    updateProgressBar('productProgress', (kpis.totalProducts / 200) * 100 || 0);
    updateProgressBar('supplierProgress', (kpis.suppliers / 50) * 100 || 0);
    updateProgressBar('pendingProgress', (kpis.pendingOrders / 20) * 100 || 0);
    updateProgressBar('deliveryProgress', kpis.deliveryRate || 0);
    updateProgressBar('growthProgress', Math.abs(kpis.growthRate || 0));
    
    // Update trends
    updateTrend('salesTrend', kpis.salesTrend);
    updateTrend('revenueTrend', kpis.revenueTrend);
    updateTrend('avgTrend', kpis.avgTrend);
}

// animateCounter fonksiyonunu güncelle
function animateCounter(elementId, targetValue, isCurrency = false) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.warn(`Element with id '${elementId}' not found`);
        return;
    }
    
    if (targetValue === null || targetValue === undefined || isNaN(targetValue)) {
        element.textContent = isCurrency ? '₺0' : '0';
        return;
    }
    
    const startValue = 0;
    const duration = 2000;
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = startValue + (targetValue - startValue) * easeOutQuart(progress);
        
        if (isCurrency) {
            element.textContent = formatCurrency(currentValue);
        } else {
            element.textContent = formatNumber(currentValue);
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

function easeOutQuart(t) {
    return 1 - (--t) * t * t * t;
}

function updateProgressBar(elementId, percentage) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.width = Math.min(percentage, 100) + '%';
    }
}

function updateTrend(elementId, trendData) {
    const element = document.getElementById(elementId);
    if (!element || !trendData) return;
    
    const { value, direction } = trendData;
    const icon = direction === 'up' ? 'fa-arrow-up' : direction === 'down' ? 'fa-arrow-down' : 'fa-minus';
    const colorClass = direction === 'up' ? 'trend-up' : direction === 'down' ? 'trend-down' : 'trend-neutral';
    
    element.innerHTML = `<i class="fas ${icon} ${colorClass}"></i> ${value}`;
    element.className = `text-white-50 ${colorClass}`;
}

function updateCategoryLegend(categoryData) {
    const legendContainer = document.getElementById('categoryLegend');
    if (!legendContainer || !categoryData) return;
    
    let legendHTML = '';
    categoryData.labels.forEach((label, index) => {
        const color = categoryChart.data.datasets[0].backgroundColor[index];
        const value = categoryData.data[index];
        const total = categoryData.data.reduce((a, b) => a + b, 0);
        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
        
        legendHTML += `
            <div class="legend-item">
                <div class="legend-color" style="background-color: ${color}"></div>
                <span class="legend-text">
                    <strong>${label}</strong>: ${formatNumber(value)} (${percentage}%)
                </span>
            </div>
        `;
    });
    
    legendContainer.innerHTML = legendHTML;
}

function updateExecutiveTable(executiveData) {
    const tableBody = document.getElementById('executiveTableBody');
    if (!tableBody || !executiveData) return;
    
    let tableHTML = '';
    executiveData.forEach(item => {
        const changeClass = item.change > 0 ? 'trend-up' : item.change < 0 ? 'trend-down' : 'trend-neutral';
        const changeIcon = item.change > 0 ? 'fa-arrow-up' : item.change < 0 ? 'fa-arrow-down' : 'fa-minus';
        const performanceBar = Math.min((item.current / item.target) * 100, 100);
        const performanceClass = performanceBar >= 100 ? 'bg-success' : performanceBar >= 80 ? 'bg-warning' : 'bg-danger';
        
        tableHTML += `
            <tr>
                <td><strong>${item.kpi}</strong></td>
                <td>${item.currentFormatted}</td>
                <td>${item.previousFormatted}</td>
                <td class="${changeClass}">
                    <i class="fas ${changeIcon}"></i> ${item.changeFormatted}
                </td>
                <td>${item.targetFormatted}</td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${performanceClass}" style="width: ${performanceBar}%">
                            ${performanceBar.toFixed(0)}%
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge ${item.actionClass}">${item.action}</span>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = tableHTML;
}

function updatePerformanceTable(performanceData) {
    const tableBody = document.getElementById('performanceTableBody');
    if (!tableBody || !performanceData) return;
    
    let tableHTML = '';
    // Sadece ilk 4 metriği göster ve kompakt format kullan
    const limitedData = performanceData.slice(0, 4);
    
    limitedData.forEach(item => {
        const trendClass = item.trend > 0 ? 'trend-up' : item.trend < 0 ? 'trend-down' : 'trend-neutral';
        const trendIcon = item.trend > 0 ? 'fa-arrow-up' : item.trend < 0 ? 'fa-arrow-down' : 'fa-minus';
        
        // Metrik adını kısalt
        let shortMetric = item.metric;
        if (shortMetric.includes('Toplam Gelir')) shortMetric = 'Gelir';
        if (shortMetric.includes('Satış Miktarı')) shortMetric = 'Satış';
        if (shortMetric.includes('Müşteri Sayısı')) shortMetric = 'Müşteri';
        if (shortMetric.includes('Ortalama Sipariş')) shortMetric = 'Ort. Sipariş';
        
        tableHTML += `
            <tr style="font-size: 0.8rem;">
                <td><strong>${shortMetric}</strong></td>
                <td>${item.year2024}</td>
                <td>${item.year2025}</td>
                <td class="${trendClass}" style="font-weight: bold;">${item.yoyChange}</td>
                <td class="${trendClass}" style="text-align: center;">
                    <i class="fas ${trendIcon}"></i>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = tableHTML;
}

function formatNumber(num) {
    return new Intl.NumberFormat('tr-TR').format(Math.round(num));
}

function formatCurrency(num) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 0
    }).format(Math.round(num));
}

function applyFilters() {
    loadDashboardData();
}

function resetFilters() {
    document.getElementById('filterCategory').value = 'all';
    document.getElementById('filterYear').value = 'all';
    document.getElementById('filterQuarter').value = 'all';
    document.getElementById('filterStatus').value = 'all';
    setDefaultDates();
    loadDashboardData();
}

function exportData() {
    const filters = {
        category: document.getElementById('filterCategory').value,
        year: document.getElementById('filterYear').value,
        quarter: document.getElementById('filterQuarter').value,
        startDate: document.getElementById('startDate').value,
        endDate: document.getElementById('endDate').value,
        status: document.getElementById('filterStatus').value
    };

    const queryString = new URLSearchParams(filters).toString();
    window.location.href = `export_data.php?${queryString}&format=excel`;
}

function exportToPDF() {
    const filters = {
        category: document.getElementById('filterCategory').value,
        year: document.getElementById('filterYear').value,
        quarter: document.getElementById('filterQuarter').value,
        startDate: document.getElementById('startDate').value,
        endDate: document.getElementById('endDate').value,
        status: document.getElementById('filterStatus').value
    };

    const queryString = new URLSearchParams(filters).toString();
    window.location.href = `export_data.php?${queryString}&format=pdf`;
}

function scheduleReport() {
    alert('Otomatik rapor planlama özelliği yakında aktif olacak.');
}

function changeChartType(chartId, newType) {
    const chart = window[chartId];
    if (chart) {
        chart.config.type = newType;
        chart.update();
    }
}

// showErrorMessage fonksiyonunu güncelle
function showErrorMessage(message) {
    console.error('Error:', message);
    
    // Create a better error notification
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Hata!</strong> ${message}
    `;
    
    // Insert at the top of content
    const contentSection = document.querySelector('.content');
    if (contentSection) {
        contentSection.insertBefore(alertDiv, contentSection.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    } else {
        alert(message);
    }
}

function setupAutoRefresh() {
    // Auto-refresh every 5 minutes for real-time data
    setInterval(function() {
        if (document.getElementById('filterCategory').value === 'all' && 
            document.getElementById('filterYear').value === 'all') {
            loadDashboardData();
        }
    }, 300000);
}

// Add print functionality
function printDashboard() {
    window.print();
}

// Add full screen functionality
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

</script>

<?php include("footer.php"); ?>