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

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Production'da 0 olmalı

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'kds');
if (!$conn) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'error' => 'Database connection failed: ' . mysqli_connect_error()]));
}
mysqli_set_charset($conn, "utf8");

// Get filters
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    if ($input) {
        $filters = json_decode($input, true) ?: [];
    }
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
    
    // Main KPIs
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
    
    // Top Category
    $sql2 = "SELECT k.kategori_ad, 
                    SUM(s.toplam_tutar) as category_revenue,
                    (SUM(s.toplam_tutar) / (SELECT SUM(toplam_tutar) FROM siparis s2 
                     JOIN urun u2 ON s2.urun_id = u2.urun_id WHERE $whereClause) * 100) as market_share
             FROM siparis s 
             JOIN urun u ON s.urun_id = u.urun_id 
             JOIN kategori k ON u.kategori_id = k.kategori_id 
             WHERE $whereClause
             GROUP BY k.kategori_id, k.kategori_ad
             ORDER BY category_revenue DESC 
             LIMIT 1";
    
    $result2 = mysqli_query($conn, $sql2);
    if ($result2 && $topCategory = mysqli_fetch_assoc($result2)) {
        $kpis['topCategory'] = $topCategory['kategori_ad'] ?? '-';
        $kpis['categoryShare'] = round((float)($topCategory['market_share'] ?? 0), 1);
    }
    
    // Additional counts
    $sql3 = "SELECT COUNT(DISTINCT urun_id) as count FROM urun";
    $result3 = mysqli_query($conn, $sql3);
    if ($result3 && $row = mysqli_fetch_assoc($result3)) {
        $kpis['totalProducts'] = (int)$row['count'];
    }
    
    $sql4 = "SELECT COUNT(DISTINCT tedarik_id) as count FROM tedarik";
    $result4 = mysqli_query($conn, $sql4);
    if ($result4 && $row = mysqli_fetch_assoc($result4)) {
        $kpis['suppliers'] = (int)$row['count'];
    }
    
    $sql5 = "SELECT COUNT(*) as count FROM siparis WHERE siparis_durumu IN ('beklemede', 'onaylandi', 'kargoda')";
    $result5 = mysqli_query($conn, $sql5);
    if ($result5 && $row = mysqli_fetch_assoc($result5)) {
        $kpis['pendingOrders'] = (int)$row['count'];
    }
    
    // Delivery stats
    $sql6 = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN siparis_durumu = 'teslim_edildi' THEN 1 END) as delivered,
        AVG(CASE WHEN siparis_durumu = 'teslim_edildi' AND teslim_tarihi IS NOT NULL 
            THEN DATEDIFF(teslim_tarihi, siparis_tarihi) END) as avg_days
        FROM siparis s JOIN urun u ON s.urun_id = u.urun_id WHERE $whereClause";
    
    $result6 = mysqli_query($conn, $sql6);
    if ($result6 && $deliveryStats = mysqli_fetch_assoc($result6)) {
        $deliveryRate = $deliveryStats['total'] > 0 ? ($deliveryStats['delivered'] / $deliveryStats['total']) * 100 : 0;
        $kpis['deliveryRate'] = round($deliveryRate, 1);
        $kpis['avgDeliveryDays'] = round((float)($deliveryStats['avg_days'] ?? 0), 1);
    }
    
    // Progress calculations
    $kpis['salesProgress'] = min(($kpis['totalSales'] / 10000) * 100, 100);
    $kpis['revenueProgress'] = min(($kpis['totalRevenue'] / 50000000) * 100, 100);
    $kpis['avgProgress'] = min(($kpis['avgOrderValue'] / 50000) * 100, 100);
    $kpis['categoryProgress'] = $kpis['categoryShare'];
    
    // Mock trend data
    $kpis['salesTrend'] = ['value' => '+12.5%', 'direction' => 'up'];
    $kpis['revenueTrend'] = ['value' => '+8.3%', 'direction' => 'up'];
    $kpis['avgTrend'] = ['value' => '+2.1%', 'direction' => 'up'];
    
    return $kpis;
}

// Get Sales Trend Data
function getSalesTrendData($conn, $whereClause) {
    $sql = "SELECT 
                DATE_FORMAT(s.siparis_tarihi, '%Y-%m') as month,
                SUM(s.siparis_adet) as sales,
                SUM(s.toplam_tutar) as revenue,
                AVG(s.toplam_tutar) as avg_order_value
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
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $data = array_reverse($data);
        
        foreach ($data as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $sales[] = (int)$row['sales'];
            $revenue[] = (float)$row['revenue'];
            $avgOrderValue[] = (float)$row['avg_order_value'];
        }
    }
    
    // Default data if empty
    if (empty($labels)) {
        $labels = ['Jan 2024', 'Feb 2024', 'Mar 2024'];
        $sales = [100, 150, 200];
        $revenue = [50000, 75000, 100000];
        $avgOrderValue = [500, 500, 500];
    }
    
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
            FROM kategori k
            LEFT JOIN urun u ON k.kategori_id = u.kategori_id
            LEFT JOIN siparis s ON u.urun_id = s.urun_id AND ($whereClause)
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
    $sql = "SELECT 
                i.il_ad,
                SUM(s.siparis_adet) as sales,
                SUM(s.toplam_tutar) as revenue
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN musteri m ON s.musteri_id = m.musteri_id
            JOIN iller i ON m.il_id = i.il_id
            WHERE $whereClause
            GROUP BY i.il_id, i.il_ad
            ORDER BY revenue DESC
            LIMIT 8";
    
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
function getTopProducts($conn, $whereClause) {
    $sql = "SELECT 
                u.urun_ad,
                SUM(s.siparis_adet) as total_sales
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause
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
function getFinancialData($conn, $whereClause) {
    $sql = "SELECT 
                DATE_FORMAT(s.siparis_tarihi, '%Y-%m') as month,
                SUM(s.toplam_tutar) as revenue
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause
            GROUP BY DATE_FORMAT(s.siparis_tarihi, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";
    
    $result = mysqli_query($conn, $sql);
    
    $labels = [];
    $revenue = [];
    $costs = [];
    $profit = [];
    
    if ($result) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $data = array_reverse($data);
        
        foreach ($data as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $monthRevenue = (float)$row['revenue'];
            $revenue[] = $monthRevenue;
            $costs[] = $monthRevenue * 0.65;
            $profit[] = $monthRevenue * 0.35;
        }
    }
    
    return [
        'labels' => $labels,
        'revenue' => $revenue,
        'costs' => $costs,
        'profit' => $profit
    ];
}

// Get Comparative Data - Comprehensive Hotel Performance Analysis
function getComparativeData($conn) {
    // Tüm otellerin kapasiteleri ve lokasyon bilgileri
    $allHotels = [
        'İstanbul' => ['name' => 'Swissôtel The Bosphorus', 'capacity' => 500, 'target_occupancy' => 85, 'country' => 'Türkiye'],
        'İzmir' => ['name' => 'Swissôtel Büyük Efes', 'capacity' => 400, 'target_occupancy' => 80, 'country' => 'Türkiye'],
        'Muğla' => ['name' => 'Swissôtel Bodrum Beach', 'capacity' => 350, 'target_occupancy' => 75, 'country' => 'Türkiye'],
        'Ankara' => ['name' => 'Swissôtel Capital', 'capacity' => 300, 'target_occupancy' => 78, 'country' => 'Türkiye'],
        'Antalya' => ['name' => 'Swissôtel Resort', 'capacity' => 450, 'target_occupancy' => 82, 'country' => 'Türkiye'],
        'Singapur' => ['name' => 'Swissôtel The Stamford', 'capacity' => 600, 'target_occupancy' => 90, 'country' => 'Singapur'],
        'Moskova' => ['name' => 'Swissôtel Krasnye Holmy', 'capacity' => 380, 'target_occupancy' => 75, 'country' => 'Rusya'],
        'Tallinn' => ['name' => 'Swissôtel Tallinn', 'capacity' => 280, 'target_occupancy' => 70, 'country' => 'Estonya'],
        'Amsterdam' => ['name' => 'Swissôtel Amsterdam', 'capacity' => 320, 'target_occupancy' => 85, 'country' => 'Hollanda'],
        'Chicago' => ['name' => 'Swissôtel Chicago', 'capacity' => 420, 'target_occupancy' => 80, 'country' => 'ABD'],
        'Osaka' => ['name' => 'Swissôtel Nankai Osaka', 'capacity' => 360, 'target_occupancy' => 88, 'country' => 'Japonya'],
        'Sydney' => ['name' => 'Swissôtel Sydney', 'capacity' => 380, 'target_occupancy' => 82, 'country' => 'Avustralya']
    ];
    
    // Türkiye otelleri için gerçek veri çek
    $turkeyData = [];
    $sql = "SELECT 
                i.il_ad as hotel_city,
                COUNT(DISTINCT s.siparis_id) as total_orders,
                SUM(s.siparis_adet) as total_sales,
                SUM(s.toplam_tutar) as total_revenue,
                COUNT(DISTINCT s.musteri_id) as unique_customers,
                AVG(s.toplam_tutar) as avg_order_value
            FROM siparis s 
            JOIN musteri m ON s.musteri_id = m.musteri_id
            JOIN iller i ON m.il_id = i.il_id
            WHERE YEAR(s.siparis_tarihi) = 2025
            AND s.siparis_durumu = 'teslim_edildi'
            AND i.il_ad IN ('İstanbul', 'İzmir', 'Muğla', 'Ankara', 'Antalya')
            GROUP BY i.il_ad";
    
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $turkeyData[$row['hotel_city']] = $row;
        }
    }
    
    // Tüm oteller için performans hesapla
    $hotelPerformances = [];
    
    foreach ($allHotels as $city => $hotelInfo) {
        $hotelName = $hotelInfo['name'];
        $capacity = $hotelInfo['capacity'];
        $targetOccupancy = $hotelInfo['target_occupancy'];
        $country = $hotelInfo['country'];
        
        if ($country === 'Türkiye' && isset($turkeyData[$city])) {
            // Gerçek veri var
            $data = $turkeyData[$city];
            $actualOrders = (int)$data['total_orders'];
            $actualRevenue = (float)$data['total_revenue'];
            $actualCustomers = (int)$data['unique_customers'];
        } else {
            // Simüle edilmiş veri (ülkeye göre ayarlanmış)
            $baseMultiplier = 1.0;
            switch($country) {
                case 'Singapur':
                case 'ABD':
                case 'Japonya':
                    $baseMultiplier = 1.2;
                    break;
                case 'Hollanda':
                case 'Avustralya':
                    $baseMultiplier = 1.1;
                    break;
                case 'Rusya':
                case 'Estonya':
                    $baseMultiplier = 0.8;
                    break;
                default:
                    $baseMultiplier = 1.0;
                    break;
            }
            
            $actualOrders = round($capacity * 0.25 * $baseMultiplier * (0.8 + mt_rand(0, 40) / 100));
            $actualRevenue = $actualOrders * mt_rand(3000, 8000) * $baseMultiplier;
            $actualCustomers = round($actualOrders * 0.7);
        }
        
        // Performans metrikleri hesapla
        $capacityUtilization = min(($actualOrders / ($capacity * 0.3)) * 100, 100);
        $occupancyPerformance = min(($capacityUtilization / $targetOccupancy) * 100, 120);
        
        $expectedRevenue = $capacity * 30000; // Oda başına yıllık hedef gelir
        $revenuePerformance = min(($actualRevenue / $expectedRevenue) * 100, 120);
        
        $expectedCustomers = $capacity * 0.5;
        $customerPerformance = min(($actualCustomers / $expectedCustomers) * 100, 120);
        
        // Operasyonel verimlilik (simüle)
        $operationalEfficiency = 70 + mt_rand(0, 25);
        
        // Toplam performans skoru (ağırlıklı ortalama)
        $totalPerformance = (
            $occupancyPerformance * 0.35 +    // Kapasite kullanımı %35
            $revenuePerformance * 0.30 +      // Gelir performansı %30
            $customerPerformance * 0.20 +     // Müşteri performansı %20
            $operationalEfficiency * 0.15     // Operasyonel verimlilik %15
        );
        
        $hotelPerformances[] = [
            'hotel' => $hotelName,
            'city' => $city,
            'country' => $country,
            'capacity' => $capacity,
            'performance_score' => round($totalPerformance, 1),
            'occupancy_rate' => round($capacityUtilization, 1),
            'revenue_performance' => round($revenuePerformance, 1),
            'customer_performance' => round($customerPerformance, 1),
            'operational_efficiency' => round($operationalEfficiency, 1),
            'actual_orders' => $actualOrders,
            'actual_revenue' => $actualRevenue,
            'actual_customers' => $actualCustomers
        ];
    }
    
    // Performansa göre sırala (yüksekten düşüğe)
    usort($hotelPerformances, function($a, $b) {
        return $b['performance_score'] <=> $a['performance_score'];
    });
    
    // Chart data hazırla
    $labels = [];
    $performanceData = [];
    $occupancyData = [];
    $revenueData = [];
    $capacityData = [];
    $colors = [];
    
    $colorPalette = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
        '#4BC0C0', '#36A2EB', '#FFCE56', '#FF6384'
    ];
    
    foreach ($hotelPerformances as $index => $hotel) {
        $labels[] = $hotel['hotel'];
        $performanceData[] = $hotel['performance_score'];
        $occupancyData[] = $hotel['occupancy_rate'];
        $revenueData[] = $hotel['revenue_performance'];
        $capacityData[] = $hotel['capacity'];
        $colors[] = $colorPalette[$index % count($colorPalette)];
    }
    
    return [
        'type' => 'comprehensive_hotel_performance',
        'hotels' => $hotelPerformances,
        'chart_data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Toplam Performans Skoru',
                    'data' => $performanceData,
                    'backgroundColor' => $colors,
                    'borderColor' => '#fff',
                    'borderWidth' => 2
                ]
            ]
        ],
        'detailed_metrics' => [
            'occupancy_data' => $occupancyData,
            'revenue_data' => $revenueData,
            'capacity_data' => $capacityData
        ]
    ];
}

// Get Hotel Capacity Performance Data
function getHotelCapacityPerformance($conn) {
    $sql = "SELECT 
                i.il_ad as city,
                COUNT(DISTINCT s.siparis_id) as total_orders,
                SUM(s.toplam_tutar) as total_revenue,
                COUNT(DISTINCT s.musteri_id) as unique_customers,
                AVG(s.toplam_tutar) as avg_order_value
            FROM siparis s 
            JOIN musteri m ON s.musteri_id = m.musteri_id
            JOIN iller i ON m.il_id = i.il_id
            WHERE s.siparis_durumu = 'teslim_edildi'
            AND YEAR(s.siparis_tarihi) = 2025
            AND i.il_ad IN ('İstanbul', 'İzmir', 'Muğla', 'Ankara', 'Antalya')
            GROUP BY i.il_ad
            ORDER BY total_revenue DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $hotelCapacities = [
        'İstanbul' => ['name' => 'Swissôtel The Bosphorus', 'capacity' => 500],
        'İzmir' => ['name' => 'Swissôtel Büyük Efes', 'capacity' => 400],
        'Muğla' => ['name' => 'Swissôtel Bodrum Beach', 'capacity' => 350],
        'Ankara' => ['name' => 'Swissôtel Capital', 'capacity' => 300],
        'Antalya' => ['name' => 'Swissôtel Antalya Resort', 'capacity' => 450]
    ];
    
    $hotelNames = [];
    $capacityUtilization = [];
    $totalRevenue = [];
    $totalBookings = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $city = $row['city'];
            $capacity = $hotelCapacities[$city]['capacity'];
            $hotelName = $hotelCapacities[$city]['name'];
            
            // Kapasite kullanım oranını hesapla (sipariş sayısına göre)
            $utilization = min(($row['total_orders'] / ($capacity * 0.3)) * 100, 100);
            
            $hotelNames[] = $hotelName;
            $capacityUtilization[] = round($utilization, 1);
            $totalRevenue[] = (float)$row['total_revenue'];
            $totalBookings[] = (int)$row['total_orders'];
        }
    }
    
    // Eğer veri yoksa varsayılan değerler
    if (empty($hotelNames)) {
        $hotelNames = ['Swissôtel The Bosphorus', 'Swissôtel Büyük Efes', 'Swissôtel Bodrum Beach', 'Swissôtel Capital'];
        $capacityUtilization = [87.5, 92.3, 78.9, 82.1];
        $totalRevenue = [2500000, 1800000, 1200000, 1500000];
        $totalBookings = [438, 369, 276, 350];
    }
    
    return [
        'hotelNames' => $hotelNames,
        'capacityUtilization' => $capacityUtilization,
        'totalRevenue' => $totalRevenue,
        'totalBookings' => $totalBookings
    ];
}

// Get Hotel Performance Metrics Table
function getHotelPerformanceTable($conn) {
    $sql = "SELECT 
                i.il_ad as city,
                COUNT(DISTINCT s.siparis_id) as siparis_sayisi,
                SUM(s.toplam_tutar) as toplam_gelir,
                AVG(s.toplam_tutar) as ortalama_siparis,
                COUNT(DISTINCT s.musteri_id) as unique_customers
            FROM siparis s 
            JOIN musteri m ON s.musteri_id = m.musteri_id
            JOIN iller i ON m.il_id = i.il_id
            WHERE s.siparis_durumu = 'teslim_edildi'
            AND YEAR(s.siparis_tarihi) = 2025
            AND i.il_ad IN ('İstanbul', 'İzmir', 'Muğla', 'Ankara', 'Antalya')
            GROUP BY i.il_ad
            ORDER BY toplam_gelir DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $hotelCapacities = [
        'İstanbul' => ['name' => 'Swissôtel The Bosphorus', 'capacity' => 500],
        'İzmir' => ['name' => 'Swissôtel Büyük Efes', 'capacity' => 400],
        'Muğla' => ['name' => 'Swissôtel Bodrum Beach', 'capacity' => 350],
        'Ankara' => ['name' => 'Swissôtel Capital', 'capacity' => 300],
        'Antalya' => ['name' => 'Swissôtel Antalya Resort', 'capacity' => 450]
    ];
    
    $hotelData = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $city = $row['city'];
            $capacity = $hotelCapacities[$city]['capacity'];
            $hotelName = $hotelCapacities[$city]['name'];
            
            // Kapasite kullanım oranını hesapla
            $kullanim_orani = min(($row['siparis_sayisi'] / ($capacity * 0.3)) * 100, 100);
            
            // Performans seviyesini belirle
            $performans_seviyesi = 'İyi';
            if ($kullanim_orani >= 90) {
                $performans_seviyesi = 'Mükemmel';
            } elseif ($kullanim_orani >= 75) {
                $performans_seviyesi = 'İyi';
            } else {
                $performans_seviyesi = 'Geliştirilmeli';
            }
            
            $hotelData[] = [
                'otel_ad' => $hotelName,
                'sehir' => $city,
                'siparis_sayisi' => (int)$row['siparis_sayisi'],
                'toplam_gelir' => (float)$row['toplam_gelir'],
                'ortalama_siparis' => round((float)$row['ortalama_siparis'], 0),
                'kapasite' => $capacity,
                'kullanim_orani' => round($kullanim_orani, 1),
                'performans_seviyesi' => $performans_seviyesi
            ];
        }
    }
    
    // Eğer veri yoksa varsayılan değerler
    if (empty($hotelData)) {
        $hotelData = [
            [
                'otel_ad' => 'Swissôtel The Bosphorus',
                'sehir' => 'İstanbul',
                'siparis_sayisi' => 438,
                'toplam_gelir' => 2500000,
                'ortalama_siparis' => 5707,
                'kapasite' => 500,
                'kullanim_orani' => 87.6,
                'performans_seviyesi' => 'İyi'
            ],
            [
                'otel_ad' => 'Swissôtel Büyük Efes',
                'sehir' => 'İzmir',
                'siparis_sayisi' => 369,
                'toplam_gelir' => 1800000,
                'ortalama_siparis' => 4878,
                'kapasite' => 400,
                'kullanim_orani' => 92.3,
                'performans_seviyesi' => 'Mükemmel'
            ],
            [
                'otel_ad' => 'Swissôtel Bodrum Beach',
                'sehir' => 'Muğla',
                'siparis_sayisi' => 276,
                'toplam_gelir' => 1200000,
                'ortalama_siparis' => 4348,
                'kapasite' => 350,
                'kullanim_orani' => 78.9,
                'performans_seviyesi' => 'İyi'
            ],
            [
                'otel_ad' => 'Swissôtel Capital',
                'sehir' => 'Ankara',
                'siparis_sayisi' => 321,
                'toplam_gelir' => 1500000,
                'ortalama_siparis' => 4673,
                'kapasite' => 300,
                'kullanim_orani' => 82.1,
                'performans_seviyesi' => 'İyi'
            ]
        ];
    }
    
    return $hotelData;
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

// Main execution
try {
    // Calculate all dashboard data
    $response = [
        'status' => 'success',
        'kpis' => calculateKPIs($conn, $whereClause),
        'salesTrend' => getSalesTrendData($conn, $whereClause),
        'categoryDistribution' => getCategoryDistribution($conn, $whereClause),
        'regionalPerformance' => getRegionalPerformance($conn, $whereClause),
        'topProducts' => getTopProducts($conn, $whereClause),
        'financial' => getFinancialData($conn, $whereClause),
        'comparative' => getComparativeData($conn),
        'hotelCapacity' => getHotelCapacityPerformance($conn),
        'hotelPerformanceTable' => getHotelPerformanceTable($conn),
        'executive' => getExecutiveData($conn),
        'performance' => getPerformanceData($conn),
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
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>