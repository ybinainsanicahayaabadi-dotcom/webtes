/**
 * SISLIA - app.js
 * Perilaku umum: sidebar, tema, AJAX+CSRF, toast/konfirmasi SweetAlert2,
 * default Select2 dan DataTables. Bergantung pada jQuery.
 */
(function ($) {
    'use strict';

    var BASE = $('meta[name="base-url"]').attr('content') || '';
    var CSRF = $('meta[name="csrf-token"]').attr('content') || '';
    var SISLIA = window.SISLIA = { base: BASE, csrf: CSRF };

    /* ---------- AJAX: CSRF + penanda XHR ---------- */
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' } });
    $(document).ajaxError(function (e, xhr) {
        if (xhr.status === 401) { window.location.href = BASE + '/login'; return; }
        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server.';
        SISLIA.toast('error', msg);
    });

    /* ---------- SweetAlert2 ---------- */
    SISLIA.toast = function (icon, title) {
        if (!window.Swal) { return; }
        Swal.fire({ toast: true, position: 'top-end', icon: icon, title: title,
            showConfirmButton: false, timer: 4000, timerProgressBar: true });
    };

    SISLIA.confirm = function (opts) {
        return Swal.fire($.extend({
            icon: 'question', showCancelButton: true, confirmButtonText: 'Ya',
            cancelButtonText: 'Batal', reverseButtons: true, focusCancel: true
        }, opts)).then(function (r) { return r.isConfirmed; });
    };

    // <form data-confirm="Pesan"> meminta konfirmasi sebelum dikirim
    $(document).on('submit', 'form[data-confirm]', function (e) {
        var form = this;
        if (form.dataset.confirmed === '1') { return; }
        e.preventDefault();
        SISLIA.confirm({ title: $(form).data('confirm'), icon: 'warning' }).then(function (ok) {
            if (ok) { form.dataset.confirmed = '1'; form.submit(); }
        });
    });

    // Pesan flash dari server
    $('.flash-data').each(function () {
        var t = $(this).data('type');
        SISLIA.toast({ danger: 'error', success: 'success', warning: 'warning', info: 'info' }[t] || 'info', $(this).data('message'));
    });

    /* ---------- Sidebar (mobile) ---------- */
    $(document).on('click', '[data-sidebar-toggle]', function () { $('body').toggleClass('sidebar-open'); });
    $(document).on('click', '[data-sidebar-close]', function () { $('body').removeClass('sidebar-open'); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') { $('body').removeClass('sidebar-open'); } });

    /* ---------- Tema terang/gelap ---------- */
    $(document).on('click', '[data-theme-toggle]', function () {
        var html = document.documentElement;
        var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('sislia-theme', next); } catch (e) { /* abaikan */ }
    });

    /* ---------- Form & input ---------- */
    $(document).on('change', 'form[data-autosubmit] select', function () { this.form.submit(); });

    $(document).on('click', '[data-toggle-password]', function () {
        var $input = $($(this).data('toggle-password'));
        var show = $input.attr('type') === 'password';
        $input.attr('type', show ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
    });

    // Sembunyikan gambar yang gagal dimuat (mis. logo belum diunggah)
    document.addEventListener('error', function (e) {
        var t = e.target;
        if (t && t.tagName === 'IMG' && t.hasAttribute('data-hide-on-error')) { t.style.display = 'none'; }
    }, true);
    $('img[data-hide-on-error]').each(function () {
        if (this.complete && this.naturalWidth === 0) { this.style.display = 'none'; }
    });

    /* ---------- Select2 ---------- */
    if ($.fn.select2) {
        $.fn.select2.defaults.set('theme', 'bootstrap-5');
        $.fn.select2.defaults.set('width', '100%');
        $.fn.select2.defaults.set('language', {
            noResults: function () { return 'Tidak ada hasil'; },
            searching: function () { return 'Mencari...'; }
        });
        // Pakai: <select class="select2">. Di dalam modal, dropdown otomatis menempel ke modal.
        SISLIA.select2 = function (context) {
            $(context || document).find('select.select2').each(function () {
                var $s = $(this);
                var $modal = $s.closest('.modal');
                $s.select2({ dropdownParent: $modal.length ? $modal : $(document.body) });
            });
        };
        SISLIA.select2();
        $(document).on('select2:open', function () {
            var f = document.querySelector('.select2-container--open .select2-search__field');
            if (f) { f.focus(); }
        });
    }

    /* ---------- DataTables (responsif) ---------- */
    SISLIA.dtLang = {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
        zeroRecords: 'Data tidak ditemukan', emptyTable: 'Belum ada data',
        info: 'Menampilkan _START_-_END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
        infoFiltered: '(disaring dari _MAX_ data)', processing: 'Memuat...',
        paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    };

    /**
     * SISLIA.table('#tabel', opsi)
     * Aturan kolom: kolom pertama = tombol buka/tutup (<th></th> kosong).
     * Beri class "all" pada <th> Nama dan Aksi agar selalu tampil di HP;
     * kolom lain boleh diberi "min-tablet" atau "min-desktop" agar tersembunyi di layar kecil
     * dan muncul lewat tombol + di baris.
     */
    SISLIA.table = function (selector, options) {
        if (!$.fn.DataTable) { return null; }
        options = options || {};
        var defs = [{ className: 'dtr-control', orderable: false, targets: 0 }].concat(options.columnDefs || []);
        var cfg = $.extend(true, {}, {
            responsive: { details: { type: 'column', target: 0 } },
            autoWidth: false, pageLength: 10, lengthMenu: [10, 25, 50, 100],
            order: [[1, 'asc']], language: SISLIA.dtLang
        }, options);
        cfg.columnDefs = defs;
        return $(selector).DataTable(cfg);
    };
})(jQuery);
