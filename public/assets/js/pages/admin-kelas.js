(function ($) {
    'use strict';
    var S = SISLIA;
    var B = S.base + '/admin/kelas';
    var tingkatMap = JSON.parse($('#kelas-config').attr('data-tingkat') || '{}');

    var $unit = $('#kelas-unit');
    var $tahun = $('#kelas-tahun');
    var $tingkat = $('#kelas-tingkat');
    var $wali = $('#kelas-wali');
    var reqSeq = 0;

    /* ---- Tingkat mengikuti jenjang unit ---- */
    function isiTingkat(pilih) {
        var jenjang = $unit.find('option:selected').data('jenjang');
        var daftar = tingkatMap[jenjang] || [];
        var terpilih = pilih || $tingkat.val();
        $tingkat.empty().append('<option value="">- Pilih tingkat -</option>');
        $.each(daftar, function (i, t) { $tingkat.append($('<option>').val(t).text(t)); });
        $tingkat.val(daftar.indexOf(terpilih) >= 0 ? terpilih : '');
    }

    /* ---- Wali kelas: guru yang ditempatkan di unit + tahun ajaran terpilih ---- */
    function isiWali(pilih) {
        var unit = $unit.val(), tahun = $tahun.val();
        var seq = ++reqSeq;
        $wali.empty().append('<option value="">- Belum ditentukan -</option>');
        if (!unit || !tahun) { $wali.trigger('change'); return; }
        $.ajax({ url: B + '/guru-opsi', data: { unit_id: unit, tahun_id: tahun }, dataType: 'json', global: false })
            .done(function (res) {
                if (seq !== reqSeq) { return; }   // abaikan respons yang sudah usang
                $.each(res.data, function (i, g) {
                    $wali.append($('<option>').val(g.id).text(g.nama + (g.jabatan ? ' (' + g.jabatan + ')' : '')));
                });
                if (pilih) { $wali.val(String(pilih)); }
                $wali.trigger('change');
            });
    }

    $unit.on('change', function () { isiTingkat(); isiWali(null); });
    $tahun.on('change', function () { isiWali(null); });

    /* ---- Filter daftar ---- */
    var crud = S.crud({
        table: '#tabel-kelas', modal: '#modal-kelas', form: '#form-kelas', addBtn: '#btn-tambah-kelas',
        urls: {
            list: B + '/data',
            show: function (id) { return B + '/' + id; },
            store: B,
            update: function (id) { return B + '/' + id + '/ubah'; },
            destroy: function (id) { return B + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) {
            d.unit_id = $('#filter-unit').val();
            d.tahun_id = $('#filter-tahun').val();
        },
        columns: [
            { data: 'tahun' },
            { data: 'unit' },
            { data: 'tingkat', className: 'text-center' },
            { data: 'nama', render: function (d, t) { return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' : d; } },
            { data: 'wali', render: function (d) { return S.esc(d || '-'); } },
            { data: 'jumlah_siswa', className: 'text-center', render: function (d) { return d === null ? '-' : d; } }
        ],
        order: [[1, 'desc']],
        onReset: function () {
            // bawaan: tahun ajaran dan unit sesuai filter yang sedang aktif
            var t = $('#filter-tahun').val(), u = $('#filter-unit').val();
            if (t) { $tahun.val(t).trigger('change'); }
            if (u) { $unit.val(u).trigger('change'); }
            isiTingkat();
        },
        onFill: function (data) {
            isiTingkat(String(data.tingkat));
            isiWali(data.wali_guru_id);
        }
    });

    $('#filter-unit, #filter-tahun').on('change', function () { crud.reload(); });

    /* ---- Salin kelas antar tahun ajaran ---- */
    var modalSalin = bootstrap.Modal.getOrCreateInstance($('#modal-salin')[0]);
    $('#btn-salin-kelas').on('click', function () { $('#form-salin')[0].reset(); modalSalin.show(); });

    $('#form-salin').on('submit', function (e) {
        e.preventDefault();
        var $f = $(this);
        $f.find('.field-error').remove();
        var $btn = $f.find('[type="submit"]').prop('disabled', true);
        $.ajax({ url: B + '/salin', method: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json', global: false })
            .done(function (res) {
                modalSalin.hide();
                Swal.fire({ icon: 'success', title: 'Selesai', text: res.message });
                crud.reload();
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
