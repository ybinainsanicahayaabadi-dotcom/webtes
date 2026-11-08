/**
 * SISLIA - crud.js (pola CRUD modal + DataTables server-side)
 *
 * Pemakaian:
 *   var crud = SISLIA.crud({
 *       table:  '#tabel-x',          // <table> dengan <th></th> kosong di kolom pertama
 *       modal:  '#modal-x',          // dibuat dengan UI::modalForm(...)
 *       form:   '#form-x',
 *       addBtn: '#btn-tambah-x',
 *       urls: {
 *           list:    BASE + '/admin/x/data',
 *           show:    function (id) { return BASE + '/admin/x/' + id; },
 *           store:   BASE + '/admin/x',
 *           update:  function (id) { return BASE + '/admin/x/' + id + '/ubah'; },
 *           destroy: function (id) { return BASE + '/admin/x/' + id + '/hapus'; }
 *       },
 *       columns: [ { data: 'nama' }, ... ],     // tanpa kolom kontrol & aksi (ditambahkan otomatis)
 *       order: [[1, 'asc']],                    // indeks 0 = kolom kontrol
 *       ajaxData:     function (d) { d.unit_id = ...; },   // parameter tambahan untuk daftar (filter)
 *       extraActions: function (row) { return '<button data-post="..." data-confirm="...">'; },
 *       onFill:  function (data) {},            // setelah formulir terisi data (mode ubah)
 *       onReset: function () {},                // saat formulir dikosongkan (mode tambah)
 *       onSaved: function (res) {}
 *   });
 *
 * Kontrak respons server: {ok:true,message} | {ok:false,message,errors:{field:'pesan'}} (422/404/409).
 * Tombol di dalam tabel dengan atribut data-post="URL" data-confirm="Teks" akan memicu POST + konfirmasi.
 * Formulir dikirim sebagai FormData, jadi <input type="file"> dan <select multiple name="x[]"> ikut terkirim.
 */
(function ($) {
    'use strict';
    var S = window.SISLIA;
    var BULAN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /* ---------- helper tampilan ---------- */
    S.esc = function (v) { return $('<div>').text(v === null || v === undefined ? '' : v).html(); };
    S.fmtDate = function (s) {
        if (!s) { return '-'; }
        var p = String(s).slice(0, 10).split('-');
        return parseInt(p[2], 10) + ' ' + BULAN[parseInt(p[1], 10)] + ' ' + p[0];
    };
    S.badge = function (text, color) { return '<span class="badge text-bg-' + color + '">' + S.esc(text) + '</span>'; };
    S.dateRender = function (d, type) { return type === 'display' ? S.fmtDate(d) : d; };

    function pesanGalat(xhr, bawaan) {
        return (xhr.responseJSON && xhr.responseJSON.message) || bawaan || 'Terjadi kesalahan pada server.';
    }

    /* ---------- aksi POST dengan konfirmasi (hapus, kunci, dst.) ---------- */
    function postAksi($btn, table, done) {
        S.confirm({
            title: $btn.data('confirm') || 'Lanjutkan?',
            icon: $btn.data('icon') || 'warning',
            confirmButtonText: $btn.data('yes') || 'Ya, lanjutkan'
        }).then(function (ok) {
            if (!ok) { return; }
            $.ajax({ url: $btn.data('post'), method: 'POST', dataType: 'json', global: false })
                .done(function (res) {
                    S.toast('success', res.message || 'Berhasil.');
                    table.ajax.reload(null, false);
                    if (done) { done(res); }
                })
                .fail(function (xhr) {
                    if (xhr.status === 401) { window.location.href = S.base + '/login'; return; }
                    Swal.fire({ icon: 'error', title: 'Tidak dapat diproses', text: pesanGalat(xhr) });
                });
        });
    }

    /* ---------- CRUD modal ---------- */
    S.crud = function (opt) {
        var $modal = $(opt.modal);
        var $form = $(opt.form);
        var modal = bootstrap.Modal.getOrCreateInstance($modal[0]);
        var $title = $modal.find('.modal-title');

        var cols = [{ data: null, defaultContent: '', orderable: false, searchable: false, className: 'dtr-control' }]
            .concat(opt.columns);
        cols.push({
            data: null, orderable: false, searchable: false, className: 'text-end text-nowrap',
            render: function (d, t, row) {
                return (opt.extraActions ? opt.extraActions(row) : '') +
                    '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-act="edit" data-id="' + row.id + '" title="Ubah">' +
                    '<i class="fa-regular fa-pen-to-square"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-post="' + S.esc(opt.urls.destroy(row.id)) + '" ' +
                    'data-confirm="Hapus data ini?" data-yes="Ya, hapus" title="Hapus"><i class="fa-regular fa-trash-can"></i></button>';
            }
        });

        if ($.fn.dataTable) { $.fn.dataTable.ext.errMode = 'none'; }
        var table = S.table(opt.table, {
            serverSide: true, processing: true,
            ajax: { url: opt.urls.list, type: 'GET', data: opt.ajaxData },
            columns: cols,
            order: opt.order || [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: -1 }]
        });
        $(opt.table).on('error.dt', function () { S.toast('error', 'Gagal memuat data.'); });

        /* -- form -- */
        function clearErrors() {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.field-error').remove();
        }

        function showErrors(errors) {
            var first = null;
            $.each(errors, function (field, msg) {
                // field biasa: name="x"; field larik: name="x[]"
                var $f = $form.find('[name="' + field + '"], [name="' + field + '[]"]').first();
                if (!$f.length) { return; }
                $f.addClass('is-invalid');
                var $wrap = $f.closest('.mb-3');
                $('<div class="field-error"></div>')
                    .text($.isArray(msg) ? msg[0] : msg)
                    .appendTo($wrap.length ? $wrap : $f.parent());
                if (!first) { first = $f; }
            });
            if (first) { first.trigger('focus'); }
        }

        function resetForm() {
            $form[0].reset();
            $form.find('[name="id"]').val('');
            $form.find('select.select2').trigger('change');
            clearErrors();
            if (opt.onReset) { opt.onReset(); }
        }

        function fill(data) {
            $.each(data, function (key, val) {
                var $f = $form.find('[name="' + key + '"]');
                if (!$f.length || $f.is(':file')) { return; }
                if ($f.is(':checkbox')) {
                    $f.prop('checked', val === true || val === 1 || val === '1');
                } else {
                    $f.val(val === null ? '' : val);
                    if ($f.is('select')) { $f.trigger('change'); }
                }
            });
            if (opt.onFill) { opt.onFill(data); }
        }

        function bukaModal(mode) {
            $title.text($title.data(mode === 'edit' ? 'titleEdit' : 'titleAdd'));
            modal.show();
        }

        $modal.on('shown.bs.modal', function () {
            $form.find('input:not([type=hidden]):not([type=checkbox]):not([type=file]), select').filter(':visible').first().trigger('focus');
        });

        $(opt.addBtn).on('click', function () { resetForm(); bukaModal('add'); });

        $(opt.table).on('click', '[data-act="edit"]', function () {
            var id = $(this).data('id');
            $.ajax({ url: opt.urls.show(id), dataType: 'json', global: false })
                .done(function (res) { resetForm(); fill(res.data); bukaModal('edit'); })
                .fail(function (xhr) {
                    if (xhr.status === 401) { window.location.href = S.base + '/login'; return; }
                    Swal.fire({ icon: 'error', title: 'Gagal', text: pesanGalat(xhr, 'Data tidak dapat dimuat.') });
                });
        });

        $(opt.table).on('click', '[data-post]', function () { postAksi($(this), table, opt.onSaved); });

        $form.on('submit', function (e) {
            e.preventDefault();
            clearErrors();
            var id = $form.find('[name="id"]').val();
            var $btn = $form.find('[type="submit"]').prop('disabled', true);
            $.ajax({
                url: id ? opt.urls.update(id) : opt.urls.store, method: 'POST', dataType: 'json', global: false,
                data: new FormData($form[0]), processData: false, contentType: false
            }).done(function (res) {
                modal.hide();
                S.toast('success', res.message || 'Tersimpan.');
                table.ajax.reload(null, false);
                if (opt.onSaved) { opt.onSaved(res); }
            }).fail(function (xhr) {
                if (xhr.status === 401) { window.location.href = S.base + '/login'; return; }
                var r = xhr.responseJSON || {};
                if (xhr.status === 422 && r.errors && !$.isEmptyObject(r.errors)) {
                    showErrors(r.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal menyimpan', text: pesanGalat(xhr) });
                }
            }).always(function () { $btn.prop('disabled', false); });
        });

        return { table: table, modal: modal, form: $form, reload: function () { table.ajax.reload(null, false); } };
    };
})(jQuery);
