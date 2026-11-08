(function ($) {
    'use strict';
    var S = SISLIA;
    var B = S.base + '/admin/akun';
    var ROLE_GURU = JSON.parse($('#akun-config').attr('data-role-guru') || '[]');
    var ROLE = {
        admin: ['Admin', 'danger'], ketua_bidang: ['Ketua Bidang', 'primary'],
        kepala_sekolah: ['Kepala Sekolah', 'info'], wakil_kepala: ['Wakil Kepala', 'secondary'], guru: ['Guru', 'light text-dark border']
    };
    var $role = $('#akun-role'), $guruWrap = $('#akun-guru-wrap'), $guru = $('#akun-guru');
    var $pw = $('#akun-password'), $nama = $('#akun-nama');
    var $prev = $('#akun-ttd-preview'), $kosong = $('#akun-ttd-kosong');

    function tampilTtd(src) {
        if (src) { $prev.attr('src', src).prop('hidden', false); $kosong.prop('hidden', true); }
        else { $prev.prop('hidden', true).attr('src', ''); $kosong.prop('hidden', false); }
    }

    function aturPeran() {
        var perluGuru = ROLE_GURU.indexOf($role.val()) >= 0;
        $guruWrap.toggle(perluGuru);
        if (!perluGuru) { $guru.val('').trigger('change'); }
    }
    $role.on('change', aturPeran);

    // Memilih data guru mengisi nama lengkap bila masih kosong
    $guru.on('change', function () {
        var nm = $guru.find('option:selected').data('nama');
        if (nm && !$nama.val()) { $nama.val(nm); }
    });

    // Password acak (12 karakter, pasti ada huruf & angka) dibuat di browser
    $('#akun-generate').on('click', function () {
        var abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        var out;
        do {
            var buf = new Uint32Array(12);
            window.crypto.getRandomValues(buf);
            out = Array.prototype.map.call(buf, function (n) { return abjad.charAt(n % abjad.length); }).join('');
        } while (!/[A-Za-z]/.test(out) || !/\d/.test(out));
        $pw.val(out).attr('type', 'text');
        $('[data-toggle-password="#akun-password"] i').removeClass('fa-eye').addClass('fa-eye-slash');
        S.toast('info', 'Password acak dibuat. Catat sebelum menyimpan.');
    });

    $('#akun-ttd').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        if (f.type !== 'image/png') { S.toast('error', 'Tanda tangan harus berformat PNG.'); this.value = ''; return; }
        if (f.size > 1048576) { S.toast('error', 'Ukuran file maksimal 1 MB.'); this.value = ''; return; }
        $('#akun-hapus-ttd').prop('checked', false);
        tampilTtd(URL.createObjectURL(f));
    });

    function waktu(s) {
        if (!s) { return '<span class="text-muted">Belum pernah</span>'; }
        return S.esc(S.fmtDate(s) + ' ' + String(s).slice(11, 16));
    }

    var crud = S.crud({
        table: '#tabel-akun', modal: '#modal-akun', form: '#form-akun', addBtn: '#btn-tambah-akun',
        urls: {
            list: B + '/data',
            show: function (id) { return B + '/' + id; },
            store: B,
            update: function (id) { return B + '/' + id + '/ubah'; },
            destroy: function (id) { return B + '/' + id + '/hapus'; }
        },
        ajaxData: function (d) { d.role = $('#filter-role').val(); d.status = $('#filter-status').val(); },
        columns: [
            {
                data: 'nama_lengkap',
                render: function (d, t, row) {
                    return t === 'display'
                        ? '<strong>' + S.esc(d) + '</strong><br><small class="text-muted">@' + S.esc(row.username) + '</small>'
                        : d;
                }
            },
            {
                data: 'role',
                render: function (d, t) {
                    if (t !== 'display') { return d; }
                    var r = ROLE[d] || [d, 'secondary'];
                    return S.badge(r[0], r[1]);
                }
            },
            { data: 'unit', render: function (d) { return S.esc(d || '-'); } },
            {
                data: 'is_aktif',
                render: function (d, t) {
                    if (t !== 'display') { return d; }
                    return Number(d) === 1 ? S.badge('Aktif', 'success') : S.badge('Nonaktif', 'secondary');
                }
            },
            { data: 'last_login_at', render: function (d, t) { return t === 'display' ? waktu(d) : d; } },
            {
                data: 'punya_ttd', className: 'text-center',
                render: function (d, t) {
                    if (t !== 'display') { return d; }
                    return Number(d) === 1 ? '<i class="fa-solid fa-circle-check text-success" title="Ada"></i>' : '<i class="fa-regular fa-circle text-muted" title="Belum"></i>';
                }
            }
        ],
        order: [[1, 'asc']],
        extraActions: function (row) {
            return '<button type="button" class="btn btn-sm btn-outline-warning me-1" data-reset="' + S.esc(B + '/' + row.id + '/reset') + '" ' +
                'data-nama="' + S.esc(row.nama_lengkap) + '" title="Reset password"><i class="fa-solid fa-key"></i></button>';
        },
        onReset: function () {
            tampilTtd(null);
            $pw.attr('type', 'password').attr('placeholder', $pw.data('placeholderBaru'));
            $('#akun-pw-wajib').show();
            $('#akun-aktif, #akun-wajib-ganti').prop('checked', true);
            aturPeran();
        },
        onFill: function (data) {
            tampilTtd(data.tanda_tangan ? S.base + '/berkas/ttd/' + data.id + '?v=' + encodeURIComponent(data.tanda_tangan) : null);
            $pw.val('').attr('type', 'password').attr('placeholder', $pw.data('placeholderUbah'));
            $('#akun-pw-wajib').hide();
            aturPeran();
        }
    });
    $('#filter-role, #filter-status').on('change', function () { crud.reload(); });

    /* ---------------- Reset password ---------------- */
    $('#tabel-akun').on('click', '[data-reset]', function () {
        var url = $(this).data('reset');
        var nama = $(this).data('nama');
        S.confirm({
            title: 'Reset password ' + nama + '?',
            text: 'Password baru dibuat acak, pengguna dikeluarkan dari semua perangkat dan wajib menggantinya saat login.',
            icon: 'warning', confirmButtonText: 'Ya, reset'
        }).then(function (ok) {
            if (!ok) { return; }
            $.ajax({ url: url, method: 'POST', dataType: 'json', global: false })
                .done(function (res) {
                    Swal.fire({
                        icon: 'success', title: 'Password baru',
                        html: '<p class="mb-2">Username: <strong>' + S.esc(res.username) + '</strong></p>' +
                            '<p class="mb-2"><code id="pw-baru" style="font-size:1.25rem">' + S.esc(res.password) + '</code></p>' +
                            '<button type="button" id="pw-salin" class="btn btn-sm btn-outline-secondary">Salin</button>' +
                            '<p class="small text-muted mt-3 mb-0">Password hanya tampil sekali. Sampaikan kepada pengguna secara aman.</p>',
                        confirmButtonText: 'Tutup', allowOutsideClick: false,
                        didOpen: function () {
                            $('#pw-salin').on('click', function () {
                                if (navigator.clipboard) {
                                    navigator.clipboard.writeText(res.password).then(function () { S.toast('success', 'Tersalin.'); });
                                }
                            });
                        }
                    });
                    crud.reload();
                })
                .fail(function (xhr) {
                    if (xhr.status === 401) { window.location.href = S.base + '/login'; return; }
                    Swal.fire({ icon: 'error', title: 'Gagal', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan.' });
                });
        });
    });
})(jQuery);
