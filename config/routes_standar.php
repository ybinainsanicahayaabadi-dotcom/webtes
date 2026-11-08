<?php
return [
 ['GET','/standar-mutu','StandarController@index','can:standar_mutu.lihat'],
 ['POST','/standar-mutu/standar','StandarController@standar','role:admin'],
 ['POST','/standar-mutu/butir','StandarController@butir','role:admin'],
 ['POST','/standar-mutu/isian','StandarController@isian','can:standar_mutu.isi'],
 ['POST','/standar-mutu/verifikasi','StandarController@verifikasi','can:standar_mutu.verifikasi'],
 ['POST','/standar-mutu/bukti','StandarController@bukti','can:standar_mutu.isi'],
 ['POST','/standar-mutu/bukti-hapus','StandarController@hapusBukti','can:standar_mutu.isi'],
 ['GET','/standar-mutu/bukti/{id}','StandarController@unduh','can:standar_mutu.lihat'],
];
