<?php include("header.php")?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Harita ve Raporlar</title>

    <!-- CSS Bağlantıları -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Türkçe karakter desteği için font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        
        .top-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            align-items: stretch; /* Container'ları aynı yükseklikte tutar */
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
            margin-top: 0; /* Üst margin'i kaldır */
        }
        
        .map-container {
            flex: 1;
            display: flex;
            flex-direction: column;
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
        
        .table-wrapper {
            flex: 1;
            overflow-y: auto;
            max-height: 400px; /* Harita ile aynı minimum yükseklik */
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
        
        /* Filtreler ve Export Butonları Aynı Hizada */
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
            background-color:rgb(216, 104, 12);
            color: white;
        }
        
        .export-btn.excel {
            background-color:rgb(73, 85, 243);
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
        }
    </style>
</head>
<body>
    <div class="wrapper">
        
        <!-- Üst Kısım (Harita ve Firma Bilgileri) -->
        <div class="top-container">
            <div class="data-container map-container">
                <h3> Lokasyon Bilgileri</h3>
                <div id="map"></div>
            </div>
            <div class="data-container table-container">
                <h3>Firma Bilgileri</h3>
                <div class="table-wrapper">
                    <table id="location-table">
                        <thead>
                            <tr>
                                <th>Firma Ad</th>
                                <th>İl</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Türkçe karakterleri doğru formatlamak için fonksiyon
                                function turkishTitleCase($string) {
                                    $string = mb_strtolower($string, 'UTF-8');
                                    return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
                                }
                                // Veritabanı Bağlantısı
                                $servername = "localhost";
                                $username = "root";
                                $password = "";
                                $dbname = "kds";
                                $conn = new mysqli($servername, $username, $password, $dbname);

                                if ($conn->connect_error) {
                                    die("Bağlantı hatası: " . $conn->connect_error);
                                }

                                $locationData = array(); // JavaScript için veri array'i

                                $sql = "SELECT firma_ad, il_ad, lat, lng FROM location WHERE lat IS NOT NULL AND lng IS NOT NULL";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr class='data-row' data-lat='{$row['lat']}' data-lng='{$row['lng']}'>";
                                        echo "<td>" . htmlspecialchars($row['firma_ad']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['il_ad']) . "</td>";
                                        echo "</tr>";
                                        
                                        // JavaScript için veri ekle
                                        $locationData[] = array(
                                            'firma_ad' => $row['firma_ad'],
                                            'il_ad' => $row['il_ad'],
                                            'lat' => (float)$row['lat'],
                                            'lng' => (float)$row['lng']
                                        );
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>Veri bulunamadı.</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Alt Kısım (Stok Tablosu) -->
        <div class="bottom-container">
            <h3>Firma Stok Durumu ve Fiyat Bilgileri</h3>
            
            <!-- Filtreler ve Export Butonları Aynı Hizada -->
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
                        <th>Firma Adı</th>
                        <th>İl</th>
                        <th>Kategori</th>
                        <th>Ürün Sayısı</th>
                        <th>Toplam Stok</th>
                        <th>Stok Farkı</th>
                    </tr>
                </thead>
                <tbody id="stok-table-body">
                    <?php
                        // Mevcut veritabanı yapısına uygun SQL sorgusu
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
                                
                        // Stok farkı renklendirmesi (optimize edilmiş versiyon)
                        $stok_farki = $row['toplam_stok_farki'];
                        $bg_color = '';
                        $text_color = '';

                        if ($stok_farki < 0) {
                            $bg_color = '#ffcdd2'; // Soft kırmızı
                            $text_color = '#c62828'; // Koyu kırmızı
                        } elseif ($stok_farki > 10000) {
                            $bg_color = '#c8e6c9'; // Soft yeşil
                            $text_color = '#2e7d32'; // Koyu yeşil
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
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

        <script>

            // Konum verileri PHP'den JavaScript'e aktarılıyor
            const locationData = <?php echo json_encode($locationData); ?>;
            
            // Harita başlatma
            let map;
            let markers = [];

            function initMap() {
                // Türkiye merkezi koordinatları
                map = L.map('map').setView([39.9334, 32.8597], 6);
                
                // OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Markerları ekle
                locationData.forEach(function(location) {
                    if (location.lat && location.lng) {
                        const marker = L.marker([location.lat, location.lng])
                            .bindPopup(`<b>${location.firma_ad}</b><br>${location.il_ad}`)
                            .addTo(map);
                        markers.push(marker);
                    }
                });

                // Harita boyutlandırma sorunu çözümü
                setTimeout(function() {
                    map.invalidateSize();
                }, 100);
            }

            // Sayfa yüklendiğinde haritayı başlat
            document.addEventListener('DOMContentLoaded', function() {
                initMap();
                
                // Tablo satırlarına tıklama olayı
                document.querySelectorAll('.data-row').forEach(function(row) {
                    row.addEventListener('click', function() {
                        const lat = parseFloat(this.getAttribute('data-lat'));
                        const lng = parseFloat(this.getAttribute('data-lng'));
                        
                        if (lat && lng) {
                            map.setView([lat, lng], 12);
                            
                            // İlgili marker'ı bul ve popup'ını aç
                            markers.forEach(function(marker) {
                                const markerPos = marker.getLatLng();
                                if (Math.abs(markerPos.lat - lat) < 0.001 && Math.abs(markerPos.lng - lng) < 0.001) {
                                    marker.openPopup();
                                }
                            });
                        }
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
                    // Özet rapor için sadece Firma Adı ve Kategori sütunlarını göster
                    // Başlıkları güncelle
                    thead.innerHTML = `
                        <th>Firma Adı</th>
                        <th>Kategori</th>
                        <th>Toplam Stok</th>

                    `;
                    
                    // Satırları güncelle
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
                    // Detaylı rapor için tüm sütunları göster
                    // Başlıkları güncelle
                    thead.innerHTML = `
                        <th>Firma Adı</th>
                        <th>İl</th>
                        <th>Kategori</th>
                        <th>Ürün Sayısı</th>
                        <th>Toplam Stok</th>
                        <th>Stok Farkı</th>
                    `;
                    
                    // Sayfayı yenile (detaylı verileri geri getirmek için)
                    location.reload();
                }
            }

            function exportToPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');
                const raporTuru = document.getElementById('rapor-filter').value;
                
                // Türkçe font desteği için
                doc.addFont('https://cdn.jsdelivr.net/npm/roboto-font@0.1.0/fonts/Roboto/roboto-regular-webfont.ttf', 'Roboto', 'normal');
                doc.setFont('Roboto');

                // PDF başlığı
                doc.setFontSize(16);
                const baslik = raporTuru === 'ozet' ? 'Firma Özet Raporu' : 'Firma Stok Durumu Raporu';
                doc.text(baslik, 14, 15);
                
                // Tarih bilgisi
                const today = new Date();
                doc.setFontSize(10);
                doc.text('Rapor Tarihi: ' + today.toLocaleDateString('tr-TR'), 14, 25);

                // Tablo verilerini hazırla
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

                // Tablo başlıkları
                const headers = raporTuru === 'ozet' 
                    ? ['Firma Adı', 'Kategori']
                    : ['Firma Adı', 'İl', 'Kategori', 'Ürün Sayısı', 'Toplam Stok', 'Stok Farkı'];

                // PDF tablosu oluştur
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
                        // Sayfa numarası
                        doc.setFontSize(8);
                        doc.text('Sayfa ' + data.pageNumber, data.settings.margin.left, doc.internal.pageSize.height - 10);
                    }
                });

                // PDF'i kaydet
                const today_formatted = today.getDate().toString().padStart(2, '0') + '_' + 
                                    (today.getMonth() + 1).toString().padStart(2, '0') + '_' + 
                                    today.getFullYear();
                const dosyaAdi = raporTuru === 'ozet' ? 'ozet_raporu_' : 'stok_raporu_';
                doc.save(dosyaAdi + today_formatted + '.pdf');
            }

            // Excel Export Fonksiyonu
            function exportToExcel() {
                const wb = XLSX.utils.book_new();
                const raporTuru = document.getElementById('rapor-filter').value;
                
                // Tablo verilerini hazırla
                const tableData = [];
                const headers = raporTuru === 'ozet' 
                    ? ['Firma Adı', 'Kategori']
                    : ['Firma Adı', 'İl', 'Kategori', 'Ürün Sayısı', 'Toplam Stok', 'Stok Farkı'];
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

                // Worksheet oluştur
                const ws = XLSX.utils.aoa_to_sheet(tableData);
                
                // Sütun genişliklerini ayarla
                const colWidths = raporTuru === 'ozet' 
                    ? [
                        { wch: 25 }, // Firma Adı
                        { wch: 20 }  // Kategori
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

                // Worksheet'i workbook'a ekle
                const sheetName = raporTuru === 'ozet' ? 'Özet Rapor' : 'Stok Raporu';
                XLSX.utils.book_append_sheet(wb, ws, sheetName);
                
                // Excel dosyasını kaydet
                const today = new Date();
                const dosyaAdi = raporTuru === 'ozet' ? 'ozet_raporu_' : 'stok_raporu_';
                XLSX.writeFile(wb, dosyaAdi + today.toISOString().split('T')[0] + '.xlsx');
            }
        </script>
    </div>
</body>
<?php include("footer.php") ?>
</html>