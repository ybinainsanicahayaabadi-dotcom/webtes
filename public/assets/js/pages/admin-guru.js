(function ($) {
    'use strict';
    var S = SISLIA;
    var BG = S.base + '/admin/guru';
    var BP = S.base + '/admin/penempatan';

    function statusRender(d, t) {
        if (t !== 'display') { return d; }
        return d === 'aktif' ? S.badge('Aktif', 'success') : S.badge('Nonaktif', 'secondary');
    }

    /* ---------------- Foto: pratinjau ---------------- */
    var $prev = $('#guru-foto-preview');
    var $kosong = $('#guru-foto-kosong');
    function tampilFoto(src) {
        if (src) { $prev.attr('src', src).prop('hidden', false); $kosong.prop('hidden', true); }
        else { $prev.prop('hidden', true).attr('src', ''); $kosong.prop('hidden', false); }
    }
    $('#guru-foto').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        if (['image/png', 'image/jpeg', 'image/webp'].indexOf(f.type) < 0) {
            S.toast('error', 'Format foto harus JPG, PNG, atau WebP.'); this.value = ''; return;
        }
        if (f.size > 3 * 1024 * 1024) {
            S.toast('error', 'Ukuran foto maksimal 3 MB.'); this.value = ''; return;
        }
        $('#guru-hapus-foto').prop('checked', false);
        tampilFoto(URL.createObjectURL(f));
    });

    /* ---------------- Data guru ---------------- */
    var guru = S.crud({
        table: '#tabel-guru', modal: '#modal-guru', form: '#form-guru', addBtn: '#btn-tambah-guru',
        urls: {
            list: BG + '/data',
            show: function (id) { return BG + '/' + id; },
            store: BG,
            update: function (id) { return BG + '/' + id + '/ubah'; },
            destroy: function (id) { return BG + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) {
            d.tahun_id = $('#filter-tahun').val();
            d.unit_id = $('#filter-unit').val();
            d.status = $('#filter-status').val();
        },
        columns: [
            {
                data: 'foto', orderable: false, searchable: false,
                render: function (d, t, row) {
                    if (t !== 'display') { return d; }
                    return d
                        ? '<img class="tbl-foto" data-hide-on-error alt="" src="' + S.esc(S.base + '/berkas/guru/' + row.id + '?v=' + encodeURIComponent(d)) + '">'
                        : '<span class="avatar">' + S.esc((row.nama || '?').charAt(0).toUpperCase()) + '</span>';
                }
            },
            {
                data: 'nama',
                render: function (d, t, row) {
                    return t === 'display'
                        ? '<strong>' + S.esc(d) + '</strong>' + (row.niy ? '<br><small class="text-muted">NIY ' + S.esc(row.niy) + '</small>' : '')
                        : d;
                }
            },
            { data: 'unit', render: function (d) { return d ? S.esc(d) : '<span class="text-muted">Belum ditempatkan</span>'; } },
            { data: 'jabatan', render: function (d) { return S.esc(d || '-'); } },
            {
                data: 'telepon',
                render: function (d, t, row) {
                    return t === 'display' ? S.esc(d || '-') + (row.email ? '<br><small class="text-muted">' + S.esc(row.email) + '</small>' : '') : d;
                }
            },
            { data: 'status', render: statusRender }
        ],
        order: [[2, 'asc']],
        extraActions: function (row) {
            return '<button type="button" class="btn btn-sm btn-outline-info me-1" data-riwayat="' + row.id + '" title="Riwayat penempatan">' +
                '<i class="fa-solid fa-clock-rotate-left"></i></button>';
        },
        onSaved: function () { if (penempatan) { penempatan.reload(); } },
        onReset: function () { tampilFoto(null); },
        onFill: function (data) {
            tampilFoto(data.foto ? S.base + '/berkas/guru/' + data.id + '?v=' + encodeURIComponent(data.foto) : null);
        }
    });
    $('#filter-tahun, #filter-unit, #filter-status').on('change', function () { guru.reload(); });

    /* ---------------- Riwayat penempatan ---------------- */
    var modalRiwayat = bootstrap.Modal.getOrCreateInstance($('#modal-riwayat')[0]);
    $('#tabel-guru').on('click', '[data-riwayat]', function () {
        var id = $(this).data('riwayat');
        $.ajax({ url: BG + '/' + id + '/riwayat', dataType: 'json', global: false })
            .done(function (res) {
                $('#riwayat-judul').text('Riwayat Penempatan: ' + res.guru);
                var $b = $('#riwayat-isi').empty();
                if (!res.data.length) {
                    $b.append('<tr><td colspan="4" class="text-center text-muted">Belum pernah ditempatkan.</td></tr>');
                }
                $.each(res.data, function (i, r) {
                    $b.append('<tr><td>' + S.esc(r.tahun) + '</td><td>' + S.esc(r.unit) + '</td><td>' + S.esc(r.jabatan || '-') + '</td><td>' +
                        (r.status === 'aktif' ? S.badge('Aktif', 'success') : S.badge('Nonaktif', 'secondary')) + '</td></tr>');
                });
                modalRiwayat.show();
            })
            .fail(function (xhr) {
                if (xhr.status === 401) { window.location.href = S.base + '/login'; return; }
                S.toast('error', 'Riwayat tidak dapat dimuat.');
            });
    });

    /* ---------------- Penempatan ---------------- */
    var penempatan = S.crud({
        table: '#tabel-penempatan', modal: '#modal-penempatan', form: '#form-penempatan', addBtn: '#btn-tambah-penempatan',
        urls: {
            list: BP + '/data',
            show: function (id) { return BP + '/' + id; },
            store: BP,
            update: function (id) { return BP + '/' + id + '/ubah'; },
            destroy: function (id) { return BP + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) {
            d.tahun_id = $('#p-filter-tahun').val();
            d.unit_id = $('#p-filter-unit').val();
        },
        columns: [
            {
                data: 'guru',
                render: function (d, t, row) {
                    return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' + (row.niy ? '<br><small class="text-muted">NIY ' + S.esc(row.niy) + '</small>' : '') : d;
                }
            },
            { data: 'tahun' },
            { data: 'unit' },
            { data: 'jabatan', render: function (d) { return S.esc(d || '-'); } },
            { data: 'pangkat_golongan', render: function (d) { return S.esc(d || '-'); } },
            { data: 'status', render: statusRender }
        ],
        order: [[1, 'asc']],
        onSaved: function () { guru.reload(); },
        onReset: function () {
            var t = $('#p-filter-tahun').val(), u = $('#p-filter-unit').val();
            if (t) { $('#p-tahun').val(t); }
            if (u) { $('#p-unit').val(u).trigger('change'); }
        }
    });
    $('#p-filter-tahun, #p-filter-unit').on('change', function () { penempatan.reload(); });

    /* ---------------- Tab: hitung ulang lebar tabel yang tadinya tersembunyi ---------------- */
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });

    /* ---------------- Salin penempatan ---------------- */
    var modalSalin = bootstrap.Modal.getOrCreateInstance($('#modal-salin-p')[0]);
    $('#btn-salin-penempatan').on('click', function () {
        $('#form-salin-p')[0].reset();
        $('#form-salin-p .field-error').remove();
        modalSalin.show();
    });
    $('#form-salin-p').on('submit', function (e) {
        e.preventDefault();
        var $f = $(this);
        $f.find('.field-error').remove();
        var $btn = $f.find('[type="submit"]').prop('disabled', true);
        $.ajax({ url: BP + '/salin', method: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json', global: false })
            .done(function (res) {
                modalSalin.hide();
                Swal.fire({ icon: 'success', title: 'Selesai', text: res.message });
                penempatan.reload();
                guru.reload();
            })
            .fail(function (xhr) {
                if (xhr.status === 401) { window.location.href = S.base + '/login'; return; }
                var r = xhr.responseJSON || {};
                if (xhr.status === 422 && r.errors) {
                    $.each(r.errors, function (field, msg) {
                        $('<div class="field-error"></div>').text(msg).appendTo($f.find('[name="' + field + '"]').closest('.mb-3'));
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Tidak dapat menyalin', text: r.message || 'Terjadi kesalahan.' });
                }
            })
            .always(function () { $btn.prop('disabled', false); });
    });
})(jQuery);
