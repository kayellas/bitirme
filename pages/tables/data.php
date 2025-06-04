<?php include("header.php")?>


<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ürün Listesi</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                            <style>
                                .edit i{
                                    color: green;
                                }
                                .delete i{
                                    color:red;
                                }
                            </style>
                                <tr>
                                    <th>Ürün Kodu</th>
                                    <th>Ürün Adı</th>
                                    <th>Ürün Miktarı</th>
                                    <th>Kategori Adı</th>
                                    <th>Alış Fiyatı</th>
                                    <th>Ürün Alış Tarihi</th>
                                    <th colspan="2">İşlemler</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Veritabanı bağlantısı
                                $servername = "localhost";
                                $username = "root";
                                $password = "";
                                $database = "kds";

                                $connection= new mysqli($servername,$username,$password,$database);
                                if($connection->connect_error){
                                    die("Connection failed". $connection->connect_error);
                                }

                                // Verileri çek

                                $sql= "SELECT * FROM urun";
                                $result=$connection->query($sql);

                                if(!$result){
                                    die("Invalid query:" .$connection->error);
                                }
                 

                               

                                // JSON formatında verileri döndür
                                $data = [];
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["urun_id"] . "</td>";
                                        echo "<td>" . $row["urun_ad"] . "</td>";
                                        echo "<td>" . $row["urun_miktar"] . "</td>";
                                        echo "<td>" . $row["kategori_ad"] . "</td>";
                                        echo "<td>" . $row["urun_fiyat"] . "</td>";
                                        echo "<td>". $row["urun_tarih"] . "</td>";
                                        echo "<td> <a href='edit.php?id={$row["urun_id"]}' style=' color:orange'>Düzenle</a></td>";
                                        echo "<td> <a href='delete.php?id={$row["urun_id"]}' style='color:red' onclick=\"return confirm('Silmek istediğinize emin misiniz?');\">Sil</a></td>";

                                        echo "</tr>";
                                    }
                                }

                                // Veritabanı bağlantısını kapat
                                $conn->close();
                                ?>
                            </tbody>
                            <!-- <tfoot>
                                <tr>
                                    <th>Tedarikçi Adı</th>
                                    <th>Tedarikçi İli</th>
                                    <th>Tedarikçi Bölge</th>
                                    <th>Tedarikçi Siparis</th>
                                </tr>
                            </tfoot> -->
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
<footer class="main-footer">
    <strong>FATIMA ZEYNEP KAYA  &copy; 2023-2024</strong>
</footer>
</div>
<!-- /.content-wrapper -->

