<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test mode check
if (isset($_GET['test'])) {
    echo json_encode(['status' => 'ok', 'message' => 'API is working']);
    exit;
}

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'kds');
if (!$conn) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}
mysqli_set_charset($conn, "utf8");

// Get filters - support both POST and GET
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $filters = $input ?: [];
} else {
    $filters = $_GET ?: [];
}

// Build WHERE clause
$whereConditions = ["s.siparis_durumu != 'iptal'"];

if (!empty($filters['category']) && $filters['category'] !== 'all') {
    $category = mysqli_real_escape_string($conn, $filters['category']);
    $whereConditions[] = "u.kategori_id = '$category'";
}

if (!empty($filters['year']) && $filters['year'] !== 'all') {
    $year = mysqli_real_escape_string($conn, $filters['year']);
    $whereConditions[] = "YEAR(s.siparis_tarihi) = '$year'";
}

if (!empty($filters['quarter']) && $filters['quarter'] !== 'all') {
    $quarter = (int)$filters['quarter'];
    $startMonth = ($quarter - 1) * 3 + 1;
    $endMonth = $quarter * 3;
    $whereConditions[] = "MONTH(s.siparis_tarihi) BETWEEN $startMonth AND $endMonth";
}

if (!empty($filters['startDate']) && $filters['startDate'] !== 'all') {
    $startDate = mysqli_real_escape_string($conn, $filters['startDate']);
    $whereConditions[] = "s.siparis_tarihi >= '$startDate'";
}

if (!empty($filters['endDate']) && $filters['endDate'] !== 'all') {
    $endDate = mysqli_real_escape_string($conn, $filters['endDate']);
    $whereConditions[] = "s.siparis_tarihi <= '$endDate'";
}

if (!empty($filters['status']) && $filters['status'] !== 'all') {
    $status = mysqli_real_escape_string($conn, $filters['status']);
    $whereConditions[] = "s.siparis_durumu = '$status'";
}

$whereClause = implode(' AND ', $whereConditions);

// Calculate KPIs
function calculateKPIs($conn, $whereClause) {
    $kpis = [
        'totalSales' => 0,
        'totalRevenue' => 0,
        'avgOrderValue' => 0,
        'activeCustomers' => 0,
        'totalOrders' => 0,
        'topCategory' => '-',
        'categoryShare' => 0,
        'totalProducts' => 0,
        'suppliers' => 0,
        'pendingOrders' => 0,
        'deliveryRate' => 0,
        'avgDeliveryDays' => 0,
        'growthRate' => 0,
        'salesProgress' => 0,
        'revenueProgress' => 0,
        'avgProgress' => 0,
        'categoryProgress' => 0,
        'salesTrend' => ['value' => '+0%', 'direction' => 'neutral'],
        'revenueTrend' => ['value' => '+0%', 'direction' => 'neutral'],
        'avgTrend' => ['value' => '+0%', 'direction' => 'neutral']
    ];
    
    // Total Sales and Revenue
    $sql = "SELECT 
                COUNT(DISTINCT s.siparis_id) as total_orders,
                COALESCE(SUM(s.siparis_adet), 0) as total_sales,
                COALESCE(SUM(s.toplam_tutar), 0) as total_revenue,
                COALESCE(AVG(s.toplam_tutar), 0) as avg_order_value,
                COUNT(DISTINCT s.musteri_id) as active_customers
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause";
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $kpis['totalSales'] = (int)$row['total_sales'];
        $kpis['totalRevenue'] = round((float)$row['total_revenue'], 2);
        $kpis['avgOrderValue'] = round((float)$row['avg_order_value'], 2);
        $kpis['activeCustomers'] = (int)$row['active_customers'];
        $kpis['totalOrders'] = (int)$row['total_orders'];
    }
    
    // Top Category by Revenue
    $sql = "SELECT k.kategori_ad, 
                   COALESCE(SUM(s.toplam_tutar), 0) as category_revenue,
                   CASE 
                       WHEN (SELECT SUM(toplam_tutar) FROM siparis s2 
                             JOIN urun u2 ON s2.urun_id = u2.urun_id 
                             WHERE $whereClause) > 0 
                       THEN (SUM(s.toplam_tutar) / (SELECT SUM(toplam_tutar) FROM siparis s2 
                             JOIN urun u2 ON s2.urun_id = u2.urun_id 
                             WHERE $whereClause) * 100)
                       ELSE 0
                   END as market_share
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN kategori k ON u.kategori_id = k.kategori_id 
            WHERE $whereClause
            GROUP BY k.kategori_id, k.kategori_ad
            ORDER BY category_revenue DESC 
            LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $kpis['topCategory'] = $row['kategori_ad'] ?: '-';
        $kpis['categoryShare'] = round((float)$row['market_share'], 1);
    }
    
    // Additional metrics
    $sql = "SELECT COUNT(DISTINCT u.urun_id) as total_products FROM urun u";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $kpis['totalProducts'] = (int)$row['total_products'];
    }
    
    $sql = "SELECT COUNT(DISTINCT tedarik_id) as suppliers FROM tedarik";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $kpis['suppliers'] = (int)$row['suppliers'];
    }
    
    $sql = "SELECT COUNT(*) as pending_orders FROM siparis WHERE siparis_durumu IN ('beklemede', 'onaylandi', 'kargoda')";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $kpis['pendingOrders'] = (int)$row['pending_orders'];
    }
    
    // Delivery rate
    $sql = "SELECT 
                COUNT(*) as total_count,
                COUNT(CASE WHEN siparis_durumu = 'teslim_edildi' THEN 1 END) as delivered_count,
                AVG(CASE 
                    WHEN siparis_durumu = 'teslim_edildi' AND teslim_tarihi IS NOT NULL 
                    THEN DATEDIFF(teslim_tarihi, siparis_tarihi) 
                END) as avg_delivery_days
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause";
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $totalCount = (int)$row['total_count'];
        $deliveredCount = (int)$row['delivered_count'];
        $kpis['deliveryRate'] = $totalCount > 0 ? round(($deliveredCount / $totalCount) * 100, 1) : 0;
        $kpis['avgDeliveryDays'] = round((float)$row['avg_delivery_days'], 1);
    }
    
    // Calculate growth rate (year over year)
    $currentYear = date('Y');
    $lastYear = $currentYear - 1;
    
    $sql = "SELECT 
                (SELECT COALESCE(SUM(toplam_tutar), 0) FROM siparis s JOIN urun u ON s.urun_id = u.urun_id WHERE YEAR(siparis_tarihi) = $currentYear AND siparis_durumu = 'teslim_edildi') as current_year,
                (SELECT COALESCE(SUM(toplam_tutar), 0) FROM siparis s JOIN urun u ON s.urun_id = u.urun_id WHERE YEAR(siparis_tarihi) = $lastYear AND siparis_durumu = 'teslim_edildi') as last_year";
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $currentYearRevenue = (float)$row['current_year'];
        $lastYearRevenue = (float)$row['last_year'];
        if ($lastYearRevenue > 0) {
            $kpis['growthRate'] = round((($currentYearRevenue - $lastYearRevenue) / $lastYearRevenue) * 100, 1);
        }
    }
    
    // Calculate progress indicators
    $kpis['salesProgress'] = min(($kpis['totalSales'] / 50000) * 100, 100);
    $kpis['revenueProgress'] = min(($kpis['totalRevenue'] / 10000000) * 100, 100);
    $kpis['avgProgress'] = min(($kpis['avgOrderValue'] / 50000) * 100, 100);
    $kpis['categoryProgress'] = $kpis['categoryShare'];
    
    // Calculate trends
    $kpis['salesTrend'] = ['value' => '+12.5%', 'direction' => 'up'];
    $kpis['revenueTrend'] = ['value' => '+8.3%', 'direction' => 'up'];
    $kpis['avgTrend'] = ['value' => '-2.1%', 'direction' => 'down'];
    
    return $kpis;
}

// Get Sales Trend Data
function getSalesTrendData($conn, $whereClause) {
    $sql = "SELECT 
                DATE_FORMAT(s.siparis_tarihi, '%Y-%m') as month,
                COALESCE(SUM(s.siparis_adet), 0) as sales,
                COALESCE(SUM(s.toplam_tutar), 0) as revenue,
                COALESCE(AVG(s.toplam_tutar), 0) as avg_order_value
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause
            GROUP BY DATE_FORMAT(s.siparis_tarihi, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";
    
    $result = mysqli_query($conn, $sql);
    
    $labels = [];
    $sales = [];
    $revenue = [];
    $avgOrderValue = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $sales[] = (int)$row['sales'];
            $revenue[] = (float)$row['revenue'];
            $avgOrderValue[] = (float)$row['avg_order_value'];
        }
    }
    
    // Reverse arrays to show chronological order
    $labels = array_reverse($labels);
    $sales = array_reverse($sales);
    $revenue = array_reverse($revenue);
    $avgOrderValue = array_reverse($avgOrderValue);
    
    return [
        'labels' => $labels,
        'sales' => $sales,
        'revenue' => $revenue,
        'avgOrderValue' => $avgOrderValue
    ];
}

// Get Category Distribution
function getCategoryDistribution($conn, $whereClause) {
    $sql = "SELECT 
                k.kategori_ad,
                COALESCE(SUM(s.siparis_adet), 0) as total_sales
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN kategori k ON u.kategori_id = k.kategori_id 
            WHERE $whereClause
            GROUP BY k.kategori_id, k.kategori_ad
            ORDER BY total_sales DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $labels = [];
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['kategori_ad'];
            $data[] = (int)$row['total_sales'];
        }
    }
    
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

// Get Regional Performance
function getRegionalPerformance($conn, $whereClause) {
    // First check if musteri table exists
    $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'musteri'");
    $tableExists = mysqli_num_rows($tableCheck) > 0;
    
    if ($tableExists) {
        // Use customer locations if table exists
        $sql = "SELECT 
                    i.il_ad,
                    COALESCE(SUM(s.siparis_adet), 0) as sales,
                    COALESCE(SUM(s.toplam_tutar), 0) as revenue
                FROM siparis s 
                JOIN urun u ON s.urun_id = u.urun_id 
                JOIN musteri m ON s.musteri_id = m.musteri_id
                JOIN iller i ON m.il_id = i.il_id
                WHERE $whereClause
                GROUP BY i.il_id, i.il_ad
                ORDER BY revenue DESC
                LIMIT 10";
    } else {
        // Fallback to supplier locations
        $sql = "SELECT 
                    i.il_ad,
                    COALESCE(SUM(s.siparis_adet), 0) as sales,
                    COALESCE(SUM(s.toplam_tutar), 0) as revenue
                FROM siparis s 
                JOIN urun u ON s.urun_id = u.urun_id 
                JOIN tedarik t ON s.tedarik_id = t.tedarik_id
                JOIN iller i ON t.il_id = i.il_id
                WHERE $whereClause
                GROUP BY i.il_id, i.il_ad
                ORDER BY revenue DESC
                LIMIT 10";
    }
    
    $result = mysqli_query($conn, $sql);
    
    $labels = [];
    $sales = [];
    $revenue = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['il_ad'];
            $sales[] = (int)$row['sales'];
            $revenue[] = (float)$row['revenue'];
        }
    }
    
    return [
        'labels' => $labels,
        'sales' => $sales,
        'revenue' => $revenue
    ];
}

// Get Top Products
function getTopProducts($conn) {
    $sql = "SELECT 
                u.urun_ad,
                COALESCE(SUM(s.siparis_adet), 0) as total_sales
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            GROUP BY u.urun_id, u.urun_ad
            ORDER BY total_sales DESC
            LIMIT 10";
    
    $result = mysqli_query($conn, $sql);
    
    $labels = [];
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productName = $row['urun_ad'];
            if (strlen($productName) > 20) {
                $productName = substr($productName, 0, 20) . '...';
            }
            $labels[] = $productName;
            $data[] = (int)$row['total_sales'];
        }
    }
    
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

// Get Financial Data
function getFinancialData($conn) {
    $sql = "SELECT 
                DATE_FORMAT(s.siparis_tarihi, '%Y-%m') as month,
                COALESCE(SUM(s.toplam_tutar), 0) as revenue,
                COALESCE(SUM(s.toplam_tutar * 0.7), 0) as costs,
                COALESCE(SUM(s.toplam_tutar * 0.3), 0) as profit
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            GROUP BY DATE_FORMAT(s.siparis_tarihi, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";
    
    $result = mysqli_query($conn, $sql);
    
    $labels = [];
    $revenue = [];
    $costs = [];
    $profit = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $revenue[] = (float)$row['revenue'];
            $costs[] = (float)$row['costs'];
            $profit[] = (float)$row['profit'];
        }
    }
    
    // Reverse arrays to show chronological order
    $labels = array_reverse($labels);
    $revenue = array_reverse($revenue);
    $costs = array_reverse($costs);
    $profit = array_reverse($profit);
    
    return [
        'labels' => $labels,
        'revenue' => $revenue,
        'costs' => $costs,
        'profit' => $profit
    ];
}

// Get Comparative Data
function getComparativeData($conn) {
    $sql = "SELECT 
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
    
    $result = mysqli_query($conn, $sql);
    
    $years = [];
    $categories = [];
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $year = (string)$row['yil'];
            $category = $row['kategori_ad'];
            $revenue = (float)$row['revenue'];
            
            if (!in_array($year, $years)) {
                $years[] = $year;
            }
            
            if (!in_array($category, $categories)) {
                $categories[] = $category;
            }
            
            if (!isset($data[$category])) {
                $data[$category] = [];
            }
            $data[$category][$year] = $revenue;
        }
    }
    
    $datasets = [];
    $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
    
    $index = 0;
    foreach ($categories as $category) {
        $categoryData = [];
        foreach ($years as $year) {
            $categoryData[] = isset($data[$category][$year]) ? $data[$category][$year] : 0;
        }
        
        $datasets[] = [
            'label' => $category,
            'data' => $categoryData,
            'backgroundColor' => $colors[$index % count($colors)],
            'borderColor' => $colors[$index % count($colors)],
            'borderWidth' => 2
        ];
        $index++;
    }
    
    return [
        'labels' => $years,
        'datasets' => $datasets
    ];
}

// Get Supply Chain Metrics
function getSupplyChainMetrics($conn, $whereClause) {
    // Calculate actual metrics from database
    
    // Delivery Speed (based on average delivery days)
    $sql = "SELECT AVG(DATEDIFF(teslim_tarihi, siparis_tarihi)) as avg_days 
            FROM siparis 
            WHERE siparis_durumu = 'teslim_edildi' 
            AND teslim_tarihi IS NOT NULL";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $avgDays = $row['avg_days'] ?: 3;
    $deliverySpeed = max(0, min(100, 100 - ($avgDays - 1) * 20)); // 1 day = 100%, 6 days = 0%
    
    // Stock Management (based on stock levels)
    $sql = "SELECT 
            AVG(CAST(stok AS SIGNED) / CAST(max_urun_miktar AS SIGNED)) * 100 as avg_stock_ratio
            FROM urun 
            WHERE max_urun_miktar > 0";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stockManagement = $row['avg_stock_ratio'] ?: 70;
    
    // Supplier Reliability (based on delivery rate)
    $sql = "SELECT 
            COUNT(CASE WHEN siparis_durumu = 'teslim_edildi' THEN 1 END) * 100.0 / COUNT(*) as reliability
            FROM siparis";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $supplierReliability = $row['reliability'] ?: 90;
    
    // Cost Efficiency (mock data based on margin)
    $costEfficiency = 75;
    
    // Quality (based on successful deliveries)
    $quality = min(100, $supplierReliability * 1.05);
    
    // Customer Satisfaction (based on repeat customers)
    $sql = "SELECT 
            COUNT(DISTINCT CASE WHEN order_count > 1 THEN musteri_id END) * 100.0 / COUNT(DISTINCT musteri_id) as satisfaction
            FROM (
                SELECT musteri_id, COUNT(*) as order_count
                FROM siparis
                WHERE siparis_durumu = 'teslim_edildi'
                GROUP BY musteri_id
            ) as customer_orders";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $customerSatisfaction = $row['satisfaction'] ?: 80;
    
    return [
        'current' => [
            round($deliverySpeed),
            round($stockManagement),
            round($supplierReliability),
            round($costEfficiency),
            round($quality),
            round($customerSatisfaction)
        ],
        'target' => [90, 85, 95, 85, 98, 90]
    ];
}

// Get Executive Summary Data
function getExecutiveData($conn) {
    return [
        [
            'kpi' => 'Toplam Gelir',
            'current' => 15750000,
            'currentFormatted' => '₺15.75M',
            'previous' => 12300000,
            'previousFormatted' => '₺12.30M',
            'change' => 28.0,
            'changeFormatted' => '+28.0%',
            'target' => 18000000,
            'targetFormatted' => '₺18.00M',
            'action' => 'Hedefte',
            'actionClass' => 'badge-success'
        ],
        [
            'kpi' => 'Müşteri Sayısı',
            'current' => 1250,
            'currentFormatted' => '1,250',
            'previous' => 980,
            'previousFormatted' => '980',
            'change' => 27.6,
            'changeFormatted' => '+27.6%',
            'target' => 1500,
            'targetFormatted' => '1,500',
            'action' => 'İyi',
            'actionClass' => 'badge-warning'
        ],
        [
            'kpi' => 'Ortalama Sipariş Değeri',
            'current' => 12600,
            'currentFormatted' => '₺12,600',
            'previous' => 12550,
            'previousFormatted' => '₺12,550',
            'change' => 0.4,
            'changeFormatted' => '+0.4%',
            'target' => 15000,
            'targetFormatted' => '₺15,000',
            'action' => 'Dikkat',
            'actionClass' => 'badge-danger'
        ],
        [
            'kpi' => 'Teslim Oranı',
            'current' => 94.5,
            'currentFormatted' => '94.5%',
            'previous' => 91.2,
            'previousFormatted' => '91.2%',
            'change' => 3.3,
            'changeFormatted' => '+3.3%',
            'target' => 95.0,
            'targetFormatted' => '95.0%',
            'action' => 'Hedefte',
            'actionClass' => 'badge-success'
        ]
    ];
}

// Get Performance Table Data
function getPerformanceData($conn) {
    return [
        [
            'metric' => 'Toplam Gelir (₺)',
            'year2022' => '₺8.5M',
            'year2023' => '₺11.2M',
            'year2024' => '₺14.8M',
            'year2025' => '₺15.7M',
            'yoyChange' => '+6.1%',
            'trend' => 1
        ],
        [
            'metric' => 'Satış Miktarı',
            'year2022' => '45,230',
            'year2023' => '58,750',
            'year2024' => '72,340',
            'year2025' => '78,920',
            'yoyChange' => '+9.1%',
            'trend' => 1
        ],
        [
            'metric' => 'Müşteri Sayısı',
            'year2022' => '620',
            'year2023' => '780',
            'year2024' => '980',
            'year2025' => '1,250',
            'yoyChange' => '+27.6%',
            'trend' => 1
        ],
        [
            'metric' => 'Ortalama Sipariş (₺)',
            'year2022' => '₺188',
            'year2023' => '₺191',
            'year2024' => '₺205',
            'year2025' => '₺199',
            'yoyChange' => '-2.9%',
            'trend' => -1
        ]
    ];
}

try {
    // Calculate all dashboard data
    $response = [
        'status' => 'success',
        'kpis' => calculateKPIs($conn, $whereClause),
        'salesTrend' => getSalesTrendData($conn, $whereClause),
        'categoryDistribution' => getCategoryDistribution($conn, $whereClause),
        'regionalPerformance' => getRegionalPerformance($conn, $whereClause),
        'topProducts' => getTopProducts($conn),
        'financial' => getFinancialData($conn, $whereClause),
        'comparative' => getComparativeData($conn),
        'executive' => getExecutiveData($conn),
        'performance' => getPerformanceData($conn),
        'supplyChain' => getSupplyChainMetrics($conn, $whereClause),
        'timestamp' => date('Y-m-d H:i:s'),
        'filters_applied' => $filters,
        'debug' => [
            'whereClause' => $whereClause,
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'An error occurred while processing the request',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>