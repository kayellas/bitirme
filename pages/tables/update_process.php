<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['urun_id'])) {
    $urunId = $_POST['urun_id'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "kds";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed" . $connection->connect_error);
    }

    $urunAd = $_POST['urun_ad'];
    $urunFiyat = $_POST['urun_fiyat'];
    $kategoriId = $_POST['kategori_id'];
    $urunMiktar = $_POST['urun_miktar'];
    $urunTarih = $_POST['urun_tarih'];
    $kategoriAd= $_POST['kategori_ad'];

    $sql = "UPDATE urun SET 
        urun_ad = ?, 
        urun_fiyat = ?, 
        kategori_id = ?, 
        urun_miktar = ?, 
        urun_tarih = ?, 
        kategori_ad = ?
        WHERE urun_id = ?";

    $stmt = $connection->prepare($sql);

    $stmt->bind_param("siiiss", $urunAd, $urunFiyat, $kategoriId, $urunMiktar, $urunTarih, $personelTurId, $personelId);

    if ($stmt->execute()) {
        // Başarılı bir şekilde güncellendiğinde kursiyer.php sayfasına yönlendirme yapabilirsiniz
        /* header("Location: kursiyer.php"); */
        echo '<script type="text/javascript">alert("Güncelleme başarıyla gerçekleştirildi.");</script>';
        header("Location: update_process.php");

        exit();
    } else {
        die("Güncelleme hatası: " . $stmt->error);
    }

    $stmt->close();
    $connection->close();

} else {
    // Hatalı veya eksik parametrelerle erişim durumunda başka bir sayfaya yönlendirme yapabilirsiniz
    header("Location: data.php");
    exit();
}
?>
