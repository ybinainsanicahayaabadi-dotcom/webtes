<?php
/** Skrip di akhir <body>: vendor JS, app.js, JS halaman, data flash. Butuh: $libs, $pageJs */
?>
<?= vendor_tags('js', $libs) ?>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php foreach ($pageJs as $js): ?>
<script src="<?= e(asset('js/pages/' . $js)) ?>"></script>
<?php endforeach; ?>
<?php foreach (flash_pull() as $f): ?>
<div class="flash-data" hidden data-type="<?= e($f['type']) ?>" data-message="<?= e($f['message']) ?>"></div>
<?php endforeach; ?>
