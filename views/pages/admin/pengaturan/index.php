<?php
/** Nilai tampil: input lama (bila validasi gagal) atau nilai tersimpan */
$val = static function (string $k) use ($old) {
    return array_key_exists($k, $old) ? (string) $old[$k] : (string) setting($k, '');
};
$err = static function (string $k) use ($errors) {
    return isset($errors[$k]) ? '<div class="field-error">' . e($errors[$k]) . '</div>' : '';
};
$cls = static function (string $k) use ($errors) {
    return isset($errors[$k]) ? ' is-invalid' : '';
};
?>
<?php if ($errors): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Periksa kembali isian yang ditandai merah.</div>
<?php endif; ?>

<form method="post" action="<?= e(url('/admin/pengaturan')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card panel mb-3">
                <div class="card-body">
                    <h2 class="panel-title"><i class="fa-regular fa-id-badge me-2"></i>Identitas Aplikasi</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="app_nama">Nama aplikasi *</label>
                            <input type="text" class="form-control<?= $cls('app_nama') ?>" id="app_nama" name="app_nama" maxlength="40" value="<?= e($val('app_nama')) ?>" required>
                            <?= $err('app_nama') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="yayasan_nama">Nama yayasan *</label>
                            <input type="text" class="form-control<?= $cls('yayasan_nama') ?>" id="yayasan_nama" name="yayasan_nama" maxlength="120" value="<?= e($val('yayasan_nama')) ?>" required>
                            <?= $err('yayasan_nama') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="app_kepanjangan">Kepanjangan aplikasi</label>
                        <input type="text" class="form-control<?= $cls('app_kepanjangan') ?>" id="app_kepanjangan" name="app_kepanjangan" maxlength="120" value="<?= e($val('app_kepanjangan')) ?>">
                        <?= $err('app_kepanjangan') ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="app_footer">Teks footer</label>
                        <input type="text" class="form-control<?= $cls('app_footer') ?>" id="app_footer" name="app_footer" maxlength="150" value="<?= e($val('app_footer')) ?>">
                        <?= $err('app_footer') ?>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="tema_default">Tema bawaan</label>
                        <select class="form-select<?= $cls('tema_default') ?>" id="tema_default" name="tema_default">
                            <option value="terang" <?= $val('tema_default') !== 'gelap' ? 'selected' : '' ?>>Terang</option>
                            <option value="gelap" <?= $val('tema_default') === 'gelap' ? 'selected' : '' ?>>Gelap</option>
                        </select>
                        <div class="form-text">Berlaku untuk pengguna yang belum memilih tema sendiri.</div>
                        <?= $err('tema_default') ?>
                    </div>
                </div>
            </div>

            <div class="card panel mb-3">
                <div class="card-body">
                    <h2 class="panel-title"><i class="fa-solid fa-shield-halved me-2"></i>Keamanan</h2>
                    <div class="mb-0">
                        <label class="form-label" for="password_min">Panjang minimal password *</label>
                        <input type="number" class="form-control<?= $cls('password_min') ?>" id="password_min" name="password_min" min="8" max="32" value="<?= e($val('password_min') ?: '8') ?>" required style="max-width:140px">
                        <div class="form-text">Antara 8 dan 32 karakter. Batas percobaan login dan masa sesi diatur di config/config.php.</div>
                        <?= $err('password_min') ?>
                    </div>
                </div>
            </div>

            <div class="card panel">
                <div class="card-body">
                    <h2 class="panel-title"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Unggahan & Penyimpanan</h2>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label" for="upload_maks_mb">Ukuran maksimal per file (MB) *</label>
                            <input type="number" class="form-control<?= $cls('upload_maks_mb') ?>" id="upload_maks_mb" name="upload_maks_mb" min="1" max="50" value="<?= e($val('upload_maks_mb') ?: '5') ?>" required>
                            <?= $err('upload_maks_mb') ?>
                        </div>
                        <div class="col-md-7 mb-3">
                            <label class="form-label" for="storage_driver">Penyimpanan bukti fisik</label>
                            <select class="form-select<?= $cls('storage_driver') ?>" id="storage_driver" name="storage_driver">
                                <option value="lokal" selected>Server lokal (folder storage)</option>
                                <option value="nextcloud" disabled>Nextcloud (tersedia pada Tahap 7)</option>
                            </select>
                            <?= $err('storage_driver') ?>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="upload_ekstensi">Ekstensi file yang diizinkan *</label>
                        <input type="text" class="form-control<?= $cls('upload_ekstensi') ?>" id="upload_ekstensi" name="upload_ekstensi" maxlength="200" value="<?= e($val('upload_ekstensi')) ?>" required>
                        <div class="form-text">Pisahkan dengan koma. Pilihan: pdf, jpg, jpeg, png, webp, doc, docx, xls, xlsx, ppt, pptx, txt, csv.</div>
                        <?= $err('upload_ekstensi') ?>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-gradient px-4"><i class="fa-regular fa-floppy-disk me-1"></i>Simpan pengaturan</button>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card panel">
                <div class="card-body">
                    <h2 class="panel-title"><i class="fa-solid fa-server me-2"></i>Info Server</h2>
                    <dl class="kv-list">
                        <?php foreach ($server as $k => $v): ?>
                            <dt><?= e($k) ?></dt>
                            <dd><?= e($v) ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</form>
