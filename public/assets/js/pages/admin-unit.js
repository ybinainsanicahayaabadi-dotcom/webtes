(function ($) {
    'use strict';
    var S = SISLIA;
    var B = S.base + '/admin/unit';
    var $prev = $('#unit-logo-preview');
    var $kosong = $('#unit-logo-kosong');

    function tampilLogo(src) {
        if (src) { $prev.attr('src', src).prop('hidden', false); $kosong.prop('hidden', true); }
        else { $prev.prop('hidden', true).attr('src', ''); $kosong.prop('hidden', false); }
    }

    // Pratinjau logo sebelum diunggah (validasi cepat di sisi klien; server tetap memeriksa ulang)
    $('#unit-logo').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        if (['image/png', 'image/jpeg', 'image/webp'].indexOf(f.type) < 0) {
            S.toast('error', 'Format logo harus JPG, PNG, atau WebP.');
            this.value = ''; return;
        }
        if (f.size > 2 * 1024 * 1024) {
            S.toast('error', 'Ukuran logo maksimal 2 MB.');
            this.value = ''; return;
        }
        $('#unit-hapus-logo').prop('checked', false);
        tampilLogo(URL.createObjectURL(f));
    });

    S.crud({
        table: '#tabel-unit', modal: '#modal-unit', form: '#form-unit', addBtn: '#btn-tambah-unit',
        urls: {
            list: B + '/data',
            show: function (id) { return B + '/' + id; },
            store: B,
            update: function (id) { return B + '/' + id + '/ubah'; },
            destroy: function (id) { return B + '/' + id + '/hapus'; }
        },
        columns: [
            {
                data: 'logo', orderable: false, searchable: false,
                render: function (d, t) {
                    if (t !== 'display') { return d; }
                    return d ? '<img class="tbl-logo" data-hide-on-error alt="" src="' + S.esc(S.base + '/' + d) + '">' : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'nama',
                render: function (d, t, row) {
                    return t === 'display' ? '<strong>' + S.esc(d) + '</strong><br><small class="text-muted">' + S.esc(row.kode) + '</small>' : d;
                }
            },
            { data: 'jenjang' },
            { data: 'kab_kota', render: function (d) { return S.esc(d || '-'); } },
            { data: 'telepon', render: function (d) { return S.esc(d || '-'); } },
            { data: 'urutan', className: 'text-center' },
            {
                data: 'is_aktif',
                render: function (d, t) {
                    if (t !== 'display') { return d; }
                    return Number(d) === 1 ? S.badge('Aktif', 'success') : S.badge('Nonaktif', 'secondary');
                }
            }
        ],
        order: [[6, 'asc']],
        onReset: function () { tampilLogo(null); },
        onFill: function (data) { tampilLogo(data.logo ? S.base + '/' + data.logo : null); }
    });
})(jQuery);
