<?php $ttdUrl = $authUser['tanda_tangan'] ? url('/berkas/ttd/' . (int) $authUser['id']) . '?v=' . substr(md5($authUser['tanda_tangan']), 0, 8) : null; ?>

<?php if ((int) $authUser['must_change_pw'] === 1): ?>
    <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Ganti password Anda dulu untuk membuka menu lain.</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card panel h-100">
            <div class="card-body">
                <h2 class="panel-title"><i class="fa-regular fa-id-card me-2"></i>Data Akun</h2>
                <form method="post" action="<?= e(url('/profil')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= e($authUser['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Peran</label>
                        <input type="text" class="form-control" value="<?= e(role_label($authUser['role'])) ?><?= !empty($authUser['unit_nama']) ? ' - ' . e($authUser['unit_nama']) : '' ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="nama_lengkap">Nama lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" maxlength="150" required value="<?= e($authUser['nama_lengkap']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="120" value="<?= e($authUser['email'] ?? '') ?>">
                    </div>
                    <button class="btn btn-gradient" type="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card panel h-100">
            <div class="card-body">
                <h2 class="panel-title"><i class="fa-solid fa-key me-2"></i>Ganti Password</h2>
                <form method="post" action="<?= e(url('/profil/password')) ?>" autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="password_lama">Password lama</label>
                        <input type="password" class="form-control" id="password_lama" name="password_lama" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_baru">Password baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru" required minlength="8" autocomplete="new-password">
                        <div class="form-text">Minimal 8 karakter, kombinasi huruf dan angka.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_konfirmasi">Ulangi password baru</label>
                        <input type="password" class="form-control" id="password_konfirmasi" name="password_konfirmasi" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-gradient" type="submit">Ganti password</button>
                    <p class="form-text mt-2 mb-0">Perangkat lain akan otomatis dikeluarkan.</p>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card panel">
            <div class="card-body">
                <h2 class="panel-title"><i class="fa-solid fa-signature me-2"></i>Tanda Tangan Digital</h2>
                <?php if ($wajibTtd && !$ttdUrl): ?>
                    <div class="alert alert-warning py-2 small">Peran Anda wajib mengunggah tanda tangan PNG agar dokumen penilaian dapat ditandatangani.</div>
                <?php endif; ?>
                <div class="row g-3 align-items-start">
                    <div class="col-md-5">
                        <div class="ttd-box" id="ttd-preview-box">
                            <?php if ($ttdUrl): ?>
                                <img src="<?= e($ttdUrl) ?>" alt="Tanda tangan" id="ttd-preview">
                            <?php else: ?>
                                <span class="text-muted small" id="ttd-empty">Belum ada tanda tangan</span>
                                <img src="" alt="Pratinjau" id="ttd-preview" hidden>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <form method="post" action="<?= e(url('/profil/tanda-tangan')) ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <label class="form-label" for="ttd">Unggah file PNG (latar transparan disarankan)</label>
                            <input class="form-control mb-2" type="file" id="ttd" name="ttd" accept="image/png" required>
                            <div class="form-text mb-3">Maksimal 1 MB, minimal 100x50 piksel. Gambar akan diproses ulang dan disimpan privat.</div>
                            <button class="btn btn-gradient" type="submit">Simpan tanda tangan</button>
                        </form>
                        <?php if ($ttdUrl): ?>
                            <form method="post" action="<?= e(url('/profil/tanda-tangan/hapus')) ?>" class="mt-2" data-confirm="Hapus tanda tangan digital Anda?">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-danger btn-sm" type="submit"><i class="fa-regular fa-trash-can me-1"></i>Hapus</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
