(function ($) {
    'use strict';
    var S = SISLIA;
    var cfg = $('#mapel-config');
    var mapelList = JSON.parse(cfg.attr('data-mapel') || '[]');
    var tingkatMap = JSON.parse(cfg.attr('data-tingkat') || '{}');

    /* ====================== KATALOG MAPEL ====================== */
    var BM = S.base + '/admin/mapel';
    var katalog = S.crud({
        table: '#tabel-mapel', modal: '#modal-mapel', form: '#form-mapel', addBtn: '#btn-tambah-mapel',
        urls: {
            list: BM + '/data',
            show: function (id) { return BM + '/' + id; },
            store: BM,
            update: function (id) { return BM + '/' + id + '/ubah'; },
            destroy: function (id) { return BM + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) { d.unit_id = $('#m-filter-unit').val(); },
        columns: [
            { data: 'unit' },
            { data: 'kode', render: function (d) { return S.esc(d || '-'); } },
            { data: 'nama', render: function (d, t) { return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' : d; } },
            { data: 'jml_semester', className: 'text-center' }
        ],
        order: [[1, 'asc']],
        onReset: function () {
            var u = $('#m-filter-unit').val();
            if (u) { $('#mapel-unit').val(u).trigger('change'); }
        },
        onSaved: function () { segarkanMapel(); }
    });
    $('#m-filter-unit').on('change', function () { katalog.reload(); });

    // Muat ulang daftar mapel untuk pilihan di modal Mapel per Semester
    function segarkanMapel() {
        $.ajax({ url: BM + '/opsi', dataType: 'json', global: false }).done(function (res) { mapelList = res.data; });
    }

    /* ====================== MAPEL PER SEMESTER ====================== */
    var BS = S.base + '/admin/mapel-semester';
    var $msUnit = $('#ms-unit'), $msMapel = $('#ms-mapel'), $msTingkat = $('#ms-tingkat');

    function isiMapel(pilih) {
        var unit = parseInt($msUnit.val(), 10);
        $msMapel.empty().append($('<option>').val('').text(unit ? '- Pilih mapel -' : '- Pilih unit dulu -'));
        $.each(mapelList, function (i, m) {
            if (parseInt(m.unit_id, 10) === unit) {
                $msMapel.append($('<option>').val(m.id).text(m.nama + (m.kode ? ' (' + m.kode + ')' : '')));
            }
        });
        if (pilih) { $msMapel.val(String(pilih)); }
        $msMapel.trigger('change');
    }

    function isiTingkat(pilih) {
        var jenjang = $msUnit.find('option:selected').data('jenjang');
        var daftar = tingkatMap[jenjang] || [];
        $msTingkat.empty().append('<option value="">Semua tingkat</option>');
        $.each(daftar, function (i, t) { $msTingkat.append($('<option>').val(t).text('Tingkat ' + t)); });
        $msTingkat.val(pilih !== undefined && daftar.indexOf(pilih) >= 0 ? pilih : '');
    }

    $msUnit.on('change', function () { isiMapel(null); isiTingkat(); });

    var ms = S.crud({
        table: '#tabel-ms', modal: '#modal-ms', form: '#form-ms', addBtn: '#btn-tambah-ms',
        urls: {
            list: BS + '/data',
            show: function (id) { return BS + '/' + id; },
            store: BS,
            update: function (id) { return BS + '/' + id + '/ubah'; },
            destroy: function (id) { return BS + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) {
            d.semester_id = $('#ms-filter-semester').val();
            d.unit_id = $('#ms-filter-unit').val();
        },
        columns: [
            {
                data: 'mapel',
                render: function (d, t, row) {
                    return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' + (row.kode ? '<br><small class="text-muted">' + S.esc(row.kode) + '</small>' : '') : d;
                }
            },
            { data: 'unit' },
            { data: 'tingkat', className: 'text-center', render: function (d) { return d ? S.esc(d) : '<span class="text-muted">Semua</span>'; } },
            { data: 'jam_per_minggu', className: 'text-center', render: function (d) { return d === null ? '-' : d; } },
            { data: 'jml_tugas', className: 'text-center' }
        ],
        order: [[1, 'asc']],
        onReset: function () {
            var s = $('#ms-filter-semester').val(), u = $('#ms-filter-unit').val();
            if (s) { $('#ms-semester').val(s); }
            if (u) { $msUnit.val(u).trigger('change'); } else { isiMapel(null); isiTingkat(); }
        },
        onFill: function (data) {
            $msUnit.val(String(data.unit_id)).trigger('change');     // membangun ulang mapel & tingkat
            isiMapel(data.mapel_id);
            isiTingkat(data.tingkat === null ? '' : String(data.tingkat));
        },
        onSaved: function () { if (typeof pgReload === 'function') { pgReload(); } }
    });
    $('#ms-filter-semester, #ms-filter-unit').on('change', function () { ms.reload(); });

    /* ---- Salin antar semester ---- */
    var modalSalin = bootstrap.Modal.getOrCreateInstance($('#modal-salin-ms')[0]);
    $('#btn-salin-ms').on('click', function () {
        $('#form-salin-ms')[0].reset();
        $('#form-salin-ms .field-error').remove();
        var ke = $('#ms-filter-semester').val();
        if (ke) { $('#sms-ke').val(ke); }
        modalSalin.show();
    });
    $('#form-salin-ms').on('submit', function (e) {
        e.preventDefault();
        var $f = $(this);
        $f.find('.field-error').remove();
        var $btn = $f.find('[type="submit"]').prop('disabled', true);
        $.ajax({ url: BS + '/salin', method: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json', global: false })
            .done(function (res) {
                modalSalin.hide();
                Swal.fire({ icon: 'success', title: 'Selesai', text: res.message });
                ms.reload();
                $(document).trigger('sislia:mapel-berubah');
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

    /* ---- Tab: hitung ulang lebar tabel yang tadinya tersembunyi ---- */
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });
})(jQuery);
