-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 08 Mar 2025, 22:27:40
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `kds`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bolge`
--

CREATE TABLE `bolge` (
  `bolge_id` int(11) NOT NULL,
  `bolge_ad` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `bolge`
--

INSERT INTO `bolge` (`bolge_id`, `bolge_ad`) VALUES
(1, 'Marmara Bölgesi'),
(2, 'Ege Bölgesi'),
(3, 'Akdeniz Bölgesi'),
(4, 'Karadeniz Bölgesi'),
(5, 'İç Anadolu Bölgesi'),
(6, 'Güneydoğu Anadolu Bölgesi'),
(7, 'Doğu Anadolu Bölgesi');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `iller`
--

CREATE TABLE `iller` (
  `il_id` int(11) NOT NULL,
  `il_ad` varchar(50) NOT NULL,
  `bolge_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `iller`
--

INSERT INTO `iller` (`il_id`, `il_ad`, `bolge_id`) VALUES
(1, 'Adana', 3),
(2, 'Adıyaman', 6),
(3, 'Afyonkarahisar', 2),
(4, 'Ağrı', 7),
(5, 'Amasya', 4),
(6, 'Ankara', 5),
(7, 'Antalya', 3),
(8, 'Artvin', 4),
(9, 'Aydın', 2),
(10, 'Balıkesir', 1),
(11, 'Bilecik', 1),
(12, 'Bingöl', 7),
(13, 'Bitlis', 7),
(14, 'Bolu', 4),
(15, 'Burdur', 3),
(16, 'Bursa', 1),
(17, 'Çanakkale', 1),
(18, 'Çankırı', 5),
(19, 'Çorum', 4),
(20, 'Denizli', 2),
(21, 'Diyarbakır', 6),
(22, 'Edirne', 1),
(23, 'Elazığ', 7),
(24, 'Erzincan', 7),
(25, 'Erzurum', 7),
(26, 'Eskişehir', 5),
(27, 'Gaziantep', 6),
(28, 'Giresun', 4),
(29, 'Gümüşhane', 4),
(30, 'Hakkari', 7),
(31, 'Hatay', 3),
(32, 'Isparta', 3),
(33, 'Mersin', 3),
(34, 'İstanbul', 1),
(35, 'İzmir', 2),
(36, 'Kars', 7),
(37, 'Kastamonu', 4),
(38, 'Kayseri', 5),
(39, 'Kırklareli', 1),
(40, 'Kırşehir', 5),
(41, 'Kocaeli', 1),
(42, 'Konya', 5),
(43, 'Kütahya', 5),
(44, 'Malatya', 7),
(45, 'Manisa', 2),
(46, 'Kahramanmaraş', 3),
(47, 'Mardin', 6),
(48, 'Muğla', 2),
(49, 'Muş', 7),
(50, 'Nevşehir', 5),
(51, 'Niğde', 5),
(52, 'Ordu', 4),
(53, 'Rize', 4),
(54, 'Sakarya', 1),
(55, 'Samsun', 4),
(56, 'Siirt', 7),
(57, 'Sinop', 4),
(58, 'Sivas', 4),
(59, 'Tekirdağ', 1),
(60, 'Tokat', 4),
(61, 'Trabzon', 4),
(62, 'Tunceli', 7),
(63, 'Şanlıurfa', 6),
(64, 'Uşak', 2),
(65, 'Van', 7),
(66, 'Yozgat', 5),
(67, 'Zonguldak', 4),
(68, 'Aksaray', 5),
(69, 'Bayburt', 4),
(70, 'Karaman', 5),
(71, 'Kırıkkale', 5),
(72, 'Batman', 6),
(73, 'Şırnak', 7),
(74, 'Bartın', 4),
(75, 'Ardahan', 7),
(76, 'Iğdır', 7),
(77, 'Yalova', 1),
(78, 'Karabük', 4),
(79, 'Kilis', 6),
(80, 'Osmaniye', 3),
(81, 'Düzce', 4);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategori`
--

CREATE TABLE `kategori` (
  `kategori_id` int(11) NOT NULL,
  `kategori_ad` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `kategori`
--

INSERT INTO `kategori` (`kategori_id`, `kategori_ad`) VALUES
(1, 'softdrinks'),
(2, 'sicak icecekler'),
(3, 'alkollu');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanici`
--

CREATE TABLE `kullanici` (
  `kullanici_adi` varchar(45) NOT NULL,
  `sifre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `kullanici`
--

INSERT INTO `kullanici` (`kullanici_adi`, `sifre`) VALUES
('zeynep', '123');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `location`
--

CREATE TABLE `location` (
  `firma_id` int(11) NOT NULL,
  `firma_ad` varchar(45) NOT NULL,
  `il_ad` varchar(45) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `kategori_ad` varchar(45) NOT NULL,
  `arac_sayisi` int(11) NOT NULL,
  `lat` float NOT NULL,
  `lng` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `location`
--

INSERT INTO `location` (`firma_id`, `firma_ad`, `il_ad`, `kategori_id`, `kategori_ad`, `arac_sayisi`, `lat`, `lng`) VALUES
(1, 'ABC Lojistik A.Ş.', 'İstanbul', 1, 'softdrinks', 50, 41.0082, 28.9784),
(2, 'XYZ Nakliye Ltd. Şti.', 'Ankara', 2, 'sicak_icecekler', 30, 39.9334, 32.8597),
(3, 'Kapadokya Turizm ve Lojistik', 'Nevşehir', 3, 'alkollu', 20, 38.9637, 34.7668),
(4, 'Mavi Deniz Taşıma A.Ş.', 'Antalya', 3, 'alkollu', 15, 36.8856, 30.7074),
(5, 'Ege Bölgesi Taşıma Ltd. Şti.', 'İzmir', 3, 'alkollu', 10, 38.4192, 27.1287),
(6, 'Koc Holding', 'Ankara', 1, 'softfrinks', 10, 39.9208, 32.8541),
(7, 'Vestel', 'İstanbul', 1, 'softdrinks', 5, 41.0082, 28.9784),
(8, 'Ege Seramik', 'İzmir', 2, 'sicak icecekler', 8, 38.4192, 27.1287),
(9, 'Siemens', 'Berlin', 1, 'softdrinks', 12, 52.52, 13.405),
(10, 'L’Oréal', 'Paris', 3, 'alkollu', 7, 48.8566, 2.3522),
(11, 'Google', 'New York', 3, 'alkollu', 15, 40.7128, -74.006),
(12, 'Sony', 'Tokyo', 3, 'alkollu', 20, 35.6895, 139.692),
(13, 'BP', 'Londra', 2, 'sicak icecekler', 9, 51.5074, -0.1278),
(14, 'Qantas', 'Sidney', 1, 'softdrinks', 11, -33.8688, 151.209),
(15, 'Rogers Communications', 'Toronto', 2, 'sicak icecekler', 6, 43.6532, -79.3832);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparis`
--

CREATE TABLE `siparis` (
  `siparis_id` int(11) NOT NULL,
  `siparis_adet` int(11) DEFAULT NULL,
  `siparis_tarihi` date DEFAULT NULL,
  `urun_id` int(11) NOT NULL,
  `urun_fiyat` int(11) DEFAULT NULL,
  `tedarik_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `stok`
--

CREATE TABLE `stok` (
  `firma_id` int(25) NOT NULL,
  `firma_ad` varchar(45) NOT NULL,
  `stok_miktar` int(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tedarik`
--

CREATE TABLE `tedarik` (
  `tedarik_id` int(11) NOT NULL,
  `tedarik_ad` varchar(45) NOT NULL,
  `il_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `tedarik`
--

INSERT INTO `tedarik` (`tedarik_id`, `tedarik_ad`, `il_id`) VALUES
(1, 'Anadolu Ticaret AŞ', 6),
(2, 'İstanbul Lojistik Ltd. Şti.', 34),
(3, 'Ege İthalat İhracat Co.', 35),
(4, 'Karadeniz Tedarik Hizmetleri', 37),
(5, 'Kapadokya Ticaret Limited', 50),
(6, 'Akdeniz Toptan Pazarlama', 6),
(7, 'Anzer Ticaret Kooperatifi', 45),
(8, 'AYDA MESRUBAT GIDA SAN.TIC LTD', 35);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urun`
--

CREATE TABLE `urun` (
  `urun_id` int(11) NOT NULL,
  `urun_ad` varchar(45) DEFAULT NULL,
  `urun_fiyat` varchar(45) DEFAULT NULL,
  `kategori_id` int(11) NOT NULL,
  `kategori_ad` varchar(45) NOT NULL,
  `firma_id` int(11) NOT NULL,
  `urun_miktar` int(11) DEFAULT NULL,
  `max_urun_miktar` int(25) NOT NULL,
  `urun_tarih` date DEFAULT NULL,
  `stok` int(11) GENERATED ALWAYS AS (`max_urun_miktar` - `urun_miktar`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `urun`
--

INSERT INTO `urun` (`urun_id`, `urun_ad`, `urun_fiyat`, `kategori_id`, `kategori_ad`, `firma_id`, `urun_miktar`, `max_urun_miktar`, `urun_tarih`) VALUES
(1, 'Pepsi', '12000', 1, 'softdrinks', 0, 4000, 5000, '2022-12-01'),
(2, 'Cola', '5300', 1, 'softdrinks', 0, 1500, 5000, '2022-12-01'),
(3, 'Sprite', '4000', 1, 'softdrinks', 0, 1800, 5000, '2022-12-01'),
(4, 'Schweppes', '5200', 1, 'softdrinks', 0, 1500, 5000, '2022-12-01'),
(5, 'Ice Tea', '4100', 1, 'softdrinks', 0, 2500, 5000, '2022-12-01'),
(6, 'Fuse Tea', '3500', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(7, 'Fanta', '2500', 1, 'softdrinks', 0, 3400, 5000, '2022-12-01'),
(8, 'Mountain Dew', '6500', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(9, 'Dr. Pepper', '5500', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(10, '7UP', '2700', 1, 'softdrinks', 0, 2900, 5000, '2022-12-01'),
(11, 'Club Soda', '1500', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(12, 'Root Beer', '6500', 1, 'softdrinks', 0, 4000, 5000, '2022-12-01'),
(13, 'Ginger Ale', '1000', 1, 'softdrinks', 0, 500, 5000, '2022-12-01'),
(14, 'Tonic Water', '6000', 1, 'softdrinks', 0, 1500, 5000, '2022-12-01'),
(15, 'Cream Soda', '5500', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(16, 'Orange Crush', '4500', 1, 'softdrinks', 0, 700, 5000, '2022-12-01'),
(17, 'Grape Soda', '3000', 1, 'softdrinks', 0, 500, 5000, '2022-12-01'),
(18, 'Lemon-Lime Soda', '4000', 1, 'softdrinks', 0, 900, 5000, '2022-12-01'),
(19, 'Cherry Cola', '3000', 1, 'softdrinks', 0, 550, 5000, '2022-12-01'),
(20, 'Birch Beer', '4000', 1, 'softdrinks', 0, 600, 5000, '2022-12-01'),
(21, 'Iced Coffee', '5000', 1, 'softdrinks', 0, 1500, 5000, '2022-12-01'),
(22, 'Fruit Punch', '4000', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(23, 'Cranberry Juice', '6000', 1, 'softdrinks', 0, 1000, 5000, '2022-12-01'),
(24, 'Pineapple Juice', '1500', 1, 'softdrinks', 0, 600, 5000, '2022-12-01'),
(25, 'Coconut Water', '5000', 1, 'softdrinks', 0, 800, 5000, '2022-12-01'),
(26, 'Aloe Vera Juice', '5000', 1, 'softdrinks', 0, 1000, 5000, '2023-12-01'),
(27, 'Peach Iced Tea', '4500', 1, 'softdrinks', 0, 1000, 5000, '2023-12-01'),
(28, 'Raspberry Iced Tea', '7000', 1, 'softdrinks', 0, 700, 5000, '2023-12-01'),
(29, 'Strawberry Lemonade', '4500', 1, 'softdrinks', 0, 800, 5000, '2023-12-01'),
(30, 'Blueberry Lemonade', '6000', 1, 'softdrinks', 0, 600, 5000, '2023-12-01'),
(31, 'Watermelon Juice', '7000', 1, 'softdrinks', 0, 590, 5000, '2023-12-01'),
(32, 'Passion Fruit Juice', '5000', 1, 'softdrinks', 0, 600, 5000, '2023-12-01'),
(33, 'Kiwi-Strawberry Soda', '5500', 1, 'softdrinks', 0, 1100, 5000, '2023-12-01'),
(34, 'Raspberry Ginger Ale', '5500', 1, 'softdrinks', 0, 1100, 5000, '2023-12-01'),
(35, 'Black Cherry Soda', '5500', 1, 'softdrinks', 0, 550, 5000, '2023-12-01'),
(36, 'Vanilla Cream Soda', '8900', 1, 'softdrinks', 0, 2000, 5000, '2023-12-01'),
(37, 'Pomegranate Juice', '6000', 1, 'softdrinks', 0, 690, 5000, '2023-12-01'),
(38, 'Rosemary Grapefruit Sparkler', '7000', 1, 'softdrinks', 0, 1000, 5000, '2023-12-01'),
(39, 'Blue Raspberry Lemonade', '9000', 1, 'softdrinks', 0, 2500, 5000, '2023-12-01'),
(40, 'Hibiscus Iced Tea', '12000', 1, 'softdrinks', 0, 3500, 5000, '2023-12-01'),
(41, 'Türk Kahvesi', '12000', 2, 'sicak icecekler', 0, 4000, 5000, '2023-12-01'),
(42, 'Türk Kahvesi', '11000', 2, 'sicak icecekler', 0, 3500, 5000, '2022-12-01'),
(43, 'Mısır İnciri Çayı', '9000', 2, 'sicak icecekler', 0, 1000, 5000, '2023-12-01'),
(44, 'Espresso', '13000', 2, 'sicak icecekler', 0, 3000, 5000, '2023-12-01'),
(45, 'Americano', '13000', 2, 'sicak icecekler', 0, 8000, 5000, '2023-12-01'),
(46, 'Latte', '4500', 2, 'sicak icecekler', 0, 5500, 5000, '2022-12-01'),
(47, 'Cappuccino', '9000', 2, 'sicak icecekler', 0, 4000, 5000, '2023-12-01'),
(48, 'Cappuccino', '7000', 2, 'sicak icecekler', 0, 3500, 5000, '2022-12-01'),
(49, 'Macchiato', '14000', 2, 'sicak icecekler', 0, 4000, 5000, '2023-12-01'),
(50, 'Mocha', '13000', 2, 'sicak icecekler', 0, 5000, 5000, '2023-12-01'),
(51, 'Flat White', '12000', 2, 'sicak icecekler', 0, 4000, 5000, '2023-12-01'),
(52, 'Filtre Kahve', '25000', 2, 'sicak icecekler', 0, 9000, 5000, '2023-12-01'),
(53, 'Filtre Kahve', '23000', 2, 'sicak icecekler', 0, 7000, 5000, '2022-12-01'),
(54, 'White Chocolate Mocha', '13000', 2, 'sicak icecekler', 0, 6000, 5000, '2023-12-01'),
(55, 'Yerba Mate Çayı', '4300', 2, 'sicak icecekler', 0, 700, 5000, '2023-12-01'),
(56, 'Zencefilli Sıcak Elma İçkisi', '9900', 2, 'sicak icecekler', 0, 8090, 5000, '2023-12-01'),
(57, 'Nane Çikolatalı Sıcak İçecek', '8990', 2, 'sicak icecekler', 0, 5450, 5000, '2023-12-01'),
(58, 'VODKA ABSOLUT PEAR 70 CL', '65.685,98', 3, 'alkollu', 0, 3750, 5000, '2022-01-01'),
(59, 'VODKA ABSOLUT PEAR 70 CL', '86.945,95', 3, 'alkollu', 0, 4500, 5000, '2023-01-01'),
(60, 'VODKA ABSOLUT PEAR 70 CL', '75.000', 3, 'alkollu', 0, 4500, 5000, '2024-01-01'),
(61, 'WHISKEY JAMESON 70 CL', '51.954,75', 3, 'alkollu', 0, 4500, 5000, '2022-01-01'),
(62, 'WHISKEY JAMESON 70 CL', '67.900', 3, 'alkollu', 0, 4500, 5000, '2023-01-01'),
(63, 'WHISKEY JAMESON 70 CL', '81.874', 3, 'alkollu', 0, 8750, 5000, '2024-01-01'),
(64, 'TEQUILA OLMECA 70 CL', '38.789', 3, 'alkollu', 0, 4500, 5000, '2022-01-01'),
(65, 'TEQUILA OLMECA 70 CL', '53.689', 3, 'alkollu', 0, 7900, 5000, '2023-01-01'),
(66, 'TEQUILA OLMECA 70 CL', '78.859', 3, 'alkollu', 0, 8980, 5000, '2024-01-01'),
(67, 'VODKA ABSOLUT RASPBERRY 70 CL', '45.978', 3, 'alkollu', 0, 2500, 5000, '2024-01-01');

--
-- Tetikleyiciler `urun`
--
DELIMITER $$
CREATE TRIGGER `update_stok` BEFORE INSERT ON `urun` FOR EACH ROW BEGIN
    SET NEW.stok = NEW.max_urun_miktar - NEW.urun_miktar;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_stok_on_update` BEFORE UPDATE ON `urun` FOR EACH ROW BEGIN
    SET NEW.stok = NEW.max_urun_miktar - NEW.urun_miktar;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urun_miktar`
--

CREATE TABLE `urun_miktar` (
  `urun_id` int(11) NOT NULL,
  `toplam_urun_sayisi` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `bolge`
--
ALTER TABLE `bolge`
  ADD PRIMARY KEY (`bolge_id`);

--
-- Tablo için indeksler `iller`
--
ALTER TABLE `iller`
  ADD PRIMARY KEY (`il_id`),
  ADD KEY `bolge_id` (`bolge_id`);

--
-- Tablo için indeksler `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`kategori_id`);

--
-- Tablo için indeksler `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`firma_id`),
  ADD KEY `il_ad` (`il_ad`),
  ADD KEY `il_ad_2` (`il_ad`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Tablo için indeksler `siparis`
--
ALTER TABLE `siparis`
  ADD PRIMARY KEY (`siparis_id`),
  ADD KEY `urun_id` (`urun_id`,`tedarik_id`),
  ADD KEY `urun_id_2` (`urun_id`,`tedarik_id`),
  ADD KEY `urun_id_3` (`urun_id`,`tedarik_id`),
  ADD KEY `tedarik_id` (`tedarik_id`);

--
-- Tablo için indeksler `stok`
--
ALTER TABLE `stok`
  ADD KEY `firma_id` (`firma_id`),
  ADD KEY `firma_id_2` (`firma_id`);

--
-- Tablo için indeksler `tedarik`
--
ALTER TABLE `tedarik`
  ADD PRIMARY KEY (`tedarik_id`),
  ADD KEY `il_id` (`il_id`);

--
-- Tablo için indeksler `urun`
--
ALTER TABLE `urun`
  ADD PRIMARY KEY (`urun_id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `kategori_id_2` (`kategori_id`),
  ADD KEY `firma_id` (`firma_id`);

--
-- Tablo için indeksler `urun_miktar`
--
ALTER TABLE `urun_miktar`
  ADD KEY `urun_id` (`urun_id`),
  ADD KEY `urun_id_2` (`urun_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `iller`
--
ALTER TABLE `iller`
  MODIFY `il_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Tablo için AUTO_INCREMENT değeri `location`
--
ALTER TABLE `location`
  MODIFY `firma_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `siparis`
--
ALTER TABLE `siparis`
  MODIFY `siparis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tedarik`
--
ALTER TABLE `tedarik`
  MODIFY `tedarik_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `urun`
--
ALTER TABLE `urun`
  MODIFY `urun_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `iller`
--
ALTER TABLE `iller`
  ADD CONSTRAINT `iller_ibfk_1` FOREIGN KEY (`bolge_id`) REFERENCES `bolge` (`bolge_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `kategori`
--
ALTER TABLE `kategori`
  ADD CONSTRAINT `kategori_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `location` (`kategori_id`);

--
-- Tablo kısıtlamaları `siparis`
--
ALTER TABLE `siparis`
  ADD CONSTRAINT `siparis_ibfk_1` FOREIGN KEY (`tedarik_id`) REFERENCES `tedarik` (`tedarik_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `siparis_ibfk_2` FOREIGN KEY (`urun_id`) REFERENCES `urun` (`urun_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `stok`
--
ALTER TABLE `stok`
  ADD CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`firma_id`) REFERENCES `location` (`firma_id`);

--
-- Tablo kısıtlamaları `urun`
--
ALTER TABLE `urun`
  ADD CONSTRAINT `urun_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`kategori_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `urun_miktar`
--
ALTER TABLE `urun_miktar`
  ADD CONSTRAINT `urun_miktar_ibfk_1` FOREIGN KEY (`urun_id`) REFERENCES `urun` (`urun_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
