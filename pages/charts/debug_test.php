<?php
// Debug için basit test dosyası
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DATABASE CONNECTION TEST ===\n";

$conn = mysqli_connect('localhost', 'root', '', 'kds');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "✓ Database connected successfully\n";

mysqli_set_charset($conn, "utf8");

echo "\n=== BASIC DATA TEST ===\n";

// Test basic siparis data
$sql = "SELECT COUNT(*) as count FROM siparis";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
echo "Siparis count: " . $row['count'] . "\n";

// Test urun data
$sql = "SELECT COUNT(*) as count FROM urun";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
echo "Urun count: " . $row['count'] . "\n";

// Test kategori data
$sql = "SELECT COUNT(*) as count FROM kategori";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
echo "Kategori count: " . $row['count'] . "\n";

// Test musteri data
$sql = "SELECT COUNT(*) as count FROM musteri";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
echo "Musteri count: " . $row['count'] . "\n";

echo "\n=== JOIN TEST ===\n";

// Test main join
$sql = "SELECT COUNT(*) as count FROM siparis s 
        JOIN urun u ON s.urun_id = u.urun_id";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Siparis-Urun JOIN count: " . $row['count'] . "\n";
} else {
    echo "JOIN ERROR: " . mysqli_error($conn) . "\n";
}

echo "\n=== SAMPLE QUERIES ===\n";

// Test KPI query
$sql = "SELECT 
            COUNT(DISTINCT s.siparis_id) as total_orders,
            COALESCE(SUM(s.siparis_adet), 0) as total_sales,
            COALESCE(SUM(s.toplam_tutar), 0) as total_revenue
        FROM siparis s 
        JOIN urun u ON s.urun_id = u.urun_id 
        WHERE s.siparis_durumu != 'iptal'";

$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "KPI Data:\n";
    print_r($row);
} else {
    echo "KPI QUERY ERROR: " . mysqli_error($conn) . "\n";
}

// Test category distribution
echo "\n=== CATEGORY TEST ===\n";
$sql = "SELECT 
            k.kategori_ad,
            COALESCE(SUM(s.siparis_adet), 0) as total_sales
        FROM kategori k
        LEFT JOIN urun u ON k.kategori_id = u.kategori_id
        LEFT JOIN siparis s ON u.urun_id = s.urun_id 
        GROUP BY k.kategori_id, k.kategori_ad
        ORDER BY total_sales DESC";

$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['kategori_ad'] . ": " . $row['total_sales'] . "\n";
    }
} else {
    echo "CATEGORY QUERY ERROR: " . mysqli_error($conn) . "\n";
}

// Test monthly data
echo "\n=== MONTHLY TREND TEST ===\n";
$sql = "SELECT 
            DATE_FORMAT(s.siparis_tarihi, '%Y-%m') as month,
            COUNT(*) as order_count,
            SUM(s.siparis_adet) as total_sales,
            SUM(s.toplam_tutar) as total_revenue
        FROM siparis s 
        JOIN urun u ON s.urun_id = u.urun_id 
        WHERE s.siparis_durumu != 'iptal'
        GROUP BY DATE_FORMAT(s.siparis_tarihi, '%Y-%m')
        ORDER BY month DESC
        LIMIT 5";

$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['month'] . " - Orders: " . $row['order_count'] . 
             ", Sales: " . $row['total_sales'] . 
             ", Revenue: " . $row['total_revenue'] . "\n";
    }
} else {
    echo "MONTHLY QUERY ERROR: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>