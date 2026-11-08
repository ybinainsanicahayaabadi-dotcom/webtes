<section class="hero-card mb-4">
    <div>
        <p class="mb-1 opacity-75"><?= e($salam) ?>,</p>
        <h2 class="mb-1"><?= e($authUser['nama_lengkap']) ?></h2>
        <p class="mb-0 opacity-75">
            <?= e(role_label($authUser['role'])) ?>
            <?php if (!empty($authUser['unit_nama'])): ?> &middot; <?= e($authUser['unit_nama']) ?><?php endif; ?>
        </p>
    </div>
    <div class="hero-meta">
        <?php if ($periode): ?>
            <span class="chip"><i class="fa-regular fa-calendar me-1"></i><?= e($periode['tahun']) ?> &middot; <?= e($periode['semester']) ?></span>
            <span class="chip"><i class="fa-solid <?= $periode['is_terkunci'] ? 'fa-lock' : 'fa-lock-open' ?> me-1"></i><?= $periode['is_terkunci'] ? 'Terkunci' : 'Terbuka' ?></span>
        <?php else: ?>
            <span class="chip">Belum ada periode</span>
        <?php endif; ?>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="stat-tile"><i class="fa-solid fa-school"></i><div><span class="stat-num"><?= (int) $ringkas['unit'] ?></span><span class="stat-label">Unit sekolah</span></div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="stat-tile"><i class="fa-solid fa-chalkboard-user"></i><div><span class="stat-num"><?= (int) $ringkas['guru'] ?></span><span class="stat-label">Guru &amp; tenaga aktif</span></div></div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="stat-tile"><i class="fa-solid fa-door-open"></i><div><span class="stat-num"><?= (int) $ringkas['kelas'] ?></span><span class="stat-label">Kelas tahun ini</span></div></div>
    </div>
</div>

<h2 class="section-title">Unit Sekolah</h2>
<?php if (!$units): ?>
    <div class="alert alert-info">Belum ada unit untuk ditampilkan.</div>
<?php endif; ?>
<div class="row g-3">
    <?php foreach ($units as $u): $logo = logo_url($u['logo']); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <article class="unit-card">
                <div class="unit-head">
                    <?php if ($logo): ?>
                        <img src="<?= e($logo) ?>" alt="" width="52" height="52" loading="lazy" data-hide-on-error>
                    <?php else: ?>
                        <span class="brand-mark"><i class="fa-solid fa-school"></i></span>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <h3 class="text-truncate"><?= e($u['nama']) ?></h3>
                        <small class="text-muted"><?= e($u['jenjang']) ?> &middot; <?= (int) $u['jml_guru'] ?> guru &middot; <?= (int) $u['jml_kelas'] ?> kelas</small>
                    </div>
                </div>
                <div class="unit-links">
                    <span class="soon-pill">Standar Mutu</span>
                    <span class="soon-pill">PKKS</span>
                    <span class="soon-pill">Supervisi</span>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
