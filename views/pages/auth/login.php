<?php $logo = logo_url('assets/img/logo/yayasan.png'); ?>
<div class="auth-card">
    <div class="text-center mb-4">
        <?php if ($logo): ?>
            <img src="<?= e($logo) ?>" alt="Logo Yayasan" width="72" height="72" class="mb-3" data-hide-on-error>
        <?php else: ?>
            <span class="brand-mark brand-mark-lg mb-3"><i class="fa-solid fa-graduation-cap"></i></span>
        <?php endif; ?>
        <h1 class="auth-title"><?= e(setting('app_nama', 'SISLIA')) ?></h1>
        <p class="text-muted small mb-0"><?= e(setting('app_kepanjangan', 'Sistem Satuan Layanan Informasi Akademik')) ?></p>
        <p class="text-muted small"><?= e(setting('yayasan_nama', '')) ?></p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small" role="alert"><i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/login')) ?>" autocomplete="on" id="form-login">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username" value="<?= e($oldUser) ?>"
                       maxlength="60" required autofocus autocomplete="username">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password" aria-label="Tampilkan password">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <?php if ($semesters): ?>
            <div class="row g-2 mb-4">
                <div class="col-7">
                    <label class="form-label" for="login-tahun">Tahun ajaran</label>
                    <select class="form-select" id="login-tahun" name="tahun_ajaran_id">
                        <?php foreach ($tahunList as $tid => $tnama): ?>
                            <option value="<?= (int) $tid ?>" <?= (int) $tid === (int) $pilihTa ? 'selected' : '' ?>><?= e($tnama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-5">
                    <label class="form-label" for="login-semester">Semester</label>
                    <select class="form-select" id="login-semester" name="semester_id">
                        <?php foreach ($semesters as $s): ?>
                            <option value="<?= (int) $s['id'] ?>"
                                    data-tahun="<?= (int) $s['tahun_id'] ?>"
                                    data-aktif="<?= (int) $s['is_aktif'] ?>"
                                    <?= (int) $s['id'] === (int) $pilihSem ? 'selected' : '' ?>>
                                <?= e($s['semester']) ?><?= $s['is_aktif'] ? ' (aktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-4"></div>
        <?php endif; ?>

        <button type="submit" class="btn btn-gradient w-100 py-2">Masuk <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i></button>
        <p class="text-center text-muted small mt-3 mb-0">Sesi login bertahan hingga 30 hari di perangkat ini. Periode bisa diganti kapan saja dari bilah atas.</p>
    </form>
</div>
