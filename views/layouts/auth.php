<?php
/** Layout halaman tanpa login (login, error untuk tamu). */
$libs = ['bootstrap', 'fontawesome', 'jquery', 'sweetalert2'];
?><!doctype html>
<html lang="id" data-bs-theme="light" data-default-theme="<?= e(setting('tema_default', 'terang') === 'gelap' ? 'dark' : 'light') ?>">
<head>
<?php require __DIR__ . '/css.php'; ?>
</head>
<body class="auth-body">
<div class="auth-bg" aria-hidden="true"></div>
<main class="auth-wrap">
    <?= $content ?>
</main>
<?php require __DIR__ . '/js.php'; ?>
</body>
</html>
