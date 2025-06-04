
const express = require('express');
const app = express();
const port = 3306;
const baglanti = require('./database');
const mysql = require('mysql');
const ejs = require('ejs');

app.set("view engine", "ejs");

app.get('/',(req,res)=>{
    res.render('index',{data:results});
}); 

app.get('/api/urun', (req, res) => {
    // MySQL sorgusu ile veri çekme
    const query = "SELECT COUNT(urun_id) FROM urun";

    baglanti.query(query, function (err, sonuc) {
        if (err) {
            console.error('Sorgu hatası: ' + err.stack);
            res.status(500).send('Veri çekme hatası');
            return;
        }

        // Veritabanından alınan veriyi JSON formatına çevirip client'a gönder
        res.json(sonuc);
    });
});

    const sqlQuery ='Select urun_ad, urun_fiyat FROM urun';
app.get('/urun', (req, res) => {
    const query = 'SELECT * FROM urun'; // urunler tablosundan veri çekme sorgusu
  
    connection.query(query, (error, results, fields) => {
      if (error) {
        console.error('MySQL query error: ', error);
        res.status(500).send('Internal Server Error');
      } else {
          res.send(results);
      }
    });
  });
  app.use(express.static('public'));

  app.get('/', (req, res) => {
    res.sendFile(__dirname + '/public/index.php');
  });
    
  const products= [
    {urun_ad: 'Ürün 1', urun_fiyat:100},
    {urun_ad: 'Ürün 2', urun_fiyat:100},
    {urun_ad: 'Ürün 3', urun_fiyat:100},
  ];




app.listen(port, () => {
    console.log(`Sunucu http://localhost:${port} adresinde çalışıyor`);
});
