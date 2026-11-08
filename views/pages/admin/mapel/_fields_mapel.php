<div class="mb-3">
    <label class="form-label" for="mapel-unit">Unit sekolah <span class="text-danger">*</span></label>
    <select class="form-select select2" id="mapel-unit" name="unit_id" required>
        <option value="">- Pilih unit -</option>
        <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
    </select>
</div>
<div class="row">
    <div class="col-4 mb-3">
        <label class="form-label" for="mapel-kode">Kode</label>
        <input type="text" class="form-control" id="mapel-kode" name="kode" maxlength="20">
    </div>
    <div class="col-8 mb-3">
        <label class="form-label" for="mapel-nama">Nama mata pelajaran <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="mapel-nama" name="nama" maxlength="120" required>
    </div>
</div>
