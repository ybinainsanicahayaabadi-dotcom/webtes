<?php
/** <head>: meta, tema awal, CSS vendor, CSS aplikasi. Butuh: $libs, $pageTitle, $pageCss */
$judul = ($pageTitle !== '' ? $pageTitle . ' | ' : '') . setting('app_nama', 'SISLIA');
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="base-url" content="<?= e(rtrim(BASE_URL, '/')) ?>">
<title><?= e($judul) ?></title>
<script src="<?= e(asset('js/theme-init.js')) ?>"></script>
<?= vendor_tags('css', $libs) ?>
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<?php foreach ($pageCss as $css): ?>
<link rel="stylesheet" href="<?= e(asset('css/pages/' . $css)) ?>">
<?php endforeach; ?>
