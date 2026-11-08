<?php
/**
 * SISLIA - menu sidebar.
 * 'izin' => [modul, aksi] menentukan siapa yang melihat menu (null = semua yang login).
 * 'segera' => true menandai halaman yang dibuat pada tahap berikutnya.
 */
return [
    ['judul' => null, 'item' => [
        ['label' => 'Dashboard', 'ikon' => 'fa-gauge-high', 'url' => '/dashboard', 'izin' => null],
    ]],
    ['judul' => 'Mutu & Kinerja', 'item' => [
        ['label' => 'Standar Mutu', 'ikon' => 'fa-layer-group',    'url' => '/standar-mutu', 'izin' => ['standar_mutu', 'lihat']],
        ['label' => 'PKKS',         'ikon' => 'fa-user-tie',       'url' => '/pkks',         'izin' => ['pkks', 'lihat'],         'segera' => true],
        ['label' => 'Supervisi',    'ikon' => 'fa-clipboard-check','url' => '/supervisi',    'izin' => ['supervisi', 'lihat'],    'segera' => true],
    ]],
    ['judul' => 'Administrasi', 'item' => [
        ['label' => 'Periode',          'ikon' => 'fa-calendar-days',   'url' => '/admin/periode',    'izin' => ['periode', 'lihat']],
        ['label' => 'Unit Sekolah',     'ikon' => 'fa-school',          'url' => '/admin/unit',       'izin' => ['unit', 'lihat']],
        ['label' => 'Kelas',            'ikon' => 'fa-door-open',       'url' => '/admin/kelas',      'izin' => ['kelas', 'lihat']],
        ['label' => 'Guru',             'ikon' => 'fa-chalkboard-user', 'url' => '/admin/guru',       'izin' => ['guru', 'lihat']],
        ['label' => 'Mapel & Penugasan','ikon' => 'fa-book',            'url' => '/admin/mapel',      'izin' => ['mapel', 'lihat']],
        ['label' => 'Akun',             'ikon' => 'fa-users-gear',      'url' => '/admin/akun',       'izin' => ['akun', 'lihat']],
        ['label' => 'Pengaturan',       'ikon' => 'fa-sliders',         'url' => '/admin/pengaturan', 'izin' => ['pengaturan', 'lihat']],
    ]],
    ['judul' => 'Akun Saya', 'item' => [
        ['label' => 'Profil',   'ikon' => 'fa-id-card', 'url' => '/profil',   'izin' => null],
        ['label' => 'Perangkat','ikon' => 'fa-laptop',  'url' => '/perangkat','izin' => null],
    ]],
];
