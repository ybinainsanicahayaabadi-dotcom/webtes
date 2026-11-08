<?php
/**
 * SISLIA - rute modul Admin (digabung dengan config/routes.php oleh public/index.php).
 * Setiap tahap menambah rute di file ini.
 */
return [
    // ---- Periode: tahun ajaran
    ['GET',  '/admin/periode',                       'PeriodeAdminController@index',          'can:periode.lihat'],
    ['GET',  '/admin/periode/tahun/data',            'PeriodeAdminController@tahunData',      'can:periode.lihat'],
    ['GET',  '/admin/periode/tahun/{id}',            'PeriodeAdminController@tahunShow',      'can:periode.lihat'],
    ['POST', '/admin/periode/tahun',                 'PeriodeAdminController@tahunStore',     'can:periode.tambah'],
    ['POST', '/admin/periode/tahun/{id}/ubah',       'PeriodeAdminController@tahunUpdate',    'can:periode.ubah'],
    ['POST', '/admin/periode/tahun/{id}/hapus',      'PeriodeAdminController@tahunDelete',    'can:periode.hapus'],

    // ---- Periode: semester
    ['GET',  '/admin/periode/semester/data',         'PeriodeAdminController@semesterData',   'can:periode.lihat'],
    ['GET',  '/admin/periode/semester/{id}',         'PeriodeAdminController@semesterShow',   'can:periode.lihat'],
    ['POST', '/admin/periode/semester',              'PeriodeAdminController@semesterStore',  'can:periode.tambah'],
    ['POST', '/admin/periode/semester/{id}/ubah',    'PeriodeAdminController@semesterUpdate', 'can:periode.ubah'],
    ['POST', '/admin/periode/semester/{id}/kunci',   'PeriodeAdminController@semesterKunci',  'can:periode.ubah'],
    ['POST', '/admin/periode/semester/{id}/hapus',   'PeriodeAdminController@semesterDelete', 'can:periode.hapus'],

    // ---- Unit sekolah
    ['GET',  '/admin/unit',                          'UnitAdminController@index',             'can:unit.lihat'],
    ['GET',  '/admin/unit/data',                     'UnitAdminController@data',              'can:unit.lihat'],
    ['GET',  '/admin/unit/{id}',                     'UnitAdminController@show',              'can:unit.lihat'],
    ['POST', '/admin/unit',                          'UnitAdminController@store',             'can:unit.tambah'],
    ['POST', '/admin/unit/{id}/ubah',                'UnitAdminController@update',            'can:unit.ubah'],
    ['POST', '/admin/unit/{id}/hapus',               'UnitAdminController@delete',            'can:unit.hapus'],

    // ---- Kelas
    ['GET',  '/admin/kelas',                         'KelasAdminController@index',            'can:kelas.lihat'],
    ['GET',  '/admin/kelas/data',                    'KelasAdminController@data',             'can:kelas.lihat'],
    ['GET',  '/admin/kelas/guru-opsi',               'KelasAdminController@guruOpsi',         'can:kelas.lihat'],
    ['POST', '/admin/kelas/salin',                   'KelasAdminController@salin',            'can:kelas.tambah'],
    ['GET',  '/admin/kelas/{id}',                    'KelasAdminController@show',             'can:kelas.lihat'],
    ['POST', '/admin/kelas',                         'KelasAdminController@store',            'can:kelas.tambah'],
    ['POST', '/admin/kelas/{id}/ubah',               'KelasAdminController@update',           'can:kelas.ubah'],
    ['POST', '/admin/kelas/{id}/hapus',              'KelasAdminController@delete',           'can:kelas.hapus'],

    // ---- Guru (data diri)
    ['GET',  '/admin/guru',                          'GuruAdminController@index',             'can:guru.lihat'],
    ['GET',  '/admin/guru/data',                     'GuruAdminController@data',              'can:guru.lihat'],
    ['GET',  '/admin/guru/{id}/riwayat',             'GuruAdminController@riwayat',           'can:guru.lihat'],
    ['GET',  '/admin/guru/{id}',                     'GuruAdminController@show',              'can:guru.lihat'],
    ['POST', '/admin/guru',                          'GuruAdminController@store',             'can:guru.tambah'],
    ['POST', '/admin/guru/{id}/ubah',                'GuruAdminController@update',            'can:guru.ubah'],
    ['POST', '/admin/guru/{id}/hapus',               'GuruAdminController@delete',            'can:guru.hapus'],

    // ---- Penempatan guru per tahun ajaran
    ['GET',  '/admin/penempatan/data',               'PenempatanAdminController@data',        'can:guru.lihat'],
    ['POST', '/admin/penempatan/salin',              'PenempatanAdminController@salin',       'can:guru.tambah'],
    ['GET',  '/admin/penempatan/{id}',               'PenempatanAdminController@show',        'can:guru.lihat'],
    ['POST', '/admin/penempatan',                    'PenempatanAdminController@store',       'can:guru.tambah'],
    ['POST', '/admin/penempatan/{id}/ubah',          'PenempatanAdminController@update',      'can:guru.ubah'],
    ['POST', '/admin/penempatan/{id}/hapus',         'PenempatanAdminController@delete',      'can:guru.hapus'],

    // ---- Akun pengguna
    ['GET',  '/admin/akun',                          'AkunAdminController@index',             'can:akun.lihat'],
    ['GET',  '/admin/akun/data',                     'AkunAdminController@data',              'can:akun.lihat'],
    ['GET',  '/admin/akun/{id}',                     'AkunAdminController@show',              'can:akun.lihat'],
    ['POST', '/admin/akun',                          'AkunAdminController@store',             'can:akun.tambah'],
    ['POST', '/admin/akun/{id}/ubah',                'AkunAdminController@update',            'can:akun.ubah'],
    ['POST', '/admin/akun/{id}/reset',               'AkunAdminController@reset',             'can:akun.ubah'],
    ['POST', '/admin/akun/{id}/hapus',               'AkunAdminController@delete',            'can:akun.hapus'],

    // ---- Mapel (katalog), mapel per semester, penugasan mengajar
    ['GET',  '/admin/mapel',                         'MapelAdminController@index',            'can:mapel.lihat'],
    ['GET',  '/admin/mapel/data',                    'MapelAdminController@data',             'can:mapel.lihat'],
    ['GET',  '/admin/mapel/opsi',                    'MapelAdminController@opsi',             'can:mapel.lihat'],
    ['GET',  '/admin/mapel/{id}',                    'MapelAdminController@show',             'can:mapel.lihat'],
    ['POST', '/admin/mapel',                         'MapelAdminController@store',            'can:mapel.tambah'],
    ['POST', '/admin/mapel/{id}/ubah',               'MapelAdminController@update',           'can:mapel.ubah'],
    ['POST', '/admin/mapel/{id}/hapus',              'MapelAdminController@delete',           'can:mapel.hapus'],

    ['GET',  '/admin/mapel-semester/data',           'MapelSemesterAdminController@data',     'can:mapel.lihat'],
    ['POST', '/admin/mapel-semester/salin',          'MapelSemesterAdminController@salin',    'can:mapel.tambah'],
    ['GET',  '/admin/mapel-semester/{id}',           'MapelSemesterAdminController@show',     'can:mapel.lihat'],
    ['POST', '/admin/mapel-semester',                'MapelSemesterAdminController@store',    'can:mapel.tambah'],
    ['POST', '/admin/mapel-semester/{id}/ubah',      'MapelSemesterAdminController@update',   'can:mapel.ubah'],
    ['POST', '/admin/mapel-semester/{id}/hapus',     'MapelSemesterAdminController@delete',   'can:mapel.hapus'],

    ['GET',  '/admin/penugasan/data',                'PenugasanAdminController@data',         'can:mapel.lihat'],
    ['GET',  '/admin/penugasan/opsi',                'PenugasanAdminController@opsi',         'can:mapel.lihat'],
    ['GET',  '/admin/penugasan/{id}',                'PenugasanAdminController@show',         'can:mapel.lihat'],
    ['POST', '/admin/penugasan',                     'PenugasanAdminController@store',        'can:mapel.tambah'],
    ['POST', '/admin/penugasan/{id}/ubah',           'PenugasanAdminController@update',       'can:mapel.ubah'],
    ['POST', '/admin/penugasan/{id}/hapus',          'PenugasanAdminController@delete',       'can:mapel.hapus'],

    // ---- Pengaturan aplikasi
    ['GET',  '/admin/pengaturan',                    'PengaturanController@index',            'can:pengaturan.lihat'],
    ['POST', '/admin/pengaturan',                    'PengaturanController@simpan',           'can:pengaturan.ubah'],

    // ---- Berkas privat
    ['GET',  '/berkas/guru/{id}',                    'BerkasController@guru',                 'auth'],
];
