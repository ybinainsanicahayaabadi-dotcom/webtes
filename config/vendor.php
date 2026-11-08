<?php
/**
 * SISLIA - daftar library front-end.
 * Format: [file lokal (relatif ke public/assets/vendor), URL CDN cadangan].
 * Jalankan `php tools/unduh_vendor.php` agar semua file tersimpan lokal.
 * Bila file lokal belum ada, halaman otomatis memakai CDN.
 */
$jsd = 'https://cdn.jsdelivr.net/npm/';

return [
    'bootstrap' => [
        'css' => [['bootstrap/bootstrap.min.css', $jsd . 'bootstrap@5.3.3/dist/css/bootstrap.min.css']],
        'js'  => [['bootstrap/bootstrap.bundle.min.js', $jsd . 'bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js']],
    ],
    'fontawesome' => [
        'css' => [['fontawesome/css/all.min.css', $jsd . '@fortawesome/fontawesome-free@6.6.0/css/all.min.css']],
        'js'  => [],
    ],
    'jquery' => [
        'css' => [],
        'js'  => [['jquery/jquery.min.js', $jsd . 'jquery@3.7.1/dist/jquery.min.js']],
    ],
    'datatables' => [
        'css' => [
            ['datatables/dataTables.bootstrap5.min.css', $jsd . 'datatables.net-bs5@2.1.8/css/dataTables.bootstrap5.min.css'],
            ['datatables/responsive.bootstrap5.min.css', $jsd . 'datatables.net-responsive-bs5@3.0.3/css/responsive.bootstrap5.min.css'],
        ],
        'js'  => [
            ['datatables/dataTables.min.js',            $jsd . 'datatables.net@2.1.8/js/dataTables.min.js'],
            ['datatables/dataTables.bootstrap5.min.js', $jsd . 'datatables.net-bs5@2.1.8/js/dataTables.bootstrap5.min.js'],
            ['datatables/dataTables.responsive.min.js', $jsd . 'datatables.net-responsive@3.0.3/js/dataTables.responsive.min.js'],
            ['datatables/responsive.bootstrap5.min.js', $jsd . 'datatables.net-responsive-bs5@3.0.3/js/responsive.bootstrap5.min.js'],
        ],
    ],
    'select2' => [
        'css' => [
            ['select2/select2.min.css', $jsd . 'select2@4.1.0-rc.0/dist/css/select2.min.css'],
            ['select2/select2-bootstrap-5-theme.min.css', $jsd . 'select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css'],
        ],
        'js'  => [['select2/select2.min.js', $jsd . 'select2@4.1.0-rc.0/dist/js/select2.min.js']],
    ],
    'sweetalert2' => [
        'css' => [],
        'js'  => [['sweetalert2/sweetalert2.all.min.js', $jsd . 'sweetalert2@11.14.5/dist/sweetalert2.all.min.js']],
    ],
    // File tambahan yang ikut diunduh (font Font Awesome) - tidak dimuat langsung
    '_extra' => [
        ['fontawesome/webfonts/fa-solid-900.woff2',   $jsd . '@fortawesome/fontawesome-free@6.6.0/webfonts/fa-solid-900.woff2'],
        ['fontawesome/webfonts/fa-regular-400.woff2', $jsd . '@fortawesome/fontawesome-free@6.6.0/webfonts/fa-regular-400.woff2'],
        ['fontawesome/webfonts/fa-brands-400.woff2',  $jsd . '@fortawesome/fontawesome-free@6.6.0/webfonts/fa-brands-400.woff2'],
    ],
];
