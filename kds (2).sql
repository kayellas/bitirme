-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 04 Haz 2025, 18:58:07
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

DELIMITER $$
--
-- Yordamlar
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetCategoryComparison` (IN `p_year` INT, IN `p_month` INT)   BEGIN
    SELECT 
        k.kategori_ad,
        SUM(s.toplam_tutar) as kategori_ciro,
        SUM(s.siparis_adet) as kategori_adet,
        COUNT(s.siparis_id) as siparis_sayisi,
        ROUND((SUM(s.toplam_tutar) / (SELECT SUM(toplam_tutar) FROM siparis WHERE YEAR(siparis_tarihi) = p_year AND (p_month IS NULL OR MONTH(siparis_tarihi) = p_month) AND siparis_durumu = 'teslim_edildi')) * 100, 2) as yuzde_payi
    FROM siparis s
    INNER JOIN urun u ON s.urun_id = u.urun_id
    INNER JOIN kategori k ON u.kategori_id = k.kategori_id
    WHERE YEAR(s.siparis_tarihi) = p_year
    AND (p_month IS NULL OR MONTH(s.siparis_tarihi) = p_month)
    AND s.siparis_durumu = 'teslim_edildi'
    GROUP BY k.kategori_id
    ORDER BY kategori_ciro DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMonthlySalesData` (IN `p_year` INT, IN `p_category_id` INT)   BEGIN
    SELECT 
        MONTH(s.siparis_tarihi) as ay,
        MONTHNAME(s.siparis_tarihi) as ay_adi,
        SUM(s.toplam_tutar) as toplam_ciro,
        SUM(s.siparis_adet) as toplam_adet,
        COUNT(s.siparis_id) as siparis_sayisi
    FROM siparis s
    INNER JOIN urun u ON s.urun_id = u.urun_id
    WHERE YEAR(s.siparis_tarihi) = p_year
    AND (p_category_id IS NULL OR u.kategori_id = p_category_id)
    AND s.siparis_durumu = 'teslim_edildi'
    GROUP BY MONTH(s.siparis_tarihi)
    ORDER BY ay;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetYearlySalesData` (IN `p_start_year` INT, IN `p_end_year` INT, IN `p_category_id` INT)   BEGIN
    SELECT 
        YEAR(s.siparis_tarihi) as yil,
        SUM(s.toplam_tutar) as toplam_ciro,
        SUM(s.siparis_adet) as toplam_adet,
        COUNT(s.siparis_id) as siparis_sayisi,
        COUNT(DISTINCT s.musteri_id) as aktif_musteri
    FROM siparis s
    INNER JOIN urun u ON s.urun_id = u.urun_id
    WHERE YEAR(s.siparis_tarihi) BETWEEN p_start_year AND p_end_year
    AND (p_category_id IS NULL OR u.kategori_id = p_category_id)
    AND s.siparis_durumu = 'teslim_edildi'
    GROUP BY YEAR(s.siparis_tarihi)
    ORDER BY yil;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_dashboard_summary` ()   BEGIN
    -- Bugünün özetini hesapla
    INSERT INTO dashboard_summary (summary_date, kategori_id, total_sales, total_revenue, total_orders, avg_order_value, unique_customers)
    SELECT 
        CURDATE(),
        u.kategori_id,
        SUM(s.siparis_adet),
        SUM(s.toplam_tutar),
        COUNT(s.siparis_id),
        AVG(s.toplam_tutar),
        COUNT(DISTINCT s.musteri_id)
    FROM siparis s
    JOIN urun u ON s.urun_id = u.urun_id
    WHERE DATE(s.siparis_tarihi) = CURDATE()
    AND s.siparis_durumu = 'teslim_edildi'
    GROUP BY u.kategori_id
    ON DUPLICATE KEY UPDATE
        total_sales = VALUES(total_sales),
        total_revenue = VALUES(total_revenue),
        total_orders = VALUES(total_orders),
        avg_order_value = VALUES(avg_order_value),
        unique_customers = VALUES(unique_customers);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `alt_kategori`
--

CREATE TABLE `alt_kategori` (
  `alt_kategori_id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `alt_kategori_ad` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `alt_kategori`
--

INSERT INTO `alt_kategori` (`alt_kategori_id`, `kategori_id`, `alt_kategori_ad`) VALUES
(1, 1, 'Gazlı İçecekler'),
(2, 1, 'Meyve Suları'),
(3, 1, 'Enerji İçecekleri'),
(4, 2, 'Kahve'),
(5, 2, 'Çay'),
(6, 2, 'Bitki çayı'),
(7, 3, 'Kokteyller'),
(8, 3, 'Şaraplar'),
(9, 3, 'Bira'),
(10, 3, 'Sert İçkiler');

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
(1, 'Soft Drinks'),
(2, 'Sıcak İçecekler'),
(3, 'Alkollü');

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
  `type` varchar(45) NOT NULL,
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

INSERT INTO `location` (`firma_id`, `firma_ad`, `type`, `il_ad`, `kategori_id`, `kategori_ad`, `arac_sayisi`, `lat`, `lng`) VALUES
(1, 'ABC Lojistik A.Ş.', 'Tedarik', 'İstanbul', 1, 'Soft Drinks', 50, 41.0082, 28.9784),
(2, 'XYZ Nakliye Ltd. Şti.', 'Tedarik', 'Ankara', 2, 'Sıcak İçecekler', 30, 39.9334, 32.8597),
(3, 'Kapadokya Turizm ve Lojistik', 'Tedarik', 'Nevşehir', 3, 'Alkollü', 20, 38.9637, 34.7668),
(4, 'Mavi Deniz Taşıma A.Ş.', 'Tedarik', 'Antalya', 3, 'Alkollü', 15, 36.8856, 30.7074),
(5, 'Ege Bölgesi Taşıma Ltd. Şti.', 'Tedarik', 'İzmir', 3, 'Alkollü', 10, 38.4192, 27.1287),
(6, 'Koc Holding', 'Tedarik', 'Ankara', 1, 'Soft Drinks', 10, 39.9208, 32.8541),
(13, 'BP', 'Tedarik', 'Londra', 2, 'Sıcak İçecekler', 9, 51.5074, -0.1278),
(14, 'Qantas', 'Tedarik', 'Sidney', 1, 'Soft Drinks', 11, -33.8688, 151.209),
(15, 'Rogers Communications', 'Tedarik', 'Toronto', 2, 'Sıcak İçecekler', 6, 43.6532, -79.3832),
(16, 'PepsiCo Türkiye', 'Tedarik', 'İstanbul', 1, 'Soft Drinks', 40, 41.0151, 28.9795),
(17, 'Coca-Cola İçecek A.Ş.', 'Tedarik', 'İstanbul', 1, 'Soft Drinks', 50, 41.0086, 28.9802),
(18, 'Doğuş Çay', 'Tedarik', 'Rize', 2, 'Sıcak İçecekler', 25, 41.0201, 40.5234),
(19, 'Kuru Kahveci Mehmet Efendi', 'Tedarik', 'İstanbul', 2, 'Sıcak İçecekler', 20, 41.012, 28.976),
(20, 'Efes Pilsen', 'Tedarik', 'İzmir', 3, 'Alkollü', 35, 38.419, 27.1285),
(21, 'Mey İçki (Rakı)', 'Tedarik', 'Tekirdağ', 3, 'Alkollü', 30, 40.978, 27.5111),
(22, 'Lipton Çay Fabrikası', 'Tedarik', 'Balıkesir', 2, 'Sıcak İçecekler', 15, 39.6484, 27.8826),
(23, 'Red Bull Türkiye', 'Tedarik', 'Ankara', 1, 'Soft Drinks', 18, 39.9208, 32.8541),
(24, 'Nestlé Kahve', 'Tedarik', 'Bursa', 2, 'Sıcak İçecekler', 28, 40.1828, 29.0662),
(25, 'Tuborg Türkiye', 'Tedarik', 'İzmir', 3, 'Alkollü', 32, 38.4189, 27.1287),
(26, 'Uludağ İçecek A.Ş.', 'Tedarik', 'Bursa', 1, 'Soft Drinks', 22, 40.1828, 29.0662),
(27, 'Dimes Gıda Sanayi', 'Tedarik', 'Tokat', 1, 'Soft Drinks', 18, 40.3139, 36.5544),
(28, 'Pınar Su ve İçecek', 'Tedarik', 'İzmir', 1, 'Soft Drinks', 20, 38.4192, 27.1287),
(29, 'Çaykur (Çay Sanayi)', 'Tedarik', 'Rize', 2, 'Sıcak İçecekler', 30, 41.0201, 40.5234),
(30, 'Tchibo Türkiye', 'Tedarik', 'İstanbul', 2, 'Sıcak İçecekler', 25, 41.0082, 28.9784),
(31, 'Starbucks Türkiye', 'Tedarik', 'İstanbul', 2, 'Sıcak İçecekler', 35, 41.0086, 28.9802),
(32, 'Brew Coffee Works', 'Tedarik', 'Ankara', 2, 'sicak icecekler', 15, 39.9334, 32.8597),
(33, 'Mey Diageo (Viski & Rakı)', 'Tedarik', 'İzmir', 3, 'alkollu', 28, 38.4192, 27.1287),
(34, 'Sarar Şarapçılık', 'Tedarik', 'Manisa', 3, 'alkollu', 17, 38.6191, 27.4289),
(35, 'Pamukkale Şarapları', 'Tedarik', 'Denizli', 3, 'alkollu', 22, 37.7765, 29.0864),
(36, 'Kavaklıdere Şarapları', 'Tedarik', 'Ankara', 3, 'alkollu', 20, 39.9334, 32.8597),
(37, 'Çaykur Çay İşletmeleri', 'Tedarik', 'Rize', 2, 'Sıcak İçecekler', 45, 41.0201, 40.5234),
(38, 'Doğadan Bitki Çayları', 'Tedarik', 'İstanbul', 2, 'Sıcak İçecekler', 20, 41.0082, 28.9784),
(39, 'Beta Tea', 'Tedarik', 'İzmir', 2, 'Sıcak İçecekler', 18, 38.4192, 27.1287),
(40, 'Selamlique Ottoman Coffee', 'Tedarik', 'İstanbul', 2, 'Sıcak İçecekler', 15, 41.0086, 28.9802),
(41, 'Fazlıoğlu Kahve', 'Tedarik', 'İstanbul', 2, 'Sıcak İçecekler', 12, 41.012, 28.976),
(42, 'Gloria Jeans Coffee', 'Tedarik', 'Ankara', 2, 'Sıcak İçecekler', 25, 39.9334, 32.8597),
(43, 'Erikli Su ve İçecek', 'Tedarik', 'Denizli', 1, 'Soft Drinks', 30, 37.7765, 29.0864),
(44, 'Beypazarı Maden Suyu', 'Tedarik', 'Ankara', 1, 'Soft Drinks', 22, 39.9334, 32.8597),
(45, 'Damla Su', 'Tedarik', 'Antalya', 1, 'Soft Drinks', 18, 36.8856, 30.7074),
(46, 'Doluca Şarapları', 'Tedarik', 'İstanbul', 3, 'Alkollü', 25, 41.0082, 28.9784),
(47, 'Kayra Şarapları', 'Tedarik', 'Denizli', 3, 'Alkollü', 20, 37.7765, 29.0864),
(48, 'Bomonti Bira', 'Tedarik', 'İstanbul', 3, 'Alkollü', 28, 41.0086, 28.9802);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musteri`
--

CREATE TABLE `musteri` (
  `musteri_id` int(2) NOT NULL,
  `musteri_ad` varchar(13) DEFAULT NULL,
  `musteri_email` varchar(23) DEFAULT NULL,
  `musteri_telefon` bigint(11) DEFAULT NULL,
  `il_id` int(2) DEFAULT NULL,
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `musteri`
--

INSERT INTO `musteri` (`musteri_id`, `musteri_ad`, `musteri_email`, `musteri_telefon`, `il_id`, `kayit_tarihi`) VALUES
(1, 'Ahmet Yılmaz', 'ahmet.yilmaz@email.com', 5321234567, 34, '2025-06-02 08:53:02'),
(2, 'Fatma Kaya', 'fatma.kaya@email.com', 5339876543, 6, '2025-06-02 08:53:02'),
(3, 'Mehmet Öz', 'mehmet.oz@email.com', 5445678901, 35, '2025-06-02 08:53:02'),
(4, 'Ayşe Demir', 'ayse.demir@email.com', 5556789012, 7, '2025-06-02 08:53:02'),
(5, 'Ali Şahin', 'ali.sahin@email.com', 5667890123, 16, '2025-06-02 08:53:02'),
(6, 'Zeynep Arslan', 'zeynep.arslan@email.com', 5778901234, 20, '2025-06-02 08:53:02'),
(7, 'Hasan Çelik', 'hasan.celik@email.com', 5889012345, 42, '2025-06-02 08:53:02'),
(8, 'Emine Yıldız', 'emine.yildiz@email.com', 5990123456, 55, '2025-06-02 08:53:02'),
(9, 'Mustafa Aydın', 'mustafa.aydin@email.com', 5301234567, 61, '2025-06-02 08:53:02'),
(10, 'Elif Polat', 'elif.polat@email.com', 5412345678, 27, '2025-06-02 08:53:02'),
(11, 'Ahmet Yılmaz', 'ahmet.yilmaz@email.com', 5321234567, 34, '2025-06-04 13:51:36'),
(12, 'Fatma Kaya', 'fatma.kaya@email.com', 5308789543, 6, '2025-06-04 13:51:36'),
(13, 'Mehmet Öz', 'mehmet.oz@email.com', 5456789801, 35, '2025-06-04 13:51:36'),
(14, 'Ayşe Demir', 'ayse.demir@email.com', 5567890123, 7, '2025-06-04 13:51:36'),
(15, 'Ali Şahin', 'ali.sahin@email.com', 5678901234, 16, '2025-06-04 13:51:36'),
(16, 'Zeynep Arslan', 'zeynep.arslan@email.com', 5789012345, 20, '2025-06-04 13:51:36'),
(17, 'Hasan Çelik', 'hasan.celik@email.com', 5890123456, 42, '2025-06-04 13:51:36'),
(18, 'Emine Yıldız', 'emine.yildiz@email.com', 5901234567, 55, '2025-06-04 13:51:36'),
(19, 'Mustafa Aydın', 'mustafa.aydin@email.com', 5012345678, 61, '2025-06-04 13:51:36'),
(20, 'Elif Polat', 'elif.polat@email.com', 5412345679, 27, '2025-06-04 13:51:36'),
(21, 'Osman Kara', 'osman.kara@email.com', 5323456789, 33, '2025-06-04 13:51:36'),
(22, 'Seda Güneş', 'seda.gunes@email.com', 5434567890, 26, '2025-06-04 13:51:36');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `otel`
--

CREATE TABLE `otel` (
  `otel_id` int(11) NOT NULL,
  `otel_ad` varchar(100) DEFAULT NULL,
  `il_ad` varchar(50) DEFAULT NULL,
  `lat` decimal(9,6) DEFAULT NULL,
  `lng` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `otel`
--

INSERT INTO `otel` (`otel_id`, `otel_ad`, `il_ad`, `lat`, `lng`) VALUES
(90001, 'Swissôtel The Bosphorus', 'İstanbul', 41.042500, 29.008600),
(90002, 'Swissôtel Büyük Efes', 'İzmir', 38.428100, 27.133700),
(90003, 'Swissôtel Resort Bodrum Beach', 'Muğla', 37.063600, 27.266700),
(90004, 'Swissôtel The Stamford', 'Singapur', 1.293300, 103.853000),
(90005, 'Swissôtel Krasnye Holmy', 'Moskova', 55.733400, 37.643900),
(90006, 'Swissôtel Tallinn', 'Tallinn', 59.432900, 24.761500),
(90007, 'Swissôtel Amsterdam', 'Amsterdam', 52.373500, 4.893400),
(90008, 'Swissôtel Clark', 'Clark', 15.192000, 120.524000),
(90009, 'Swissôtel Chicago', 'Chicago', 41.887500, -87.619100),
(90010, 'Swissôtel Makkah', 'Mekke', 21.419600, 39.826200),
(90011, 'Swissôtel Al Maqam', 'Mekke', 21.420300, 39.826800),
(90012, 'Swissôtel Living Jeddah', 'Cidde', 21.552400, 39.155300),
(90013, 'Swissôtel Nankai Osaka', 'Osaka', 34.664600, 135.501900),
(90014, 'Swissôtel Sydney', 'Sydney', -33.870800, 151.207300);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparis`
--

CREATE TABLE `siparis` (
  `siparis_id` int(11) NOT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `siparis_adet` int(11) DEFAULT NULL,
  `siparis_tarihi` date DEFAULT NULL,
  `urun_id` int(11) NOT NULL,
  `urun_fiyat` int(11) DEFAULT NULL,
  `tedarik_id` int(11) NOT NULL,
  `toplam_tutar` decimal(10,2) GENERATED ALWAYS AS (`siparis_adet` * `urun_fiyat`) STORED,
  `siparis_durumu` enum('beklemede','onaylandi','kargoda','teslim_edildi','iptal') DEFAULT 'beklemede',
  `teslim_tarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `siparis`
--

INSERT INTO `siparis` (`siparis_id`, `musteri_id`, `siparis_adet`, `siparis_tarihi`, `urun_id`, `urun_fiyat`, `tedarik_id`, `siparis_durumu`, `teslim_tarihi`) VALUES
(1, 1, 100, '2022-01-15', 1, 12000, 1, 'teslim_edildi', '2022-01-18'),
(2, 2, 50, '2022-01-20', 2, 5300, 2, 'teslim_edildi', '2022-01-23'),
(3, 3, 75, '2022-02-10', 41, 11000, 3, 'teslim_edildi', '2022-02-13'),
(4, 1, 200, '2022-02-25', 3, 4000, 1, 'teslim_edildi', '2022-02-28'),
(5, 4, 30, '2022-03-05', 58, 65685, 4, 'teslim_edildi', '2022-03-08'),
(6, 5, 120, '2022-03-20', 4, 5200, 2, 'teslim_edildi', '2022-03-23'),
(7, 2, 80, '2022-04-10', 46, 4500, 3, 'teslim_edildi', '2022-04-13'),
(8, 6, 150, '2022-04-25', 7, 2500, 1, 'teslim_edildi', '2022-04-28'),
(9, 3, 60, '2022-05-15', 61, 51954, 4, 'teslim_edildi', '2022-05-18'),
(10, 7, 90, '2022-05-30', 8, 6500, 2, 'teslim_edildi', '2022-06-02'),
(11, 4, 110, '2022-06-12', 10, 2700, 3, 'teslim_edildi', '2022-06-15'),
(12, 8, 70, '2022-06-28', 48, 7000, 1, 'teslim_edildi', '2022-07-01'),
(13, 5, 200, '2022-07-10', 12, 6500, 4, 'teslim_edildi', '2022-07-13'),
(14, 9, 85, '2022-07-25', 53, 23000, 2, 'teslim_edildi', '2022-07-28'),
(15, 6, 130, '2022-08-08', 15, 5500, 3, 'teslim_edildi', '2022-08-11'),
(16, 10, 95, '2022-08-22', 17, 3000, 1, 'teslim_edildi', '2022-08-25'),
(17, 1, 160, '2022-09-05', 64, 38789, 4, 'teslim_edildi', '2022-09-08'),
(18, 7, 75, '2022-09-20', 19, 3000, 2, 'teslim_edildi', '2022-09-23'),
(19, 2, 140, '2022-10-12', 21, 5000, 3, 'teslim_edildi', '2022-10-15'),
(20, 8, 55, '2022-10-28', 24, 1500, 1, 'teslim_edildi', '2022-10-31'),
(21, 3, 180, '2022-11-10', 25, 5000, 4, 'teslim_edildi', '2022-11-13'),
(22, 9, 65, '2022-11-25', 42, 11000, 2, 'teslim_edildi', '2022-11-28'),
(23, 4, 220, '2022-12-08', 1, 12000, 3, 'teslim_edildi', '2022-12-11'),
(24, 10, 90, '2022-12-22', 2, 5300, 1, 'teslim_edildi', '2022-12-25'),
(25, 5, 150, '2023-01-08', 26, 5000, 4, 'teslim_edildi', '2023-01-11'),
(26, 6, 80, '2023-01-22', 59, 86945, 2, 'teslim_edildi', '2023-01-25'),
(27, 1, 120, '2023-02-05', 27, 4500, 3, 'teslim_edildi', '2023-02-08'),
(28, 7, 200, '2023-02-18', 28, 7000, 1, 'teslim_edildi', '2023-02-21'),
(29, 2, 90, '2023-03-12', 62, 67900, 4, 'teslim_edildi', '2023-03-15'),
(30, 8, 110, '2023-03-25', 29, 4500, 2, 'teslim_edildi', '2023-03-28'),
(31, 3, 170, '2023-04-08', 30, 6000, 3, 'teslim_edildi', '2023-04-11'),
(32, 9, 85, '2023-04-22', 65, 53689, 1, 'teslim_edildi', '2023-04-25'),
(33, 4, 140, '2023-05-15', 31, 7000, 4, 'teslim_edildi', '2023-05-18'),
(34, 10, 95, '2023-05-28', 32, 5000, 2, 'teslim_edildi', '2023-05-31'),
(35, 5, 160, '2023-06-10', 33, 5500, 3, 'teslim_edildi', '2023-06-13'),
(36, 6, 70, '2023-06-25', 44, 13000, 1, 'teslim_edildi', '2023-06-28'),
(37, 1, 190, '2023-07-08', 45, 13000, 4, 'teslim_edildi', '2023-07-11'),
(38, 7, 125, '2023-07-22', 47, 9000, 2, 'teslim_edildi', '2023-07-25'),
(39, 2, 100, '2023-08-12', 49, 14000, 3, 'teslim_edildi', '2023-08-15'),
(40, 8, 150, '2023-08-26', 50, 13000, 1, 'teslim_edildi', '2023-08-29'),
(41, 3, 80, '2023-09-10', 51, 12000, 4, 'teslim_edildi', '2023-09-13'),
(42, 9, 110, '2023-09-24', 52, 25000, 2, 'teslim_edildi', '2023-09-27'),
(43, 4, 175, '2023-10-15', 54, 13000, 3, 'teslim_edildi', '2023-10-18'),
(44, 10, 60, '2023-10-29', 36, 8900, 1, 'teslim_edildi', '2023-11-01'),
(45, 5, 130, '2023-11-12', 37, 6000, 4, 'teslim_edildi', '2023-11-15'),
(46, 6, 85, '2023-11-26', 38, 7000, 2, 'teslim_edildi', '2023-11-29'),
(47, 1, 200, '2023-12-10', 39, 9000, 3, 'teslim_edildi', '2023-12-13'),
(48, 7, 95, '2023-12-24', 40, 12000, 1, 'teslim_edildi', '2023-12-27'),
(49, 2, 140, '2024-01-15', 60, 75000, 4, 'teslim_edildi', '2024-01-18'),
(50, 8, 100, '2024-01-28', 63, 81874, 2, 'teslim_edildi', '2024-01-31'),
(51, 3, 120, '2024-02-12', 66, 78859, 3, 'teslim_edildi', '2024-02-15'),
(52, 9, 180, '2024-02-25', 67, 45978, 1, 'teslim_edildi', '2024-02-28'),
(53, 4, 90, '2024-03-10', 1, 12000, 4, 'teslim_edildi', '2024-03-13'),
(54, 10, 150, '2024-03-25', 2, 5300, 2, 'teslim_edildi', '2024-03-28'),
(55, 5, 75, '2024-04-08', 3, 4000, 3, 'teslim_edildi', '2024-04-11'),
(56, 6, 200, '2024-04-22', 41, 12000, 1, 'teslim_edildi', '2024-04-25'),
(57, 1, 110, '2024-05-15', 44, 13000, 4, 'teslim_edildi', '2024-05-18'),
(58, 7, 85, '2024-05-28', 45, 13000, 2, 'teslim_edildi', '2024-05-31'),
(59, 2, 160, '2024-06-10', 47, 9000, 3, 'teslim_edildi', '2024-06-13'),
(60, 8, 95, '2024-06-25', 49, 14000, 1, 'teslim_edildi', '2024-06-28'),
(61, 3, 130, '2024-07-08', 50, 13000, 4, 'teslim_edildi', '2024-07-11'),
(62, 9, 70, '2024-07-22', 52, 25000, 2, 'teslim_edildi', '2024-07-25'),
(63, 4, 190, '2024-08-12', 54, 13000, 3, 'teslim_edildi', '2024-08-15'),
(64, 10, 125, '2024-08-26', 68, 8000, 1, 'teslim_edildi', '2024-08-29'),
(65, 5, 100, '2024-09-10', 36, 8900, 4, 'teslim_edildi', '2024-09-13'),
(66, 6, 150, '2024-09-24', 37, 6000, 2, 'teslim_edildi', '2024-09-27'),
(67, 1, 80, '2024-10-15', 38, 7000, 3, 'teslim_edildi', '2024-10-18'),
(68, 7, 110, '2024-10-29', 39, 9000, 1, 'teslim_edildi', '2024-11-01'),
(69, 2, 175, '2024-11-12', 40, 12000, 4, 'teslim_edildi', '2024-11-15'),
(70, 8, 60, '2024-11-26', 1, 12000, 2, 'teslim_edildi', '2024-11-29'),
(71, 3, 130, '2024-12-10', 68, 8000, 3, 'teslim_edildi', '2024-12-13'),
(72, 9, 95, '2024-12-24', 2, 5300, 1, 'teslim_edildi', '2024-12-27'),
(73, 1, 150, '2025-01-05', 69, 15000, 1, 'teslim_edildi', '2025-01-08'),
(74, 2, 200, '2025-01-10', 70, 14500, 2, 'teslim_edildi', '2025-01-13'),
(75, 3, 100, '2025-01-15', 71, 18000, 3, 'teslim_edildi', '2025-01-18'),
(76, 4, 80, '2025-01-20', 72, 25000, 4, 'teslim_edildi', '2025-01-23'),
(77, 5, 120, '2025-01-25', 73, 22000, 1, 'teslim_edildi', '2025-01-28'),
(78, 6, 90, '2025-02-01', 74, 28000, 2, 'teslim_edildi', '2025-02-04'),
(79, 7, 110, '2025-02-05', 75, 32000, 3, 'teslim_edildi', '2025-02-08'),
(80, 8, 70, '2025-02-10', 76, 30000, 4, 'teslim_edildi', '2025-02-13'),
(81, 9, 130, '2025-02-15', 77, 25000, 1, 'teslim_edildi', '2025-02-18'),
(82, 10, 160, '2025-02-20', 78, 45000, 2, 'teslim_edildi', '2025-02-23');

--
-- Tetikleyiciler `siparis`
--
DELIMITER $$
CREATE TRIGGER `after_siparis_insert` AFTER INSERT ON `siparis` FOR EACH ROW BEGIN
    UPDATE urun 
    SET urun_miktar = urun_miktar + NEW.siparis_adet
    WHERE urun_id = NEW.urun_id;
END
$$
DELIMITER ;

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
  `il_id` int(11) NOT NULL,
  `il_ad` varchar(45) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `kategori_ad` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `tedarik`
--

INSERT INTO `tedarik` (`tedarik_id`, `tedarik_ad`, `il_id`, `il_ad`, `kategori_id`, `kategori_ad`) VALUES
(1, 'Anadolu Ticaret AŞ', 6, 'Ankara', 2, 'Sıcak İçecekler'),
(2, 'İstanbul Lojistik Ltd. Şti.', 34, 'İstanbul', 3, 'Alkollü'),
(3, 'Ege İthalat İhracat Co.', 35, 'İzmir', 3, 'Alkollü'),
(4, 'Karadeniz Tedarik Hizmetleri', 61, 'Trabzon', 1, 'Soft Drinks'),
(5, 'Kapadokya Ticaret Limited', 50, 'Nevşehir', 3, 'Alkollü'),
(6, 'Akdeniz Toptan Pazarlama', 7, 'Antalya', 2, 'Sıcak İçecekler'),
(7, 'Anzer Ticaret Kooperatifi', 45, 'Manisa', 1, 'Soft Drinks'),
(8, 'AYDA MESRUBAT GIDA SAN.TIC LTD', 35, 'İzmir', 3, 'Alkollü'),
(9, 'ABC Lojistik A.Ş.', 34, 'İstanbul', 1, 'Soft Drinks'),
(10, 'XYZ Nakliye Ltd. Şti.', 6, 'Ankara', 2, 'Sıcak İçecekler'),
(11, 'Kapadokya Turizm ve Lojistik', 50, 'Nevşehir', 3, 'Alkolü'),
(12, 'Mavi Deniz Taşımacılık', 7, 'Antalya', 3, 'Alkolü'),
(13, 'Ege Bölgesi Taşıma Ltd. Şti.', 35, 'İzmir', 3, 'Alkolü'),
(14, 'Avc Holding', 6, 'Ankara', 1, 'Soft Drinks'),
(15, 'SIP', 90, 'Londra', 2, 'Sıcak İçecekler'),
(16, 'Qamiss', 90, 'Sidney', 1, 'Soft Drinks'),
(17, 'Rogers Communications', 90, 'Toronto', 2, 'Sıcak İçecekler'),
(18, 'PepsiCo Türkiye', 34, 'İstanbul', 1, 'Soft Drinks'),
(19, 'Coca-Cola İçecek A.Ş', 34, 'İstanbul', 1, 'Soft Drinks'),
(20, 'Doğuş Çay', 53, 'Rize', 2, 'Sıcak İçecekler'),
(21, 'Kera Kahveci Mehmet Efendi', 34, 'İstanbul', 2, 'Sıcak İçecekler'),
(22, 'Elite Pilsen', 35, 'İzmir', 3, 'Alkolü'),
(23, 'May Içi (Raki)', 59, 'Tekirdağ', 3, 'Alkolü'),
(24, 'Lipton Çay Fabrikası', 10, 'Balıkesir', 2, 'Sıcak İçecekler'),
(25, 'Red Bull Türkiye', 6, 'Ankara', 1, 'Soft Drinks'),
(26, 'Nestle Kahve', 35, 'İzmir', 2, 'Sıcak İçecekler'),
(27, 'Tuborg Türkiye', 35, 'İzmir', 3, 'Alkolü'),
(28, 'Uludağ İçecek A.Ş.', 16, 'Bursa', 1, 'Soft Drinks'),
(29, 'Dimes Gıda Sanayi', 60, 'Tokat', 1, 'Soft Drinks'),
(30, 'Pınar Su ve İçecek', 35, 'İzmir', 1, 'Soft Drinks'),
(31, 'Çaykur (Çay Sanayi)', 53, 'Rize', 2, 'Sıcak İçecekler'),
(32, 'Tekirci Türkiye', 34, 'İstanbul', 2, 'Sıcak İçecekler'),
(33, 'Starbucks Türkiye', 34, 'İstanbul', 2, 'Sıcak İçecekler'),
(34, 'Besa Coffee Works', 6, 'Ankara', 2, 'Sıcak İçecekler'),
(35, 'May Değon (Viski & Raki)', 35, 'İzmir', 3, 'Alkolü'),
(36, 'Suma Şampajola', 45, 'Manisa', 3, 'Alkolü'),
(37, 'Pamukale Şampajı', 20, 'Denizli', 3, 'Alkolü'),
(38, 'Kavaklidere Şampajı', 6, 'Ankara', 3, 'Alkolü'),
(39, 'Çaykur Çay İşletmesi', 53, 'Rize', 2, 'Sıcak İçecekler'),
(40, 'Doğadan Bitki Çayları', 34, 'İstanbul', 2, 'Sıcak İçecekler'),
(41, 'Beta Tea', 35, 'İzmir', 2, 'Sıcak İçecekler'),
(42, 'Selamlique Ottoman Coffee', 15, 'İstanbul', 2, 'Sıcak İçecekler'),
(43, 'Flamingo Kahve', 34, 'İstanbul', 2, 'Sıcak İçecekler'),
(44, 'Gloria Jeans Coffee', 6, 'Ankara', 2, 'Sıcak İçecekler'),
(45, 'Eftal Su ve İçecek', 34, 'İstanbul', 1, 'Soft Drinks'),
(46, 'Beyazsuın Maden Suyu', 6, 'Ankara', 1, 'Soft Drinks'),
(47, 'Demes Su', 7, 'Antalya', 1, 'Soft Drinks'),
(48, 'Doluca Şampajları', 34, 'İstanbul', 3, 'Alkolü'),
(49, 'Avşare Şampanjı', 20, 'Denizli', 3, 'Alkolü'),
(50, 'Bomonti Bira', 34, 'İstanbul', 3, 'Alkolü');

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
  `stok` int(11) GENERATED ALWAYS AS (`max_urun_miktar` - `urun_miktar`) STORED,
  `alt_kategori_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `urun`
--

INSERT INTO `urun` (`urun_id`, `urun_ad`, `urun_fiyat`, `kategori_id`, `kategori_ad`, `firma_id`, `urun_miktar`, `max_urun_miktar`, `urun_tarih`, `alt_kategori_id`) VALUES
(1, 'Pepsi', '12000', 1, 'Soft Drinks', 16, 6536, 5000, '2022-12-01', 1),
(2, 'Cola', '5300', 1, 'Soft Drinks', 17, 6533, 5000, '2023-12-01', 1),
(3, 'Sprite', '4000', 1, 'Soft Drinks', 17, 4854, 5000, '2022-12-01', 1),
(4, 'Schweppes', '5200', 1, 'Soft Drinks', 3, 5505, 5000, '2022-12-01', 1),
(5, 'Ice Tea', '4100', 1, 'Soft Drinks', 7, 4322, 5000, '2022-12-01', 2),
(6, 'Fuse Tea', '3500', 1, 'Soft Drinks', 18, 4323, 5000, '2022-12-01', 10),
(7, 'Fanta', '2500', 1, 'Soft Drinks', 17, 3421, 5000, '2022-12-01', 1),
(8, 'Mountain Dew', '6500', 1, 'Soft Drinks', 6, 4522, 5000, '2022-12-01', 3),
(9, 'Dr. Pepper', '5500', 1, 'Soft Drinks', 8, 321, 5000, '2022-12-01', 1),
(10, '7UP', '2700', 1, 'Soft Drinks', 9, 3900, 5000, '2022-12-01', 1),
(11, 'Club Soda', '1500', 1, 'Soft Drinks', 10, 2950, 5000, '2022-12-01', 1),
(12, 'Root Beer', '6500', 1, 'Soft Drinks', 11, 4090, 5000, '2022-12-01', 1),
(13, 'Ginger Ale', '1000', 1, 'Soft Drinks', 12, 4623, 5000, '2023-12-15', 1),
(14, 'Tonic Water', '6000', 1, 'Soft Drinks', 3, 1500, 5000, '2022-12-01', 1),
(15, 'Cream Soda', '5500', 1, 'Soft Drinks', 13, 1000, 5000, '2022-12-01', 1),
(16, 'Orange Crush', '4500', 1, 'Soft Drinks', 14, 700, 5000, '2022-12-01', 1),
(17, 'Grape Soda', '3000', 1, 'Soft Drinks', 15, 500, 5000, '2022-12-01', 1),
(18, 'Lemon-Lime Soda', '4000', 1, 'Soft Drinks', 16, 900, 5000, '2022-12-01', 1),
(19, 'Cherry Cola', '3000', 1, 'Soft Drinks', 17, 550, 5000, '2022-12-01', 7),
(20, 'Birch Beer', '4000', 1, 'Soft Drinks', 18, 600, 5000, '2022-12-01', 9),
(21, 'Iced Coffee', '5000', 1, 'Soft Drinks', 19, 1500, 5000, '2022-12-01', 2),
(22, 'Fruit Punch', '4000', 1, 'Soft Drinks', 20, 1000, 5000, '2022-12-01', 2),
(23, 'Cranberry Juice', '6000', 1, 'Soft Drinks', 21, 1000, 5000, '2022-12-01', 2),
(24, 'Pineapple Juice', '1500', 1, 'Soft Drinks', 22, 600, 5000, '2022-12-01', 2),
(25, 'Coconut Water', '5000', 1, 'Soft Drinks', 23, 800, 5000, '2022-12-01', 2),
(26, 'Aloe Vera Juice', '5000', 1, 'Soft Drinks', 24, 1000, 5000, '2023-12-01', 2),
(27, 'Peach Iced Tea', '4500', 1, 'Soft Drinks', 18, 1000, 5000, '2023-12-01', 2),
(28, 'Raspberry Iced Tea', '7000', 1, 'Soft Drinks', 18, 700, 5000, '2023-12-01', 2),
(29, 'Strawberry Lemonade', '4500', 1, 'Soft Drinks', 27, 800, 5000, '2023-12-01', 7),
(30, 'Blueberry Lemonade', '6000', 1, 'Soft Drinks', 28, 600, 5000, '2023-12-01', 2),
(31, 'Watermelon Juice', '7000', 1, 'Soft Drinks', 29, 590, 5000, '2023-12-01', 2),
(32, 'Passion Fruit Juice', '5000', 1, 'Soft Drinks', 30, 600, 5000, '2023-12-01', 2),
(33, 'Kiwi-Strawberry Soda', '5500', 1, 'Soft Drinks', 31, 1100, 5000, '2023-12-01', 1),
(34, 'Raspberry Ginger Ale', '5500', 1, 'Soft Drinks', 12, 1100, 5000, '2023-12-01', 1),
(35, 'Black Cherry Soda', '5500', 1, 'Soft Drinks', 32, 550, 5000, '2023-12-01', 1),
(36, 'Vanilla Cream Soda', '8900', 1, 'Soft Drinks', 13, 2000, 5000, '2023-12-01', 1),
(37, 'Pomegranate Juice', '6000', 1, 'Soft Drinks', 33, 690, 5000, '2023-12-01', 2),
(38, 'Rosemary Grapefruit Sparkler', '7000', 1, 'Soft Drinks', 34, 1000, 5000, '2023-12-01', 3),
(39, 'Blue Raspberry Lemonade', '9000', 1, 'Soft Drinks', 35, 2500, 5000, '2023-12-01', 7),
(40, 'Hibiscus Iced Tea', '12000', 1, 'Soft Drinks', 18, 3500, 5000, '2023-12-01', 3),
(41, 'Türk Kahvesi', '12000', 2, 'Sıcak İçecekler', 37, 4000, 5000, '2023-12-01', 4),
(42, 'Türk Kahvesi', '11000', 2, 'Sıcak İçecekler', 37, 3500, 5000, '2022-12-01', 4),
(43, 'Mısır İnciri Çayı', '9000', 2, 'Sıcak İçecekler', 38, 1000, 5000, '2023-12-01', 6),
(44, 'Espresso', '13000', 2, 'Sıcak İçecekler', 39, 3000, 5000, '2023-12-01', 4),
(45, 'Americano', '13000', 2, 'Sıcak İçecekler', 39, 8000, 5000, '2023-12-01', 4),
(46, 'Latte', '4500', 2, 'Sıcak İçecekler', 39, 5500, 5000, '2022-12-01', 4),
(47, 'Cappuccino', '9000', 2, 'Sıcak İçecekler', 39, 4000, 5000, '2023-12-01', 4),
(48, 'Cappuccino', '7000', 2, 'Sıcak İçecekler', 39, 3500, 5000, '2022-12-01', 4),
(49, 'Macchiato', '14000', 2, 'Sıcak İçecekler', 39, 4000, 5000, '2023-12-01', 4),
(50, 'Mocha', '13000', 2, 'Sıcak İçecekler', 39, 5000, 5000, '2023-12-01', 4),
(51, 'Flat White', '12000', 2, 'Sıcak İçecekler', 39, 4000, 5000, '2023-12-01', 4),
(52, 'Filtre Kahve', '25000', 2, 'Sıcak İçecekler', 39, 9000, 5000, '2023-12-01', 4),
(53, 'Filtre Kahve', '23000', 2, 'Sıcak İçecekler', 39, 7000, 5000, '2022-12-01', 4),
(54, 'White Chocolate Mocha', '13000', 2, 'Sıcak İçecekler', 39, 6000, 5000, '2023-12-01', 4),
(55, 'Yerba Mate Çayı', '4300', 2, 'Sıcak İçecekler', 40, 700, 5000, '2023-12-01', 6),
(56, 'Zencefilli Sıcak Elma İçkisi', '9900', 2, 'Sıcak İçecekler', 41, 8090, 5000, '2023-12-01', 5),
(57, 'Nane Çikolatalı Sıcak İçecek', '8990', 2, 'Sıcak İçecekler', 42, 5450, 5000, '2023-12-01', 5),
(58, 'VODKA ABSOLUT PEAR 70 CL', '65.685,98', 3, 'Alkollü', 43, 3750, 5000, '2022-01-01', 10),
(59, 'VODKA ABSOLUT PEAR 70 CL', '86.945,95', 3, 'Alkollü', 43, 4500, 5000, '2023-01-01', 10),
(60, 'VODKA ABSOLUT PEAR 70 CL', '86250', 3, 'Alkollü', 43, 4500, 5000, '2024-01-01', 10),
(61, 'WHISKEY JAMESON 70 CL', '51.954,75', 3, 'Alkollü', 43, 4500, 5000, '2022-01-01', 10),
(62, 'WHISKEY JAMESON 70 CL', '67.900', 3, 'Alkollü', 43, 4500, 5000, '2023-01-01', 10),
(63, 'WHISKEY JAMESON 70 CL', '94155', 3, 'Alkollü', 43, 8750, 5000, '2024-01-01', 10),
(64, 'TEQUILA OLMECA 70 CL', '38.789', 3, 'Alkollü', 43, 4500, 5000, '2022-01-01', 10),
(65, 'TEQUILA OLMECA 70 CL', '53.689', 3, 'Alkollü', 43, 7900, 5000, '2023-01-01', 10),
(66, 'TEQUILA OLMECA 70 CL', '90688', 3, 'Alkollü', 43, 8980, 5000, '2024-01-01', 10),
(67, 'VODKA ABSOLUT RASPBERRY 70 CL', '52875', 3, 'Alkollü', 43, 2500, 5000, '2024-01-01', 10),
(68, 'Red Bull', '8000', 1, 'Soft Drinks', 23, 1500, 5000, '2025-05-28', 3),
(69, 'Pepsi Zero Sugar', '15000', 1, 'Soft Drinks', 16, 2000, 5000, '2025-01-01', 1),
(70, 'Coca Cola Zero', '14500', 1, 'Soft Drinks', 17, 2500, 5000, '2025-01-01', 1),
(71, 'Monster Energy', '18000', 1, 'Soft Drinks', 23, 1000, 5000, '2025-01-01', 3),
(72, 'Prime Energy', '25000', 1, 'Soft Drinks', 23, 800, 5000, '2025-01-01', 3),
(73, 'Kombucha Original', '22000', 1, 'Soft Drinks', 28, 600, 5000, '2025-01-01', 2),
(74, 'Cold Brew Coffee', '28000', 2, 'Sıcak İçecekler', 31, 1500, 5000, '2025-01-01', 4),
(75, 'Matcha Latte', '32000', 2, 'Sıcak İçecekler', 31, 800, 5000, '2025-01-01', 4),
(76, 'Turmeric Latte', '30000', 2, 'Sıcak İçecekler', 31, 600, 5000, '2025-01-01', 5),
(77, 'Bubble Tea', '25000', 2, 'Sıcak İçecekler', 29, 1000, 5000, '2025-01-01', 5),
(78, 'Craft Beer IPA', '45000', 3, 'Alkollü', 20, 500, 2000, '2025-01-01', 9),
(79, 'Premium Gin 70cl', '120000', 3, 'Alkollü', 33, 200, 1000, '2025-01-01', 10),
(80, 'Artisan Rakı 70cl', '95000', 3, 'Alkollü', 21, 300, 1000, '2025-01-01', 10),
(81, 'Frozen Mojito Mix', '35000', 3, 'Alkollü', 33, 400, 1500, '2025-06-01', 7),
(82, 'Summer Sangria', '28000', 3, 'Alkollü', 34, 600, 2000, '2025-06-01', 8),
(83, 'Iced Hibiscus Tea', '18000', 1, 'Soft Drinks', 18, 1000, 3000, '2025-06-01', 6),
(84, 'Watermelon Agua Fresca', '15000', 1, 'Soft Drinks', 28, 800, 2500, '2025-06-01', 2),
(85, 'Mulled Wine', '42000', 3, 'Alkollü', 34, 300, 1000, '2025-12-01', 8),
(86, 'Hot Buttered Rum', '38000', 3, 'Alkollü', 33, 200, 800, '2025-12-01', 10),
(87, 'Spiced Hot Chocolate', '22000', 2, 'Sıcak İçecekler', 31, 1200, 3000, '2025-12-01', 5),
(88, 'Cinnamon Apple Cider', '20000', 2, 'Sıcak İçecekler', 30, 1000, 2500, '2025-12-01', 5),
(89, 'Red Bull Premium', '10000', 1, 'Soft Drinks', 23, 1200, 5000, '2026-01-01', 3),
(90, 'Monster Energy Premium', '22500', 1, 'Soft Drinks', 23, 800, 5000, '2026-01-01', 3),
(91, 'Prime Energy Premium', '31250', 1, 'Soft Drinks', 23, 640, 5000, '2026-01-01', 3),
(92, 'Kombucha Original Premium', '27500', 1, 'Soft Drinks', 28, 480, 5000, '2026-01-01', 2),
(93, 'Cold Brew Coffee Premium', '35000', 2, 'Sıcak İçecekler', 31, 1200, 5000, '2026-01-01', 4),
(94, 'Matcha Latte Premium', '40000', 2, 'Sıcak İçecekler', 31, 640, 5000, '2026-01-01', 4),
(95, 'Turmeric Latte Premium', '37500', 2, 'Sıcak İçecekler', 31, 480, 5000, '2026-01-01', 5),
(96, 'Bubble Tea Premium', '31250', 2, 'Sıcak İçecekler', 29, 800, 5000, '2026-01-01', 5),
(97, 'Craft Beer IPA Premium', '56250', 3, 'Alkollü', 20, 400, 2000, '2026-01-01', 9),
(98, 'Premium Gin 70cl Premium', '150000', 3, 'Alkollü', 33, 160, 1000, '2026-01-01', 10),
(99, 'Artisan Rakı 70cl Premium', '118750', 3, 'Alkollü', 21, 240, 1000, '2026-01-01', 10),
(100, 'Frozen Mojito Mix Premium', '43750', 3, 'Alkollü', 33, 320, 1500, '2026-01-01', 7),
(101, 'Summer Sangria Premium', '35000', 3, 'Alkollü', 34, 480, 2000, '2026-01-01', 8),
(102, 'Iced Hibiscus Tea Premium', '22500', 1, 'Soft Drinks', 18, 800, 3000, '2026-01-01', 6),
(103, 'Watermelon Agua Fresca Premium', '18750', 1, 'Soft Drinks', 28, 640, 2500, '2026-01-01', 2),
(104, 'Mulled Wine Premium', '52500', 3, 'Alkollü', 34, 240, 1000, '2026-01-01', 8),
(105, 'Hot Buttered Rum Premium', '47500', 3, 'Alkollü', 33, 160, 800, '2026-01-01', 10),
(120, 'Çaykur Rize Turist Çayı', '25000', 2, 'Sıcak İçecekler', 37, 2000, 4000, '2025-01-01', 5),
(121, 'Çaykur Altınbaş Çay', '30000', 2, 'Sıcak İçecekler', 37, 1800, 4000, '2025-01-01', 5),
(122, 'Çaykur Organik Yeşil Çay', '35000', 2, 'Sıcak İçecekler', 37, 1500, 3000, '2025-01-01', 5),
(123, 'Çaykur Earl Grey', '28000', 2, 'Sıcak İçecekler', 37, 1200, 3000, '2025-01-01', 5),
(124, 'Doğadan Adaçayı', '22000', 2, 'Sıcak İçecekler', 38, 800, 2000, '2025-01-01', 6),
(125, 'Doğadan Papatya Çayı', '20000', 2, 'Sıcak İçecekler', 38, 900, 2000, '2025-01-01', 6),
(126, 'Doğadan Melisa Çayı', '24000', 2, 'Sıcak İçecekler', 38, 700, 2000, '2025-01-01', 6),
(127, 'Doğadan Ihlamur', '23000', 2, 'Sıcak İçecekler', 38, 650, 2000, '2025-01-01', 6),
(128, 'Starbucks Pike Place Roast', '45000', 2, 'Sıcak İçecekler', 31, 1000, 2500, '2025-01-01', 4),
(129, 'Starbucks Caramel Macchiato', '38000', 2, 'Sıcak İçecekler', 31, 1200, 2500, '2025-01-01', 4),
(130, 'Starbucks Frappuccino', '42000', 2, 'Sıcak İçecekler', 31, 1100, 2500, '2025-01-01', 4),
(131, 'Starbucks Chai Tea Latte', '36000', 2, 'Sıcak İçecekler', 31, 900, 2500, '2025-01-01', 4),
(132, 'Mehmet Efendi Türk Kahvesi Premium', '55000', 2, 'Sıcak İçecekler', 19, 800, 2000, '2025-01-01', 4),
(133, 'Mehmet Efendi Dibek Kahvesi', '48000', 2, 'Sıcak İçecekler', 19, 600, 1500, '2025-01-01', 4),
(134, 'Mehmet Efendi Osmanlı Kahvesi', '52000', 2, 'Sıcak İçecekler', 19, 700, 1500, '2025-01-01', 4),
(135, 'Pepsi Max', '16000', 1, 'Soft Drinks', 16, 2500, 5000, '2025-01-01', 1),
(136, '7UP Free', '14000', 1, 'Soft Drinks', 16, 2000, 4000, '2025-01-01', 1),
(137, 'Mirinda Portakal', '15000', 1, 'Soft Drinks', 16, 2200, 4500, '2025-01-01', 1),
(138, 'Coca Cola Light', '15500', 1, 'Soft Drinks', 17, 2800, 5000, '2025-01-01', 1),
(139, 'Fanta Zero', '14500', 1, 'Soft Drinks', 17, 2000, 4000, '2025-01-01', 1),
(140, 'Sprite Zero', '14000', 1, 'Soft Drinks', 17, 2100, 4000, '2025-01-01', 1),
(141, 'Efes Pilsen Draft', '18000', 3, 'Alkollü', 20, 1500, 3000, '2025-01-01', 9),
(142, 'Efes Dark', '20000', 3, 'Alkollü', 20, 1200, 2500, '2025-01-01', 9),
(143, 'Efes Xtra', '16000', 3, 'Alkollü', 20, 1800, 3500, '2025-01-01', 9),
(144, 'Yeni Rakı Premium', '95000', 3, 'Alkollü', 21, 300, 800, '2025-01-01', 10),
(145, 'Tekirdağ Rakısı', '85000', 3, 'Alkollü', 21, 400, 1000, '2025-01-01', 10),
(146, 'Altınbaş Rakı', '120000', 3, 'Alkollü', 21, 200, 600, '2025-01-01', 10),
(147, 'Doluca Karma Kırmızı', '65000', 3, 'Alkollü', 46, 250, 800, '2025-01-01', 8),
(148, 'Doluca Sauvignon Blanc', '70000', 3, 'Alkollü', 46, 200, 700, '2025-01-01', 8),
(149, 'Doluca Özel Kuvaj', '85000', 3, 'Alkollü', 46, 180, 600, '2025-01-01', 8);

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

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_category_performance`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_category_performance` (
`kategori_id` int(11)
,`kategori_ad` varchar(45)
,`toplam_siparis` bigint(21)
,`toplam_adet` decimal(32,0)
,`toplam_tutar` decimal(32,2)
,`ortalama_siparis` decimal(14,6)
,`benzersiz_musteri` bigint(21)
,`urun_sayisi` bigint(21)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_monthly_summary`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_monthly_summary` (
`ay` varchar(7)
,`toplam_siparis` bigint(21)
,`toplam_adet` decimal(32,0)
,`toplam_tutar` decimal(32,2)
,`ortalama_siparis_tutari` decimal(14,6)
,`benzersiz_musteri` bigint(21)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_product_performance`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_product_performance` (
`urun_id` int(11)
,`urun_ad` varchar(45)
,`kategori_ad` varchar(45)
,`siparis_sayisi` bigint(21)
,`toplam_satis` decimal(32,0)
,`toplam_gelir` decimal(32,2)
,`ortalama_siparis_tutari` decimal(14,6)
,`mevcut_stok` int(11)
,`max_urun_miktar` int(25)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_regional_performance`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_regional_performance` (
`il_id` int(11)
,`il_ad` varchar(50)
,`bolge_ad` varchar(50)
,`toplam_siparis` bigint(21)
,`toplam_adet` decimal(32,0)
,`toplam_tutar` decimal(32,2)
,`musteri_sayisi` bigint(21)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_siparis_detay`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_siparis_detay` (
`siparis_id` int(11)
,`siparis_tarihi` date
,`siparis_adet` int(11)
,`toplam_tutar` decimal(10,2)
,`siparis_durumu` enum('beklemede','onaylandi','kargoda','teslim_edildi','iptal')
,`teslim_tarihi` date
,`urun_ad` varchar(45)
,`urun_fiyat` varchar(45)
,`kategori_ad` varchar(45)
,`musteri_ad` varchar(13)
,`musteri_il` varchar(50)
,`tedarik_ad` varchar(45)
,`tedarik_il` varchar(50)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_category_performance`
--
DROP TABLE IF EXISTS `v_category_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_category_performance`  AS SELECT `k`.`kategori_id` AS `kategori_id`, `k`.`kategori_ad` AS `kategori_ad`, count(distinct `s`.`siparis_id`) AS `toplam_siparis`, sum(`s`.`siparis_adet`) AS `toplam_adet`, sum(`s`.`toplam_tutar`) AS `toplam_tutar`, avg(`s`.`toplam_tutar`) AS `ortalama_siparis`, count(distinct `s`.`musteri_id`) AS `benzersiz_musteri`, count(distinct `u`.`urun_id`) AS `urun_sayisi` FROM ((`kategori` `k` left join `urun` `u` on(`k`.`kategori_id` = `u`.`kategori_id`)) left join `siparis` `s` on(`u`.`urun_id` = `s`.`urun_id` and `s`.`siparis_durumu` = 'teslim_edildi')) GROUP BY `k`.`kategori_id`, `k`.`kategori_ad` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_monthly_summary`
--
DROP TABLE IF EXISTS `v_monthly_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_monthly_summary`  AS SELECT date_format(`siparis`.`siparis_tarihi`,'%Y-%m') AS `ay`, count(distinct `siparis`.`siparis_id`) AS `toplam_siparis`, sum(`siparis`.`siparis_adet`) AS `toplam_adet`, sum(`siparis`.`toplam_tutar`) AS `toplam_tutar`, avg(`siparis`.`toplam_tutar`) AS `ortalama_siparis_tutari`, count(distinct `siparis`.`musteri_id`) AS `benzersiz_musteri` FROM `siparis` WHERE `siparis`.`siparis_durumu` = 'teslim_edildi' GROUP BY date_format(`siparis`.`siparis_tarihi`,'%Y-%m') ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_product_performance`
--
DROP TABLE IF EXISTS `v_product_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_product_performance`  AS SELECT `u`.`urun_id` AS `urun_id`, `u`.`urun_ad` AS `urun_ad`, `k`.`kategori_ad` AS `kategori_ad`, count(`s`.`siparis_id`) AS `siparis_sayisi`, sum(`s`.`siparis_adet`) AS `toplam_satis`, sum(`s`.`toplam_tutar`) AS `toplam_gelir`, avg(`s`.`toplam_tutar`) AS `ortalama_siparis_tutari`, `u`.`stok` AS `mevcut_stok`, `u`.`max_urun_miktar` AS `max_urun_miktar` FROM ((`urun` `u` join `kategori` `k` on(`u`.`kategori_id` = `k`.`kategori_id`)) left join `siparis` `s` on(`u`.`urun_id` = `s`.`urun_id` and `s`.`siparis_durumu` = 'teslim_edildi')) GROUP BY `u`.`urun_id` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_regional_performance`
--
DROP TABLE IF EXISTS `v_regional_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_regional_performance`  AS SELECT `i`.`il_id` AS `il_id`, `i`.`il_ad` AS `il_ad`, `b`.`bolge_ad` AS `bolge_ad`, count(distinct `s`.`siparis_id`) AS `toplam_siparis`, sum(`s`.`siparis_adet`) AS `toplam_adet`, sum(`s`.`toplam_tutar`) AS `toplam_tutar`, count(distinct `m`.`musteri_id`) AS `musteri_sayisi` FROM (((`iller` `i` join `bolge` `b` on(`i`.`bolge_id` = `b`.`bolge_id`)) left join `musteri` `m` on(`i`.`il_id` = `m`.`il_id`)) left join `siparis` `s` on(`m`.`musteri_id` = `s`.`musteri_id` and `s`.`siparis_durumu` = 'teslim_edildi')) GROUP BY `i`.`il_id`, `i`.`il_ad`, `b`.`bolge_ad` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_siparis_detay`
--
DROP TABLE IF EXISTS `v_siparis_detay`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_siparis_detay`  AS SELECT `s`.`siparis_id` AS `siparis_id`, `s`.`siparis_tarihi` AS `siparis_tarihi`, `s`.`siparis_adet` AS `siparis_adet`, `s`.`toplam_tutar` AS `toplam_tutar`, `s`.`siparis_durumu` AS `siparis_durumu`, `s`.`teslim_tarihi` AS `teslim_tarihi`, `u`.`urun_ad` AS `urun_ad`, `u`.`urun_fiyat` AS `urun_fiyat`, `k`.`kategori_ad` AS `kategori_ad`, `m`.`musteri_ad` AS `musteri_ad`, `i`.`il_ad` AS `musteri_il`, `t`.`tedarik_ad` AS `tedarik_ad`, `ti`.`il_ad` AS `tedarik_il` FROM ((((((`siparis` `s` join `urun` `u` on(`s`.`urun_id` = `u`.`urun_id`)) join `kategori` `k` on(`u`.`kategori_id` = `k`.`kategori_id`)) join `musteri` `m` on(`s`.`musteri_id` = `m`.`musteri_id`)) left join `iller` `i` on(`m`.`il_id` = `i`.`il_id`)) join `tedarik` `t` on(`s`.`tedarik_id` = `t`.`tedarik_id`)) join `iller` `ti` on(`t`.`il_id` = `ti`.`il_id`)) ;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `alt_kategori`
--
ALTER TABLE `alt_kategori`
  ADD PRIMARY KEY (`alt_kategori_id`),
  ADD KEY `kategori_id` (`kategori_id`);

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
-- Tablo için indeksler `musteri`
--
ALTER TABLE `musteri`
  ADD PRIMARY KEY (`musteri_id`);

--
-- Tablo için indeksler `otel`
--
ALTER TABLE `otel`
  ADD PRIMARY KEY (`otel_id`);

--
-- Tablo için indeksler `siparis`
--
ALTER TABLE `siparis`
  ADD PRIMARY KEY (`siparis_id`),
  ADD KEY `urun_id` (`urun_id`,`tedarik_id`),
  ADD KEY `urun_id_2` (`urun_id`,`tedarik_id`),
  ADD KEY `urun_id_3` (`urun_id`,`tedarik_id`),
  ADD KEY `tedarik_id` (`tedarik_id`),
  ADD KEY `fk_siparis_musteri` (`musteri_id`);

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
  ADD KEY `firma_id` (`firma_id`),
  ADD KEY `fk_alt_kategori` (`alt_kategori_id`);

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
-- Tablo için AUTO_INCREMENT değeri `alt_kategori`
--
ALTER TABLE `alt_kategori`
  MODIFY `alt_kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `iller`
--
ALTER TABLE `iller`
  MODIFY `il_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Tablo için AUTO_INCREMENT değeri `location`
--
ALTER TABLE `location`
  MODIFY `firma_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Tablo için AUTO_INCREMENT değeri `musteri`
--
ALTER TABLE `musteri`
  MODIFY `musteri_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Tablo için AUTO_INCREMENT değeri `siparis`
--
ALTER TABLE `siparis`
  MODIFY `siparis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Tablo için AUTO_INCREMENT değeri `tedarik`
--
ALTER TABLE `tedarik`
  MODIFY `tedarik_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Tablo için AUTO_INCREMENT değeri `urun`
--
ALTER TABLE `urun`
  MODIFY `urun_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `alt_kategori`
--
ALTER TABLE `alt_kategori`
  ADD CONSTRAINT `alt_kategori_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`kategori_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `iller`
--
ALTER TABLE `iller`
  ADD CONSTRAINT `iller_ibfk_1` FOREIGN KEY (`bolge_id`) REFERENCES `bolge` (`bolge_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `fk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`kategori_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `siparis`
--
ALTER TABLE `siparis`
  ADD CONSTRAINT `tedarik_id` FOREIGN KEY (`tedarik_id`) REFERENCES `location` (`firma_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `stok`
--
ALTER TABLE `stok`
  ADD CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`firma_id`) REFERENCES `location` (`firma_id`);

DELIMITER $$
--
-- Olaylar
--
CREATE DEFINER=`root`@`localhost` EVENT `daily_dashboard_update` ON SCHEDULE EVERY 1 DAY STARTS '2025-06-05 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    CALL sp_update_dashboard_summary();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
