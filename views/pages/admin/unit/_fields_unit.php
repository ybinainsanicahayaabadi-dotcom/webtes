<div class="form-section">Identitas</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-kode">Kode unit <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="unit-kode" name="kode" maxlength="20" placeholder="SDIT1" required>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label" for="unit-nama">Nama unit <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="unit-nama" name="nama" maxlength="120" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-jenjang">Jenjang <span class="text-danger">*</span></label>
        <select class="form-select" id="unit-jenjang" name="jenjang" required>
            <option value="TK">TK</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="YAYASAN">Yayasan</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-npsn">NPSN</label>
        <input type="text" class="form-control" id="unit-npsn" name="npsn" maxlength="20">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-urutan">Urutan tampil</label>
        <input type="number" class="form-control" id="unit-urutan" name="urutan" min="0" max="99" value="0">
    </div>
</div>

<div class="form-section">Alamat & Kontak</div>
<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label" for="unit-alamat">Alamat</label>
        <textarea class="form-control" id="unit-alamat" name="alamat" rows="2" maxlength="500"></textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="unit-kelurahan">Kelurahan/Desa</label>
        <input type="text" class="form-control" id="unit-kelurahan" name="kelurahan" maxlength="80">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="unit-kecamatan">Kecamatan</label>
        <input type="text" class="form-control" id="unit-kecamatan" name="kecamatan" maxlength="80">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="unit-kabkota">Kabupaten/Kota</label>
        <input type="text" class="form-control" id="unit-kabkota" name="kab_kota" maxlength="80">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="unit-provinsi">Provinsi</label>
        <input type="text" class="form-control" id="unit-provinsi" name="provinsi" maxlength="80">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-telepon">Telepon</label>
        <input type="text" class="form-control" id="unit-telepon" name="telepon" maxlength="40">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-email">Email</label>
        <input type="email" class="form-control" id="unit-email" name="email" maxlength="120">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="unit-website">Website</label>
        <input type="text" class="form-control" id="unit-website" name="website" maxlength="150" placeholder="https://">
    </div>
</div>

<div class="form-section">Logo</div>
<div class="row align-items-center">
    <div class="col-auto mb-3">
        <div class="logo-box">
            <img id="unit-logo-preview" alt="Pratinjau logo" hidden>
            <span id="unit-logo-kosong" class="text-muted small text-center px-2">Belum ada logo</span>
        </div>
    </div>
    <div class="col mb-3">
        <label class="form-label" for="unit-logo">Unggah logo (JPG/PNG/WebP, maks 2 MB)</label>
        <input type="file" class="form-control" id="unit-logo" name="logo" accept="image/png,image/jpeg,image/webp">
        <div class="form-text">Gambar diperkecil otomatis (maks 512 px) dan diproses ulang demi keamanan.</div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="unit-hapus-logo" name="hapus_logo" value="1">
            <label class="form-check-label" for="unit-hapus-logo">Hapus logo saat ini</label>
        </div>
    </div>
</div>

<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" role="switch" id="unit-aktif" name="is_aktif" value="1" checked>
    <label class="form-check-label" for="unit-aktif">Unit aktif</label>
</div>
