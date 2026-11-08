(function ($) {
    'use strict';

    /* ---- Tombol kirim: cegah klik ganda ---- */
    var $btn = $('#form-login button[type="submit"]');
    var html = $btn.html();
    $('#form-login').on('submit', function () {
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');
    });
    $(window).on('pageshow', function () { $btn.prop('disabled', false).html(html); });

    /* ---- Semester mengikuti tahun ajaran yang dipilih ---- */
    var $tahun = $('#login-tahun');
    var $sem = $('#login-semester');
    if (!$tahun.length || !$sem.length) { return; }

    function sesuaikan(pilihAktif) {
        var tahunId = String($tahun.val());
        $sem.find('option').each(function () {
            var cocok = String($(this).data('tahun')) === tahunId;
            $(this).prop('disabled', !cocok).prop('hidden', !cocok);
        });
        var $terpilih = $sem.find('option:selected');
        if (pilihAktif || $terpilih.prop('disabled')) {
            var $kandidat = $sem.find('option:not(:disabled)');
            var $aktif = $kandidat.filter('[data-aktif="1"]').first();
            $sem.val(($aktif.length ? $aktif : $kandidat.first()).val());
        }
    }

    $tahun.on('change', function () { sesuaikan(true); });
    sesuaikan(false);
})(jQuery);
