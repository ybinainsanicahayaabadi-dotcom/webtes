<div class="mb-3">
    <label class="form-label" for="p-guru">Guru <span class="text-danger">*</span></label>
    <select class="form-select select2" id="p-guru" name="guru_id" required>
        <option value="">- Pilih guru -</option>
        <?php foreach ($guruList as $g): ?>
            <option value="<?= (int) $g['id'] ?>"><?= e($g['nama']) ?><?= $g['niy'] ? ' (' . e($g['niy']) . ')' : '' ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label" for="p-tahun">Tahun ajaran <span class="text-danger">*</span></label>
        <select class="form-select" id="p-tahun" name="tahun_ajaran_id" required>
            <?php foreach ($tahunList as $t): ?>
                <option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 mb-3">
        <label class="form-label" for="p-status">Status</label>
        <select class="form-select" id="p-status" name="status">
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="p-unit">Unit sekolah <span class="text-danger">*</span></label>
    <select class="form-select select2" id="p-unit" name="unit_id" required>
        <option value="">- Pilih unit -</option>
        <?php foreach ($unitList as $u): ?>
            <option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="p-jabatan">Jabatan <span class="text-danger">*</span></label>
        <select class="form-select" id="p-jabatan" name="jabatan" required>
            <?php foreach ($jabatanList as $j): ?>
                <option value="<?= e($j) ?>" <?= $j === 'Guru' ? 'selected' : '' ?>><?= e($j) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="p-pangkat">Pangkat/golongan</label>
        <input type="text" class="form-control" id="p-pangkat" name="pangkat_golongan" maxlength="80" placeholder="Contoh: III/a">
    </div>
</div>
