<?php
/** Topbar: tombol menu, pemilih periode, tema, menu pengguna. */
$semesters = Periode::semesters();
$aktif = Periode::current();
?>
<header class="topbar">
    <button type="button" class="btn-icon d-lg-none" data-sidebar-toggle aria-label="Buka menu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <form method="post" action="<?= e(url('/periode/pilih')) ?>" class="periode-form" data-autosubmit>
        <?= csrf_field() ?>
        <input type="hidden" name="kembali" value="<?= e(Router::path()) ?>">
        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
        <select name="semester_id" class="form-select form-select-sm" aria-label="Periode">
            <?php if (!$semesters): ?>
                <option value="">Belum ada periode</option>
            <?php endif; ?>
            <?php foreach ($semesters as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= $aktif && (int) $aktif['id'] === (int) $s['id'] ? 'selected' : '' ?>>
                    <?= e($s['tahun'] . ' - ' . $s['semester']) ?><?= $s['is_aktif'] ? ' (aktif)' : '' ?><?= $s['is_terkunci'] ? ' (terkunci)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($aktif && $aktif['is_terkunci']): ?>
            <span class="badge text-bg-warning" title="Semester terkunci, data tidak bisa diubah"><i class="fa-solid fa-lock"></i></span>
        <?php endif; ?>
    </form>

    <div class="ms-auto d-flex align-items-center gap-2">
        <button type="button" class="btn-icon" data-theme-toggle aria-label="Ganti tema">
            <i class="fa-solid fa-circle-half-stroke"></i>
        </button>

        <div class="dropdown">
            <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr($authUser['nama_lengkap'], 0, 1))) ?></span>
                <span class="d-none d-md-inline text-start lh-sm">
                    <span class="d-block fw-semibold small"><?= e($authUser['nama_lengkap']) ?></span>
                    <span class="d-block text-muted" style="font-size:.72rem"><?= e(role_label($authUser['role'])) ?></span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-2 d-md-none">
                    <div class="fw-semibold small"><?= e($authUser['nama_lengkap']) ?></div>
                    <div class="text-muted small"><?= e(role_label($authUser['role'])) ?></div>
                </li>
                <?php if (!empty($authUser['unit_nama'])): ?>
                    <li><span class="dropdown-item-text small text-muted"><i class="fa-solid fa-school me-1"></i><?= e($authUser['unit_nama']) ?></span></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="<?= e(url('/profil')) ?>"><i class="fa-regular fa-id-card me-2"></i>Profil</a></li>
                <li><a class="dropdown-item" href="<?= e(url('/perangkat')) ?>"><i class="fa-solid fa-laptop me-2"></i>Perangkat</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="<?= e(url('/logout')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
