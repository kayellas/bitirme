<?php include("header.php")?>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="index.php" class="nav-link">Anasayfa</a>
          </li>
        </ul>
    
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Navbar Search -->
          <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
              <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
              <form class="form-inline">
                <div class="input-group input-group-sm">
                  <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                  <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                      <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
              <i class="fas fa-expand-arrows-alt"></i>
            </a>
          </li>
          
        </ul>
      </nav>
      <!-- /.navbar -->
    
      <!-- Main Sidebar Container -->
      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-link">
          <span class="brand-text font-weight-light">Tedarik Zinciri Yönetimi </span>
        </a>
    
        <!-- Sidebar -->
        <div class="sidebar">
          <!-- Sidebar user panel (optional) -->
    
          <!-- SidebarSearch Form -->
          <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
              <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-sidebar">
                  <i class="fas fa-search fa-fw"></i>
                </button>
              </div>
            </div>
          </div>
    
          <!-- Sidebar Menu -->
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
              <!-- Add icons to the links using the .nav-icon class
                   with font-awesome or any other icon font library -->
              <li class="nav-item menu-open">
                <a href="index.php" class="nav-link">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>
                    Dashboard
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/chart.php" class="nav-link">
                  <i class="nav-icon fas fa-chart-pie"></i>
                  <p>
                    Grafikler
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="../kds/pages/charts/chart.php" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>ChartJS</p>
                    </a>
                  </li>
                </ul>
              </li>
    
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-table"></i>
                  <p>
                    Tablolar
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  </li>
                  <li class="nav-item">
                    <a href="../kds/pages/tables/data.php" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>DataTables</p>
                </a>
              </li>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Lokasyon Bazlı Firma
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="../map/map.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>MAP</p>

                </a>
              </li>
            </ul>
          </li>
              <li class="nav-item">
                <a href="../kds/pages/tables/data.php" class="nav-link active">
                  <i class="nav-icon fas fa-chart-pie"></i>
                  <p>
                    Ürün Ekle
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                </li>
            </ul>
          </nav>
          <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
      </aside>
    <div class="content-wrapper">
        <section class="content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ürün Ekleme Formu</h3>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col"> 
                            <div class="form-group row">
                                <label>Ürün Adı</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="urun_ad" name="urun_ad" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col"> 
                            <div class="form-group row">
                                <label>urun_fiyat</label>
                                <div class="col-sm-10">
                                    <input type="float" class="form-control" id="urun_fiyat" name="urun_fiyat" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col"> 
                            <div class="form-group row">
                                <label>kategori_id</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="kategori_id" name="kategori_id" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col"> 
                            <div class="form-group row">
                                <label>urun_miktar</label>
                                <div class="col-sm-10">
                                    <input type="float" class="form-control" id="urun_miktar" name="urun_miktar" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col"> 
                            <div class="form-group row">
                                <label>urun_tarih</label>
                                <div class="col-sm-10">
                                    <input type="date" class="form-control" id="urun_tarih" name="urun_tarih" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" id="ekle">Ekle</button>

                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block"> </div>
        <strong>PROF. DR. VAHAP TECİM - KDS PROJESİ İÇİN YAPILMIŞTIR. (ZEYNEP KAYA) &copy; 2023-2024</strong>
    </footer>
    </div>
</body>
</div>
<form action="#" method="Post">
    <script src="admin.js"></script>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msg="Başarı";
    
    $urun_ad=$_POST['urun_ad'];
    $urun_fiyat = $_POST['urun_fiyat'];
    $kategori_id = $_POST['kategori_id'];
    $urun_miktar = $_POST['urun_miktar'];
    $urun_tarih = $_POST['urun_tarih'];

    $conn = new mysqli('localhost','root','','kds');
    if ($conn->connect_error) {
      die('Connection Failed : ' . $conn->connect_error);
    } else {
      $stmt = $conn->prepare("insert into urun(urun_ad,urun_fiyat,kategori_id,urun_miktar,urun_tarih)
          values(?,?,?,?,?)");
      $stmt->bind_param("siiis", $urun_ad, $urun_fiyat, $kategori_id, $urun_miktar, $urun_tarih);
      $stmt->execute();
      $stmt->close();
      $conn->close();
    }
  }

    //my other php code here 
?>
</div>
</form>

<script>
        function showUserForm() {
            document.getElementById("user-form").style.display = "block";
        }
    </script>
<?php include("../sidebar/footer.php")?>