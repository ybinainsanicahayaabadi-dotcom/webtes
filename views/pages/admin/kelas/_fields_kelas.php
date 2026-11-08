<div class="mb-3">
    <label class="form-label" for="kelas-tahun">Tahun ajaran <span class="text-danger">*</span></label>
    <select class="form-select select2" id="kelas-tahun" name="tahun_ajaran_id" required>
        <option value="">- Pilih tahun ajaran -</option>
        <?php foreach ($tahunList as $t): ?>
            <option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="kelas-unit">Unit sekolah <span class="text-danger">*</span></label>
    <select class="form-select select2" id="kelas-unit" name="unit_id" required>
        <option value="">- Pilih unit -</option>
        <?php foreach ($unitList as $u): ?>
            <option value="<?= (int) $u['id'] ?>" data-jenjang="<?= e($u['jenjang']) ?>"><?= e($u['nama']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="row">
    <div class="col-5 mb-3">
        <label class="form-label" for="kelas-tingkat">Tingkat <span class="text-danger">*</span></label>
        <select class="form-select" id="kelas-tingkat" name="tingkat" required>
            <option value="">- Pilih tingkat -</option>
        </select>
    </div>
    <div class="col-7 mb-3">
        <label class="form-label" for="kelas-nama">Nama kelas <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="kelas-nama" name="nama" maxlength="50" placeholder="Contoh: Kelas 1A" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="kelas-wali">Wali kelas</label>
    <select class="form-select select2" id="kelas-wali" name="wali_guru_id">
        <option value="">- Belum ditentukan -</option>
    </select>
    <div class="form-text">Daftar guru muncul setelah unit dan tahun ajaran dipilih.</div>
</div>
<div class="mb-3">
    <label class="form-label" for="kelas-siswa">Jumlah siswa</label>
    <input type="number" class="form-control" id="kelas-siswa" name="jumlah_siswa" min="0" max="200" style="max-width:160px">
</div>
