-- ==================================================
-- 6. FİRMA BAZLI MEVSIMSEL PLANLAMA
-- ==================================================

-- Yaz mevsimi için firma bazlı ürün planlaması
CREATE TEMPORARY TABLE yaz_mevsim_plan AS
SELECT 
    l.firma_ad,
    u.kategori_ad,
    COUNT(*) as mevcut_urun_sayisi,
    CASE 
        WHEN u.kategori_id = 1 THEN ROUND(COUNT(*) * 1.4) -- Soft drinks %40 artış
        WHEN u.kategori_id = 2 THEN ROUND(COUNT(*) * 0.8) -- Sıcak içecek %20 azalış  
        WHEN u.kategori_id = 3 THEN ROUND(COUNT(*) * 1.2) -- Alkollü %20 artış
    END as yaz_hedef_urun_sayisi
FROM urun u
JOIN location l ON u.firma_id = l.firma_id
WHERE YEAR(u.urun_tarih) = 2025
GROUP BY l.firma_id, l.firma_ad, u.kategori_id, u.kategori_ad;

-- Kış mevsimi için firma bazlı ürün planlaması  
CREATE TEMPORARY TABLE kis_mevsim_plan AS
SELECT 
    l.firma_ad,
    u.kategori_ad,
    COUNT(*) as mevcut_urun_sayisi,
    CASE 
        WHEN u.kategori_id = 1 THEN ROUND(COUNT(*) * 0.7) -- Soft drinks %30 azalış
        WHEN u.kategori_id = 2 THEN ROUND(COUNT(*) * 1.5) -- Sıcak içecek %50 artış
        WHEN u.kategori_id = 3 THEN ROUND(COUNT(*) * 1.1) -- Alkollü %10 artış
    END as kis_hedef_urun_sayisi
FROM urun u
JOIN location l ON u.firma_id = l.firma_id  
WHERE YEAR(u.urun_tarih) = 2025
GROUP BY l.firma_id, l.firma_ad, u.kategori_id, u.kategori_ad;
