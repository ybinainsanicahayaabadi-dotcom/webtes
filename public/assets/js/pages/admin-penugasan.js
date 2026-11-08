/* Penugasan mengajar (guru - kelas - mapel semester). Bergantung pada crud.js. */
var pgReload = null;

(function ($) {
    'use strict';
    var S = SISLIA;
    var B = S.base + '/admin/penugasan';
    var $sem = $('#pg-semester'), $unit = $('#pg-unit'), $guru = $('#pg-guru'), $mapel = $('#pg-mapel'), $kelas = $('#pg-kelas');
    var seq = 0;

    /* Pilihan guru, kelas, dan mapel bergantung pada semester + unit */
    function muatOpsi(pilih) {
        var token = ++seq;
        $guru.empty().append('<option value="">- Pilih guru -</option>');
        $mapel.empty().append('<option value="">- Pilih mapel -</option>');
        $kelas.empty();
        if (!$sem.val() || !$unit.val()) { $guru.add($mapel).add($kelas).trigger('change'); return; }

        $.ajax({ url: B + '/opsi', data: { semester_id: $sem.val(), unit_id: $unit.val() }, dataType: 'json', global: false })
            .done(function (res) {
                if (token !== seq) { return; }   // abaikan respons yang sudah usang
                $.each(res.guru, function (i, g) {
                    $guru.append($('<option>').val(g.id).text(g.nama + (g.jabatan ? ' (' + g.jabatan + ')' : '')));
                });
                $.each(res.mapel, function (i, m) {
                    $mapel.append($('<option>').val(m.id).text(m.nama + (m.tingkat ? ' - tingkat ' + m.tingkat : '') + (m.jam_per_minggu ? ' (' + m.jam_per_minggu + ' jam)' : '')));
                });
                $.each(res.kelas, function (i, k) {
                    $kelas.append($('<option>').val(k.id).text(k.nama));
                });
                if (pilih) {
                    $guru.val(String(pilih.guru));
                    $mapel.val(String(pilih.mapel));
                    $kelas.val($.map(pilih.kelas, String));
                }
                $guru.add($mapel).add($kelas).trigger('change');
            });
    }
    $sem.on('change', function () { muatOpsi(null); });
    $unit.on('change', function () { muatOpsi(null); });

    var crud = S.crud({
        table: '#tabel-pg', modal: '#modal-pg', form: '#form-pg', addBtn: '#btn-tambah-pg',
        urls: {
            list: B + '/data',
            show: function (id) { return B + '/' + id; },
            store: B,
            update: function (id) { return B + '/' + id + '/ubah'; },
            destroy: function (id) { return B + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) {
            d.semester_id = $('#pg-filter-semester').val();
            d.unit_id = $('#pg-filter-unit').val();
        },
        columns: [
            { data: 'guru', render: function (d, t) { return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' : d; } },
            { data: 'kelas' },
            { data: 'mapel', render: function (d, t, row) { return t === 'display' ? S.esc(d) + (row.tingkat ? ' <small class="text-muted">(tk ' + S.esc(row.tingkat) + ')</small>' : '') : d; } },
            { data: 'unit' },
            { data: 'jam_per_minggu', className: 'text-center', render: function (d) { return d === null ? '-' : d; } }
        ],
        order: [[2, 'asc']],
        onReset: function () {
            var s = $('#pg-filter-semester').val(), u = $('#pg-filter-unit').val();
            if (s) { $sem.val(s); }
            if (u) { $unit.val(u).trigger('change'); } else { muatOpsi(null); }
        },
        onFill: function (data) {
            muatOpsi({ guru: data.guru_id, mapel: data.mapel_semester_id, kelas: [data.kelas_id] });
        }
    });
    pgReload = crud.reload;

    $('#pg-filter-semester, #pg-filter-unit').on('change', function () { crud.reload(); });
    $(document).on('sislia:mapel-berubah', function () { crud.reload(); });
})(jQuery);
