/* Terapkan tema (terang/gelap) sebelum halaman digambar agar tidak berkedip. */
(function () {
    try {
        var html = document.documentElement;
        var saved = localStorage.getItem('sislia-theme');
        html.setAttribute('data-bs-theme', saved || html.getAttribute('data-default-theme') || 'light');
    } catch (e) { /* abaikan */ }
})();
