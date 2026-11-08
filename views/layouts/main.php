<?php
/**
 * Layout utama (setelah login): sidebar + topbar + konten + footer.
 * Butuh: $content, $pageTitle, $pageCss, $pageJs, $authUser
 */
$libs = ['bootstrap', 'fontawesome', 'jquery', 'datatables', 'select2', 'sweetalert2'];
?><!doctype html>
<html lang="id" data-bs-theme="light" data-default-theme="<?= e(setting('tema_default', 'terang') === 'gelap' ? 'dark' : 'light') ?>">
<head>
<?php require __DIR__ . '/css.php'; ?>
</head>
<body class="app-body">
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="sidebar-backdrop" data-sidebar-close></div>

<div class="app-wrapper">
    <?php require __DIR__ . '/header.php'; ?>
    <main class="app-main">
        <?php if ($pageTitle !== ''): ?>
            <div class="page-head"><h1><?= e($pageTitle) ?></h1></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <?php require __DIR__ . '/footer.php'; ?>
</div>

<?php require __DIR__ . '/js.php'; ?>
</body>
</html>
