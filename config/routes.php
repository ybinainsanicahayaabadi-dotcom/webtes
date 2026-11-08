<?php
/**
 * SISLIA - daftar rute. Format: [METHOD, path, 'Controller@method', akses]
 * Akses: any | guest | auth | role:a,b | can:modul.aksi
 * {id} otomatis cocok dengan angka.
 */
return [
    ['GET',  '/',                          'AuthController@home',          'any'],
    ['GET',  '/login',                     'AuthController@showLogin',     'guest'],
    ['POST', '/login',                     'AuthController@login',         'guest'],
    ['POST', '/logout',                    'AuthController@logout',        'auth'],

    ['GET',  '/dashboard',                 'DashboardController@index',    'auth'],
    ['POST', '/periode/pilih',             'PeriodeController@pilih',      'auth'],

    ['GET',  '/profil',                    'ProfilController@index',       'auth'],
    ['POST', '/profil',                    'ProfilController@simpan',      'auth'],
    ['POST', '/profil/password',           'ProfilController@password',    'auth'],
    ['POST', '/profil/tanda-tangan',       'ProfilController@unggahTtd',   'auth'],
    ['POST', '/profil/tanda-tangan/hapus', 'ProfilController@hapusTtd',    'auth'],

    ['GET',  '/perangkat',                 'PerangkatController@index',    'auth'],
    ['POST', '/perangkat/cabut/{id}',      'PerangkatController@cabut',    'auth'],
    ['POST', '/perangkat/keluar-lainnya',  'PerangkatController@keluarLainnya', 'auth'],

    ['GET',  '/berkas/ttd/{id}',           'BerkasController@ttd',         'auth'],
];
