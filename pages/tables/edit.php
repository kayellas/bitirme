<?php
include("header.php");

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $personelId = $_GET['id'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "kds";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed" . $connection->connect_error);
    }

    $sql = "SELECT * FROM urun WHERE urun_id = $urunId";
    $result = $connection->query($sql);

    if (!$result) {
        die("Invalid query:" . $connection->error);
    }

    $row = $result->fetch_assoc();

    // HTML formunu başlat
    echo '<form method="POST" action="update_process.php">';
    
    // Hidden input ile personelId'yi form içinde taşıyın
    echo '<input type="hidden" name="urun_id" value="' . $urunId . '">';

    // Diğer input alanlarını ekleyin
    echo 'Urun Adı: <input type="text" name="urun_ad" value="' . $row['urun_ad'] . '"><br>';
    echo 'Urun Fiyatı: <input type="text" name="urun_fiyat" value="' . $row['urun_fiyat'] . '"><br>';
    echo 'Kategor ID: <input type="text" name="kategori_id" value="' . $row['kategori_id'] . '"><br>';
    echo 'Urun Miktar: <input type="text" name="urun_miktar" value="' . $row['urun_miktar'] . '"><br>';
    echo 'Urun Tarihi: <input type="date" name="urun_tarih" value="' . $row['urun_tarih'] . '"><br>';

    // Güncelleme butonu
    echo '<input type="submit" value="Güncelle">';
    
    // HTML formunu kapat
    echo '</form>';

    $connection->close();
} else {
    // Hatalı veya eksik parametrelerle erişim durumunda başka bir sayfaya yönlendirme yapabilirsiniz
    header("Location: data.php");
    exit();
}

include("../sidebar/footer.php");
?>
