<div class="error-box text-center">
    <div class="error-code"><?= (int) $code ?></div>
    <h1 class="h4"><?= e($judul) ?></h1>
    <p class="text-muted"><?= e($isi) ?></p>
    <a class="btn btn-gradient" href="<?= e(url(Auth::check() ? '/dashboard' : '/login')) ?>">
        <i class="fa-solid fa-house me-1"></i><?= Auth::check() ? 'Ke Dashboard' : 'Ke halaman login' ?>
    </a>
</div>
