-- SISLIA - migrasi: format tahun ajaran 2026/2027 -> 2026-2027
-- Jalankan sekali di database yang sudah terpasang (phpMyAdmin > SQL).
UPDATE tahun_ajaran
   SET nama = REPLACE(nama, '/', '-')
 WHERE nama REGEXP '^[0-9]{4}/[0-9]{4}$';

-- Periksa hasilnya:
-- SELECT id, nama FROM tahun_ajaran;
