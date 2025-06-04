<?php
// Basit Export - CSV/HTML formatında
$conn = mysqli_connect('localhost', 'root', '', 'kds');
if (!$conn) {
    die("Bağlantı başarısız: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Get parameters
$format = $_GET['format'] ?? 'csv';
$category = $_GET['category'] ?? 'all';
$year = $_GET['year'] ?? 'all';
$quarter = $_GET['quarter'] ?? 'all';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$status = $_GET['status'] ?? 'all';

// Build WHERE conditions
$whereConditions = ["s.siparis_durumu != 'iptal'"];

if ($category !== 'all') {
    $whereConditions[] = "u.kategori_id = '" . mysqli_real_escape_string($conn, $category) . "'";
}

if ($year !== 'all') {
    $whereConditions[] = "YEAR(s.siparis_tarihi) = '" . mysqli_real_escape_string($conn, $year) . "'";
}

if ($quarter !== 'all') {
    $quarterInt = (int)$quarter;
    $startMonth = ($quarterInt - 1) * 3 + 1;
    $endMonth = $quarterInt * 3;
    $whereConditions[] = "MONTH(s.siparis_tarihi) BETWEEN $startMonth AND $endMonth";
}

if ($startDate) {
    $whereConditions[] = "s.siparis_tarihi >= '" . mysqli_real_escape_string($conn, $startDate) . "'";
}

if ($endDate) {
    $whereConditions[] = "s.siparis_tarihi <= '" . mysqli_real_escape_string($conn, $endDate) . "'";
}

if ($status !== 'all') {
    $whereConditions[] = "s.siparis_durumu = '" . mysqli_real_escape_string($conn, $status) . "'";
}

$whereClause = implode(' AND ', $whereConditions);

if ($format === 'csv' || $format === 'excel') {
    exportToCSV($conn, $whereClause);
} else {
    exportToHTML($conn, $whereClause);
}

function exportToCSV($conn, $whereClause) {
    $filename = 'Executive_Dashboard_Report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Executive Summary
    fputcsv($output, ['DASHBOARD RAPORU - TEDARİK ZİNCİRİ YÖNETİMİ'], ';');
    fputcsv($output, ['Rapor Tarihi: ' . date('d.m.Y H:i')], ';');
    fputcsv($output, ['FATIMA ZEYNEP KAYA'], ';');
    fputcsv($output, [''], ';');
    
    // KPI Summary
    $sql = "SELECT 
                SUM(s.siparis_adet) as total_sales,
                SUM(s.toplam_tutar) as total_revenue,
                AVG(s.toplam_tutar) as avg_order_value,
                COUNT(DISTINCT s.musteri_id) as active_customers,
                COUNT(s.siparis_id) as total_orders
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause";
    
    $result = mysqli_query($conn, $sql);
    $kpiData = mysqli_fetch_assoc($result);
    
    fputcsv($output, ['ANAHTAR PERFORMANS GÖSTERGELERİ'], ';');
    fputcsv($output, ['Metrik', 'Değer'], ';');
    fputcsv($output, ['Toplam Satış Miktarı', number_format($kpiData['total_sales'] ?? 0)], ';');
    fputcsv($output, ['Toplam Gelir (₺)', number_format($kpiData['total_revenue'] ?? 0, 2)], ';');
    fputcsv($output, ['Ortalama Sipariş Değeri (₺)', number_format($kpiData['avg_order_value'] ?? 0, 2)], ';');
    fputcsv($output, ['Aktif Müşteri Sayısı', number_format($kpiData['active_customers'] ?? 0)], ';');
    fputcsv($output, ['Toplam Sipariş Sayısı', number_format($kpiData['total_orders'] ?? 0)], ';');
    fputcsv($output, [''], ';');
    
    // Category Performance
    fputcsv($output, ['KATEGORİ PERFORMANSI'], ';');
    fputcsv($output, ['Kategori', 'Satış Miktarı', 'Gelir (₺)', 'Sipariş Sayısı'], ';');
    
    $sql = "SELECT 
                k.kategori_ad,
                SUM(s.siparis_adet) as total_sales,
                SUM(s.toplam_tutar) as total_revenue,
                COUNT(s.siparis_id) as order_count
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN kategori k ON u.kategori_id = k.kategori_id 
            WHERE $whereClause
            GROUP BY k.kategori_id, k.kategori_ad
            ORDER BY total_revenue DESC";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['kategori_ad'],
            number_format($row['total_sales']),
            number_format($row['total_revenue'], 2),
            number_format($row['order_count'])
        ], ';');
    }
    
    fputcsv($output, [''], ';');
    
    // Top Products
    fputcsv($output, ['EN ÇOK SATAN ÜRÜNLER (TOP 20)'], ';');
    fputcsv($output, ['Ürün Adı', 'Kategori', 'Satış Miktarı', 'Gelir (₺)'], ';');
    
    $sql = "SELECT 
                u.urun_ad,
                k.kategori_ad,
                SUM(s.siparis_adet) as total_sales,
                SUM(s.toplam_tutar) as total_revenue
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN kategori k ON u.kategori_id = k.kategori_id 
            WHERE $whereClause
            GROUP BY u.urun_id, u.urun_ad, k.kategori_ad
            ORDER BY total_sales DESC
            LIMIT 20";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['urun_ad'],
            $row['kategori_ad'],
            number_format($row['total_sales']),
            number_format($row['total_revenue'], 2)
        ], ';');
    }
    
    fputcsv($output, [''], ';');
    
    // Regional Performance
    fputcsv($output, ['BÖLGESEL PERFORMANS ANALİZİ'], ';');
    fputcsv($output, ['İl', 'Bölge', 'Satış Miktarı', 'Gelir (₺)', 'Müşteri Sayısı'], ';');
    
    $sql = "SELECT 
                i.il_ad,
                b.bolge_ad,
                SUM(s.siparis_adet) as regional_sales,
                SUM(s.toplam_tutar) as regional_revenue,
                COUNT(DISTINCT s.musteri_id) as customers
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN musteri m ON s.musteri_id = m.musteri_id
            JOIN iller i ON m.il_id = i.il_id
            JOIN bolge b ON i.bolge_id = b.bolge_id
            WHERE $whereClause
            GROUP BY i.il_id, i.il_ad, b.bolge_ad
            ORDER BY regional_revenue DESC
            LIMIT 15";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['il_ad'],
            $row['bolge_ad'],
            number_format($row['regional_sales']),
            number_format($row['regional_revenue'], 2),
            number_format($row['customers'])
        ], ';');
    }
    
    fclose($output);
    exit;
}

function exportToHTML($conn, $whereClause) {
    $filename = 'Executive_Dashboard_Report_' . date('Y-m-d_H-i-s') . '.html';
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> Dashboard Rapor</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #366092; padding-bottom: 20px; }
            h1 { color: #366092; margin-bottom: 5px; }
            h2 { color: #366092; margin-top: 30px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #366092; color: white; font-weight: bold; }
            tr:nth-child(even) { background-color: #f8f9fa; }
            tr:hover { background-color: #e3f2fd; }
            .number { text-align: right; font-weight: 500; }
            .kpi-summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>TEDARİK ZİNCİRİ YÖNETİMİ</h1>
            <h2> Dashboard Raporu</h2>
            <p><strong>Rapor Tarihi:</strong> ' . date('d.m.Y H:i') . '</p>
            <p><strong>Hazırlayan:</strong> FATIMA ZEYNEP KAYA</p>
        </div>';
    
    // Get KPI data
    $sql = "SELECT 
                SUM(s.siparis_adet) as total_sales,
                SUM(s.toplam_tutar) as total_revenue,
                AVG(s.toplam_tutar) as avg_order_value,
                COUNT(DISTINCT s.musteri_id) as active_customers,
                COUNT(s.siparis_id) as total_orders
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            WHERE $whereClause";
    
    $result = mysqli_query($conn, $sql);
    $kpiData = mysqli_fetch_assoc($result);
    
    echo '<div class="kpi-summary">
            <h2 style="margin-top: 0; color: white;">Rapor Özeti</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div><strong>Toplam Satış:</strong><br>' . number_format($kpiData['total_sales'] ?? 0) . ' adet</div>
                <div><strong>Toplam Gelir:</strong><br>₺' . number_format($kpiData['total_revenue'] ?? 0, 2) . '</div>
                <div><strong>Ortalama Sipariş:</strong><br>₺' . number_format($kpiData['avg_order_value'] ?? 0, 2) . '</div>
                <div><strong>Aktif Müşteri:</strong><br>' . number_format($kpiData['active_customers'] ?? 0) . '</div>
            </div>
          </div>';
    
    echo '<h2>Anahtar Performans Göstergeleri</h2>
    <table>
        <tr><th>Metrik</th><th>Değer</th></tr>
        <tr><td>Toplam Satış Miktarı</td><td class="number">' . number_format($kpiData['total_sales'] ?? 0) . '</td></tr>
        <tr><td>Toplam Gelir</td><td class="number">₺' . number_format($kpiData['total_revenue'] ?? 0, 2) . '</td></tr>
        <tr><td>Ortalama Sipariş Değeri</td><td class="number">₺' . number_format($kpiData['avg_order_value'] ?? 0, 2) . '</td></tr>
        <tr><td>Aktif Müşteri Sayısı</td><td class="number">' . number_format($kpiData['active_customers'] ?? 0) . '</td></tr>
        <tr><td>Toplam Sipariş Sayısı</td><td class="number">' . number_format($kpiData['total_orders'] ?? 0) . '</td></tr>
    </table>';
    
    // Category Performance
    echo '<h2>Kategori Performansı</h2>
    <table>
        <tr><th>Kategori</th><th>Satış Miktarı</th><th>Gelir (₺)</th><th>Sipariş Sayısı</th></tr>';
    
    $sql = "SELECT 
                k.kategori_ad,
                SUM(s.siparis_adet) as total_sales,
                SUM(s.toplam_tutar) as total_revenue,
                COUNT(s.siparis_id) as order_count
            FROM siparis s 
            JOIN urun u ON s.urun_id = u.urun_id 
            JOIN kategori k ON u.kategori_id = k.kategori_id 
            WHERE $whereClause
            GROUP BY k.kategori_id, k.kategori_ad
            ORDER BY total_revenue DESC";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
            <td><strong>' . htmlspecialchars($row['kategori_ad']) . '</strong></td>
            <td class="number">' . number_format($row['total_sales']) . '</td>
            <td class="number">₺' . number_format($row['total_revenue'], 2) . '</td>
            <td class="number">' . number_format($row['order_count']) . '</td>
        </tr>';
    }
    
    echo '</table>';
    
    echo '<div class="footer">
            <p><strong>Bu rapor Tedarik Zinciri Yönetimi sistemi tarafından otomatik olarak oluşturulmuştur.</strong></p>
            <p>FATIMA ZEYNEP KAYA © 2023-2025 - Executive Dashboard v3.0.0</p>
          </div>';
    
    echo '</body></html>';
    exit;
}

mysqli_close($conn);
?>