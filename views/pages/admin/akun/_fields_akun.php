<div class="form-section">Akun</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="akun-username">Username <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="akun-username" name="username" maxlength="40" autocapitalize="none" required>
        <div class="form-text">Huruf kecil, angka, titik, - dan _.</div>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="akun-role">Peran <span class="text-danger">*</span></label>
        <select class="form-select" id="akun-role" name="role" required>
            <option value="guru">Guru</option>
            <option value="wakil_kepala">Wakil Kepala Sekolah</option>
            <option value="kepala_sekolah">Kepala Sekolah</option>
            <option value="ketua_bidang">Ketua Bidang Akademik</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    <div class="col-12 mb-3" id="akun-guru-wrap">
        <label class="form-label" for="akun-guru">Data guru <span class="text-danger">*</span></label>
        <select class="form-select select2" id="akun-guru" name="guru_id">
            <option value="">- Pilih data guru -</option>
            <?php foreach ($guruList as $g): ?>
                <option value="<?= (int) $g['id'] ?>" data-nama="<?= e($g['nama']) ?>"><?= e($g['nama']) ?><?= $g['niy'] ? ' (' . e($g['niy']) . ')' : '' ?></option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Unit akun otomatis mengikuti penempatan guru pada tahun ajaran aktif.</div>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="akun-nama">Nama lengkap <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="akun-nama" name="nama_lengkap" maxlength="150" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="akun-email">Email</label>
        <input type="email" class="form-control" id="akun-email" name="email" maxlength="120">
    </div>
</div>

<div class="form-section">Password</div>
<div class="mb-3">
    <label class="form-label" for="akun-password">Password <span class="text-danger" id="akun-pw-wajib">*</span></label>
    <div class="input-group">
        <input type="password" class="form-control" id="akun-password" name="password" autocomplete="new-password"
               data-placeholder-baru="Minimal <?= (int) $passwordMin ?> karakter, huruf dan angka"
               data-placeholder-ubah="Kosongkan jika tidak diubah">
        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#akun-password" aria-label="Tampilkan password"><i class="fa-regular fa-eye"></i></button>
        <button class="btn btn-outline-secondary" type="button" id="akun-generate" title="Buat password acak"><i class="fa-solid fa-wand-magic-sparkles"></i> Acak</button>
    </div>
</div>
<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="akun-wajib-ganti" name="must_change_pw" value="1" checked>
    <label class="form-check-label" for="akun-wajib-ganti">Wajib ganti password saat login berikutnya</label>
</div>

<div class="form-section">Tanda Tangan Digital (PNG)</div>
<div class="row align-items-center">
    <div class="col-auto mb-3">
        <div class="ttd-box" style="min-height:96px;min-width:170px">
            <img id="akun-ttd-preview" alt="Pratinjau tanda tangan" hidden>
            <span id="akun-ttd-kosong" class="text-muted small">Belum ada</span>
        </div>
    </div>
    <div class="col mb-3">
        <label class="form-label" for="akun-ttd">Unggah PNG (latar transparan, maks 1 MB)</label>
        <input type="file" class="form-control" id="akun-ttd" name="ttd" accept="image/png">
        <div class="form-text">Wajib untuk kepala sekolah, wakil kepala, dan ketua bidang agar dokumen penilaian dapat ditandatangani. Pengguna juga bisa mengunggah sendiri di menu Profil.</div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="akun-hapus-ttd" name="hapus_ttd" value="1">
            <label class="form-check-label" for="akun-hapus-ttd">Hapus tanda tangan saat ini</label>
        </div>
    </div>
</div>

<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" role="switch" id="akun-aktif" name="is_aktif" value="1" checked>
    <label class="form-check-label" for="akun-aktif">Akun aktif</label>
</div>
