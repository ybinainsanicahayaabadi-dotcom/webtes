<?php
/** Sidebar: menu dari config/menu.php, disaring menurut izin peran. */
$menu = require ROOT_PATH . '/config/menu.php';
$pathNow = Router::path();
$logoYayasan = logo_url('assets/img/logo/yayasan.png');
?>
<aside class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="<?= e(url('/dashboard')) ?>">
        <?php if ($logoYayasan): ?>
            <img src="<?= e($logoYayasan) ?>" alt="Logo" width="38" height="38" data-hide-on-error>
        <?php else: ?>
            <span class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></span>
        <?php endif; ?>
        <span class="lh-sm">
            <strong class="d-block"><?= e(setting('app_nama', 'SISLIA')) ?></strong>
            <small class="d-block text-truncate"><?= e(setting('yayasan_nama', 'Yayasan Bina Insani')) ?></small>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Menu utama">
        <?php foreach ($menu as $grup):
            $items = array_filter($grup['item'], static function ($it) {
                return $it['izin'] === null || Rbac::can($it['izin'][0], $it['izin'][1]);
            });
            if (!$items) {
                continue;
            }
            ?>
            <?php if ($grup['judul']): ?><div class="nav-heading"><?= e($grup['judul']) ?></div><?php endif; ?>
            <?php foreach ($items as $it):
                $aktifMenu = $pathNow === $it['url'] || strpos($pathNow, $it['url'] . '/') === 0;
                ?>
                <?php if (!empty($it['segera'])): ?>
                    <span class="nav-link is-soon" title="Tersedia di tahap berikutnya">
                        <i class="fa-solid <?= e($it['ikon']) ?>"></i><span><?= e($it['label']) ?></span>
                        <em class="badge">Segera</em>
                    </span>
                <?php else: ?>
                    <a class="nav-link <?= $aktifMenu ? 'active' : '' ?>" href="<?= e(url($it['url'])) ?>">
                        <i class="fa-solid <?= e($it['ikon']) ?>"></i><span><?= e($it['label']) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>
