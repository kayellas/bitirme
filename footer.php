
<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<!-- Page specific script -->


<!-- 1. -->
<body>
  
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
            ['Yıl', ''],
            <?php 
            $sql = "SELECT
            EXTRACT(YEAR FROM urun_tarih) AS yil,
            urun_ad,
            SUM(urun_miktar) AS toplam_miktar,
            SUM(urun_fiyat * urun_miktar) AS toplam_gelir
            FROM urun GROUP BY yil, urun_ad ORDER BY yil, toplam_gelir DESC;";

            $fire = mysqli_query($conn, $sql);
                while ($result = mysqli_fetch_assoc($fire)) {
                echo "['".$result["urun_ad"]."',".$result["toplam_miktar"]."],";}
            ?>
            ]);
          
            var options = {
            
            hAxis: {
            title: 'Ürün Adı'
            },
            vAxis: {
            title: 'Satış Miktarı',
            },
            curveType: 'function',
            legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);
            
        }

        
    </script>
    <!-- 2. -->

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
            ['Urun Adı', 'Urun Satış Miktarı'],
            <?php 
            $sql = "SELECT * FROM urun GROUP BY urun_ad";
            $fire = mysqli_query($conn, $sql);
                while ($result = mysqli_fetch_assoc($fire)) {
                echo "['".$result["urun_ad"]."',".$result["urun_miktar"]."],";}
            ?>
            ]);

            var options = {
            
            hAxis: {
            title: 'Ürün Adı'
            },
            vAxis: {
            title: 'Satış Miktarı',
            },
            curveType: 'function',
            legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('line'));

            chart.draw(data, options);
        }
        </script>
        <!-- 3. -->
        
<!-- 3.nün php kodu -->
<?php
$conn = new mysqli ('localhost', 'root', '', 'kds');
$query= $conn->query("WITH sira AS (
  SELECT
    kategori.kategori_ad,
    urun.urun_ad AS en_cok_satan_urun,
    SUM(urun.urun_miktar) AS toplam_satis_miktar,
    RANK() OVER (PARTITION BY kategori.kategori_id ORDER BY SUM(urun.urun_miktar) DESC) AS sira_numarasi
  FROM
    kategori
    JOIN urun ON kategori.kategori_id = urun.kategori_id
  GROUP BY
    kategori.kategori_id, urun.urun_id
)
SELECT
  kategori_ad,
  en_cok_satan_urun,
  toplam_satis_miktar
FROM
  sira
WHERE
  sira_numarasi <= 3
ORDER BY
  kategori_ad, sira_numarasi;
; ");

$kategori_ad = array();
$en_cok_satan_urun = array();
$toplam_satis_miktar = array();

foreach ($query as $data) {
    $kategori_ad[] = $data['kategori_ad'];
    $en_cok_satan_urun[] = $data['en_cok_satan_urun'];
    $toplam_satis_miktar[] = $data['toplam_satis_miktar'];
}

?>
<script>
const labels = <?php echo json_encode($kategori_ad)?>;
const data = {
  labels: labels,
  datasets: [{
    label: 'Kategoriye Göre En Çok Satılan Ürün Miktarı',
    data: <?php echo json_encode($toplam_satis_miktar)?>,
    backgroundColor: [
      'rgba(255, 99, 132, 0.2)',
      'rgba(255, 159, 64, 0.2)',
      'rgba(255, 205, 86, 0.2)',
      'rgba(75, 192, 192, 0.2)',
      'rgba(54, 162, 235, 0.2)',
      'rgba(153, 102, 255, 0.2)',
      'rgba(201, 203, 207, 0.2)'
    ],
    borderColor: [
      'rgb(255, 99, 132)',
      'rgb(255, 159, 64)',
      'rgb(255, 205, 86)',
      'rgb(75, 192, 192)',
      'rgb(54, 162, 235)',
      'rgb(153, 102, 255)',
      'rgb(201, 203, 207)'
    ],
    borderWidth: 1
  }]
};

const config = {
  type: 'bar',
  data: data,
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  },
};

    var myCharti = new Chart(
        document.getElementById('myCharti'),
        config
    );
</script>

<script src="path/to/your/charts.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    


    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

          var data = google.visualization.arrayToDataTable([
            ['Urun Adı', 'Urun Satış Miktarı'],
          <?php 
          $sql = "SELECT * FROM urun GROUP BY urun_ad ORDER BY urun_miktar ";
          $fire = mysqli_query($conn, $sql);
              while ($result = mysqli_fetch_assoc($fire)) {
              echo "['".$result["urun_ad"]."',".$result["urun_miktar"]."],";}
          ?>
          ]);

          var options = {
          
          };

          var chart = new google.visualization.PieChart(document.getElementById('piechart'));

          chart.draw(data, options);
        }
      </script>

</body>
</html>