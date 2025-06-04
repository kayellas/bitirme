const servername = "localhost";
const username = "root";
const password = "";
const database = "kds";
const connection = new mysqli(servername, username, password, database);

// Check connection
if (connection.connect_error) {
    console.error("Connection failed: " + connection.connect_error);
} else {
    const sql = "SELECT * FROM personel";
    const result = connection.query(sql);

    // Check query result
    if (result) {
        const rows = result.fetch_all(MYSQLI_ASSOC);

        rows.forEach(row => {
            console.log("<tr>");
            console.log("<td>" + row["urun_id"] + "</td>");
            console.log("<td>" + row["urun_ad"] + "</td>");
            console.log("<td>" + row["urun_kategori"] + "</td>");
            console.log("<td>" + row["urun_miktar"] + "</td>");
            console.log("<td>" + row["urun_tarih"] + "</td>");
            console.log("<td>");
            console.log("<a href='edit.php?id=" + row["urun_id"] + "' style='color: orange'>Düzenle</a>");
            console.log("<a href='delete.php?id=" + row["urun_id"] + "' style='color: red'>Sil</a>");
            console.log("</td>");
            console.log("</tr>");
        });
    } else {
        console.error("Invalid query: " + connection.error);
    }
    app.get('/urunekle', function (req, res) {
        res.sendFile(path.join(__dirname,"urunekle.html"))
      })
    // Close the database connection
    connection.close();
}
