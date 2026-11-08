(function ($) {
    'use strict';
    SISLIA.table('#tabel-perangkat', {
        order: [[3, 'desc']],
        columnDefs: [{ orderable: false, targets: -1 }]
    });
})(jQuery);
