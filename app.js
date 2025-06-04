
const express = require('express');
const app = express();
const port = 3306;
const mysql = require('mysql');



const baglanti = mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database :'kds',
    port: 3306 // Kullandığınız port numarası

});

baglanti.connect(function(err) {
    if (err) {
        console.log("Bağlantı hatası var",err);
    } else {
        console.log("Bağlandı");
    }
});


app.post('/login', (req, res) => {
    const kullanici_adi = req.body.kullanici_adi;
    const sifre = req.body.sifre;

    // Veritabanında kullanıcıyı kontrol et
    const query = 'SELECT * FROM kullanici WHERE kullanici_adi = ? AND sifre = ?';
    db.query(query, [kullanici_adi, sifre], (err, results) => {
        if (err) throw err;

        if (results.length > 0) {
            // Kullanıcı doğrulandı, başka bir sayfaya yönlendirilebilir
            res.send('Giriş başarılı!');
        } else {
            // Kullanıcı doğrulanamadı, hata mesajı gösterilebilir
            res.send('Giriş başarısız!');
        }
    });
});






app.get("/users", (req,res)=>{
    const sql = "select * from users";
    db.query(sql,(err,result)=>{
        if(err){
            res.send(err);
            }else{
                res.send(result);
        }
    })
})


function applyFilters() {
    var selectedValue = document.getElementById("filterDate").value;
      
        if (selectedValue === "today") {
          // Bugünün tarihini al
          var today = new Date();
          var formattedDate = formatDate(today);
          console.log("Seçilen Tarih (Gün-Ay-Yıl):", formattedDate);
        } else if (selectedValue === "thisWeek") {
          // Bu haftanın başlangıç ve bitiş tarihini al
          var thisWeekStart = new Date();
          thisWeekStart.setDate(thisWeekStart.getDate() - thisWeekStart.getDay());
          
          var thisWeekEnd = new Date(thisWeekStart);
          thisWeekEnd.setDate(thisWeekEnd.getDate() + 6);
      
          var formattedStartDate = formatDate(thisWeekStart);
          var formattedEndDate = formatDate(thisWeekEnd);
          console.log("Bu Haftanın Başlangıç Tarihi (Gün-Ay-Yıl):", formattedStartDate);
          console.log("Bu Haftanın Bitiş Tarihi (Gün-Ay-Yıl):", formattedEndDate);
        } else {
          console.log("Tüm tarihler gösteriliyor.");
        }
      }
      
      function formatDate(date) {
        var day = date.getDate();
        var month = date.getMonth() + 1; // Months are zero-based
        var year = date.getFullYear();
      
        // Gün ve Ay için tek haneli sayılar için sıfır ekleyin
        day = day < 10 ? "0" + day : day;
        month = month < 10 ? "0" + month : month;
      
        return day + "-" + month + "-" + year;
      }
      

app.get('/data.html', function (req, res) {
  res.sendFile(path.join(__dirname,"data.html"))
})

// app.get("/profil/:isim", (req, res) => {
//     // res.send(`${req.params.isim} kişisinin profilini görüntülüyorsunuz`)
//     var kullanici = { yas: 30, is: 'yönetici', yabanci_diller: ["Almanca", "Fransızca", "İngilizce"], admin: false }
//     res.render('profil', { kisi: req.params.isim, veri: kullanici })
// })


    // var tableolustur = "CREATE TABLE urun (urun_id INT AUTO_INCREMENT PRIMARY KEY,urun_ad VARCHAR(30),urun_miktar VARCHAR(75))";//Tablo oluşturma
    // baglanti.query(tableolustur,function(err){
    // if(err) throw err;//Sorgu esnasında hata kontrolü
    // console.log("Tablo oluşturuldu!");
    // })

    // var sutunekle = "ALTER TABLE urun ADD COLUMN urun_tarih DATE";//Tabloya sütun ekleme
    // baglanti.query(sutunekle,function(err){
    // 	if(err) throw err;
    // 	console.log("Sütun oluşturuldu!");
    // })

    // var veriekle = "INSERT INTO urun (urun_id, urun_ad, urun_miktar, urun_fiyat) VALUES ('1', 'Pepsi', '500', '0.0')";
    // baglanti.query(veriekle,function(err){
    // 	if(err) throw err;
    // 	console.log("Veri eklendi!");
    // })
    
    // var cokluveriekle = "INSERT INTO urun (urun_ad, urun_miktar) VALUES ?"; // Tabloya çoklu veri ekleme
    // var veriler = [//Eklenecek verilerin hazırlanması
    //     ["Cola","500"],
    //     ["Sprite","300"],
    //     ["Ice Tea","400"],
    //     ["Schweppes ","600"]
    // ];
    // baglanti.query(cokluveriekle,[veriler],function(err){//Sorguyu çalıştırma
    // 	if(err) throw err;
    // 	console.log("Veriler eklendi!");
    // })

    //veri ekleme bitiş

    //sorgular
   // baglanti.query("SELECT * FROM yeni",function(err,sonuc){
    //console.log(sonuc);
    // })
    //     console.log(sonuc[3].urun_id);
    	
    	
    // });

    // baglanti.query("SELECT * FROM urun WHERE urun_id > 3",function(err,sonuc){//ID'si 3'ten büyük olan öğrencileri çekme
    // 	console.log(sonuc);
    // });

   /*  var aranilan_isim = "Oguzhan";
    var aranilan_il = "Antalya";
    var verigetir = "SELECT * FROM urun WHERE urun_ad = ? AND il = ?";
    baglanti.query(verigetir,[aranilan_isim,aranilan_il],function(err,sonuc){
    	console.log(sonuc);
    });

    var verisil = "DELETE FROM urun WHERE id > 5";//Tablodan veri silmek için sorgu
    baglanti.query(verisil,function(err){});

    var siraliverigetir = "SELECT * FROM urun ORDER BY urun_ad"; // "isim" yerine "urun_ad"
    baglanti.query(siraliverigetir,function(err,sonuc){console.log(sonuc);});

    var veriguncelle = "UPDATE urun SET il = 'Erzurum' WHERE id = 4";//Tablodan veri güncelleme
    baglanti.query(veriguncelle,function(err){});

    var sinirliverigetir = "SELECT * FROM urun LIMIT 3 OFFSET 2";//2. satırdan başlayarak 3 veri getirme
    baglanti.query(sinirliverigetir,function(err,sonuc){console.log(sonuc);});	
    



 */





module.exports=baglanti;
