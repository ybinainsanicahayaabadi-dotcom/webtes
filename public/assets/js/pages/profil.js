(function ($) {
    'use strict';
    // Pratinjau tanda tangan sebelum diunggah
    $('#ttd').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        if (f.type !== 'image/png') {
            SISLIA.toast('error', 'File harus berformat PNG.');
            this.value = '';
            return;
        }
        if (f.size > 1048576) {
            SISLIA.toast('error', 'Ukuran file maksimal 1 MB.');
            this.value = '';
            return;
        }
        $('#ttd-empty').hide();
        $('#ttd-preview').attr('src', URL.createObjectURL(f)).prop('hidden', false);
    });
})(jQuery);
