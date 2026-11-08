<?php
/**
 * SISLIA - matriks hak akses per peran.
 * Struktur: peran => [modul => [aksi, ...]]. '*' = semua.
 * Aksi umum: lihat, tambah, ubah, hapus, isi, verifikasi, cetak.
 * Pembatasan unit (kepsek/wakil/guru hanya unit sendiri) dicek terpisah lewat Rbac::unitScope().
 */
return [
    'admin' => ['*' => ['*']],

    'ketua_bidang' => [
        'dashboard'    => ['lihat'],
        'standar_mutu' => ['lihat', 'verifikasi'],
        'pkks'         => ['lihat', 'isi', 'verifikasi', 'cetak'],
        'supervisi'    => ['lihat', 'isi', 'verifikasi', 'cetak'],
        'kuesioner'    => ['lihat'],
        'bukti'        => ['lihat', 'verifikasi'],
        'laporan'      => ['lihat', 'cetak'],
    ],

    'kepala_sekolah' => [
        'dashboard'    => ['lihat'],
        'standar_mutu' => ['lihat', 'isi', 'verifikasi'],
        'pkks'         => ['lihat', 'isi', 'cetak'],
        'supervisi'    => ['lihat', 'isi', 'verifikasi', 'cetak'],
        'kuesioner'    => ['lihat'],
        'bukti'        => ['lihat', 'tambah', 'hapus', 'verifikasi'],
        'laporan'      => ['lihat', 'cetak'],
    ],

    'wakil_kepala' => [
        'dashboard'    => ['lihat'],
        'standar_mutu' => ['lihat', 'isi'],
        'pkks'         => ['lihat'],
        'supervisi'    => ['lihat', 'isi', 'cetak'],
        'bukti'        => ['lihat', 'tambah'],
        'laporan'      => ['lihat'],
    ],

    'guru' => [
        'dashboard' => ['lihat'],
        'supervisi' => ['lihat'],
        'kuesioner' => ['isi'],
        'bukti'     => ['lihat', 'tambah'],
    ],
];
