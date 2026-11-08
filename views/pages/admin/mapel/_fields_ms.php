<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label" for="ms-semester">Semester <span class="text-danger">*</span></label>
        <select class="form-select" id="ms-semester" name="semester_id" required>
            <?php foreach ($semesterList as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['label']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="ms-unit">Unit sekolah <span class="text-danger">*</span></label>
        <select class="form-select select2" id="ms-unit" name="unit_id" required>
            <option value="">- Pilih unit -</option>
            <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>" data-jenjang="<?= e($u['jenjang']) ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="ms-mapel">Mata pelajaran <span class="text-danger">*</span></label>
        <select class="form-select select2" id="ms-mapel" name="mapel_id" required>
            <option value="">- Pilih unit dulu -</option>
        </select>
    </div>
    <div class="col-7 mb-3">
        <label class="form-label" for="ms-tingkat">Tingkat</label>
        <select class="form-select" id="ms-tingkat" name="tingkat">
            <option value="">Semua tingkat</option>
        </select>
        <div class="form-text">Isi bila mapel hanya untuk tingkat tertentu.</div>
    </div>
    <div class="col-5 mb-3">
        <label class="form-label" for="ms-jam">Jam/minggu</label>
        <input type="number" class="form-control" id="ms-jam" name="jam_per_minggu" min="1" max="60">
    </div>
</div>
