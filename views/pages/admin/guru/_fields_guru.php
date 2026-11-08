<div class="form-section">Data Pribadi</div>
<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label" for="guru-nama">Nama lengkap (beserta gelar) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="guru-nama" name="nama" maxlength="150" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-jk">Jenis kelamin</label>
        <select class="form-select" id="guru-jk" name="jenis_kelamin">
            <option value="">-</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-tempat">Tempat lahir</label>
        <input type="text" class="form-control" id="guru-tempat" name="tempat_lahir" maxlength="80">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-tgl">Tanggal lahir</label>
        <input type="date" class="form-control" id="guru-tgl" name="tanggal_lahir">
    </div>
</div>

<div class="form-section">Kepegawaian</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-niy">NIY</label>
        <input type="text" class="form-control" id="guru-niy" name="niy" maxlength="30">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-nuptk">NUPTK</label>
        <input type="text" class="form-control" id="guru-nuptk" name="nuptk" maxlength="30">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-tmt">TMT (mulai bertugas)</label>
        <input type="date" class="form-control" id="guru-tmt" name="tmt">
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label" for="guru-pend">Pendidikan terakhir / spesialisasi</label>
        <input type="text" class="form-control" id="guru-pend" name="pendidikan_terakhir" maxlength="100" placeholder="Contoh: S1 Pendidikan Matematika">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="guru-status">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="guru-status" name="status" required>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>
    </div>
</div>

<div class="form-section">Kontak</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="guru-email">Email</label>
        <input type="email" class="form-control" id="guru-email" name="email" maxlength="120">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="guru-telp">Telepon</label>
        <input type="text" class="form-control" id="guru-telp" name="telepon" maxlength="40">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="guru-alamat">Alamat</label>
        <textarea class="form-control" id="guru-alamat" name="alamat" rows="2" maxlength="500"></textarea>
    </div>
</div>

<div class="form-section">Foto</div>
<div class="row align-items-center">
    <div class="col-auto mb-3">
        <div class="foto-box">
            <img id="guru-foto-preview" alt="Pratinjau foto" hidden>
            <i id="guru-foto-kosong" class="fa-regular fa-user fa-2x text-muted"></i>
        </div>
    </div>
    <div class="col mb-3">
        <label class="form-label" for="guru-foto">Unggah foto (JPG/PNG/WebP, maks 3 MB)</label>
        <input type="file" class="form-control" id="guru-foto" name="foto" accept="image/png,image/jpeg,image/webp">
        <div class="form-text">Foto diperkecil otomatis dan disimpan privat (hanya dapat dilihat pengguna yang berhak).</div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="guru-hapus-foto" name="hapus_foto" value="1">
            <label class="form-check-label" for="guru-hapus-foto">Hapus foto saat ini</label>
        </div>
    </div>
</div>
