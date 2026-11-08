(function ($) {
    'use strict';
    var S = SISLIA;
    var B = S.base + '/admin/periode';

    function aktifRender(d, type) {
        if (type !== 'display') { return d; }
        return Number(d) === 1 ? S.badge('Aktif', 'success') : '<span class="text-muted">-</span>';
    }

    // ---------------- Tahun ajaran
    var tahun = S.crud({
        table: '#tabel-tahun', modal: '#modal-tahun', form: '#form-tahun', addBtn: '#btn-tambah-tahun',
        urls: {
            list: B + '/tahun/data',
            show: function (id) { return B + '/tahun/' + id; },
            store: B + '/tahun',
            update: function (id) { return B + '/tahun/' + id + '/ubah'; },
            destroy: function (id) { return B + '/tahun/' + id + '/hapus'; }
        },
        columns: [
            { data: 'nama', render: function (d, t) { return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' : d; } },
            { data: 'tgl_mulai', render: S.dateRender },
            { data: 'tgl_selesai', render: S.dateRender },
            { data: 'jml_semester', className: 'text-center' },
            { data: 'is_aktif', render: aktifRender }
        ],
        order: [[1, 'desc']],
        onSaved: function () { semester.reload(); }
    });

    // ---------------- Semester
    var semester = S.crud({
        table: '#tabel-semester', modal: '#modal-semester', form: '#form-semester', addBtn: '#btn-tambah-semester',
        urls: {
            list: B + '/semester/data',
            show: function (id) { return B + '/semester/' + id; },
            store: B + '/semester',
            update: function (id) { return B + '/semester/' + id + '/ubah'; },
            destroy: function (id) { return B + '/semester/' + id + '/hapus'; }
        },
        columns: [
            { data: 'tahun' },
            { data: 'semester', render: function (d, t) { return t === 'display' ? '<strong>' + S.esc(d) + '</strong>' : d; } },
            { data: 'tgl_mulai', render: S.dateRender },
            { data: 'tgl_selesai', render: S.dateRender },
            { data: 'is_aktif', render: aktifRender },
            {
                data: 'is_terkunci',
                render: function (d, t) {
                    if (t !== 'display') { return d; }
                    return Number(d) === 1 ? S.badge('Terkunci', 'warning') : '<span class="text-muted">Terbuka</span>';
                }
            }
        ],
        order: [[1, 'desc']],
        extraActions: function (row) {
            var terkunci = Number(row.is_terkunci) === 1;
            return '<button type="button" class="btn btn-sm btn-outline-secondary me-1" ' +
                'data-post="' + B + '/semester/' + row.id + '/kunci" ' +
                'data-confirm="' + (terkunci ? 'Buka kunci semester ini? Data akan bisa diubah kembali.' : 'Kunci semester ini? Data tidak bisa diubah sampai kunci dibuka.') + '" ' +
                'data-yes="' + (terkunci ? 'Ya, buka' : 'Ya, kunci') + '" data-icon="question" ' +
                'title="' + (terkunci ? 'Buka kunci' : 'Kunci semester') + '">' +
                '<i class="fa-solid ' + (terkunci ? 'fa-lock-open' : 'fa-lock') + '"></i></button>';
        },
        onSaved: function () { tahun.reload(); }
    });
})(jQuery);
