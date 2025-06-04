// main.js içindeki kodlar
function updateTotalProducts(total) {
    $('#totalProducts').text(total);
  }
  
  function updateSalesAmount(amount) {
    $('#salesAmount').text(amount);
  }
  
  function updateTotalOrders(total) {
    $('#totalOrders').text(total);
  }
  
// main.js
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("addItemButton").addEventListener("click", function () {
      // Yeni bir öğe eklemek için fonksiyonu çağır
      addItem();
  });
});

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("addItemButton").addEventListener("click", function () {
      // Yeni bir öğe eklemek için fonksiyonu çağır
      addItem();
  });

  // Doküman yüklendikten sonra düzenleme düğmelerini etkinleştir
  enableEditButtons();
});

function addItem() {
  // Yeni öğenin adını kullanıcıdan alabilirsiniz (örneğin bir prompt ile)
  var newItemName = prompt("Yeni öğe adını girin:");

  if (newItemName !== null) { // Kullanıcı iptali durumunu kontrol et
      if (newItemName.trim() !== "") { // Boşlukları kontrol et
          // Yeni bir liste öğesi oluştur
          var newItem = document.createElement("li");
          newItem.className = "d-flex justify-content-between align-items-center";

          // Liste öğesinin içeriğini oluştur
          newItem.innerHTML = `
              <span>${newItemName}</span>
              <div class="tools">
                  <i class="fas fa-edit edit-button" onclick="editItem(this)"></i>
                  <i class="fas fa-trash-o" onclick="deleteItem(this)"></i>
              </div>
          `;

          // Liste öğesini listeye ekle
          var itemList = document.getElementById("itemList");
          itemList.appendChild(newItem);

          alert("Öğe başarıyla eklendi: " + newItemName);
      } else {
          alert("Geçersiz öğe adı. Lütfen tekrar deneyin.");
      }
  } else {
      alert("İptal edildi veya geçersiz bir değer girdiniz.");
  }
}

function editItem(editButton) {
  var listItem = editButton.closest("li");
  var itemText = listItem.querySelector("span");
  var newItemText = prompt("Öğeyi düzenle:", itemText.textContent);

  if (newItemText !== null) { // Kullanıcı iptali durumunu kontrol et
      if (newItemText.trim() !== "") { // Boşlukları kontrol et
          itemText.textContent = newItemText;
          alert("Öğe başarıyla güncellendi: " + newItemText);
      } else {
          alert("Geçersiz öğe adı. Lütfen tekrar deneyin.");
      }
  } else {
      alert("İptal edildi veya geçersiz bir değer girdiniz.");
  }
}

function deleteItem(deleteButton) {
  var listItem = deleteButton.closest("li");
  listItem.remove();
}
