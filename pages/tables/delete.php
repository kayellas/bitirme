<?php
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $urunID = $_GET['id'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "kds";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed" . $connection->connect_error);
    }

    // Tablodan satırı sil
    $sqlDeleteTable = "DELETE FROM urun WHERE urun_id = $urunID";
    $resultDeleteTable = $connection->query($sqlDeleteTable);

    if (!$resultDeleteTable) {
        die("Ürün silme işlemi başarısız:" . $connection->error);
        
    }

    // Veritabanından satırı sil
    $sqlDeleteDatabase = "DELETE FROM urun WHERE urun_id = ?";
    
    $stmt = $connection->prepare($sqlDeleteDatabase);
    $stmt->bind_param("i", $urunID);

    if ($stmt->execute()) {
        // Başarılı bir şekilde silindiğinde ana sayfaya yönlendirme yapabilirsiniz
        echo '<script type="text/javascript">alert("Güncelleme başarıyla gerçekleştirildi.");</script>';
        header("Location:data.php");
        exit();
    } else {
        die("Veritabanı silme işlemi başarısız:" . $stmt->error);
    }

    $stmt->close();
    $connection->close();

} else {
    // Hatalı veya eksik parametrelerle erişim durumunda başka bir sayfaya yönlendirme yapabilirsiniz
    header("Location: data.php");
    exit();
}
?>
