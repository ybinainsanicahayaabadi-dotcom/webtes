<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="pg-semester">Semester <span class="text-danger">*</span></label>
        <select class="form-select" id="pg-semester" name="semester_id" required>
            <?php foreach ($semesterList as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['label']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="pg-unit">Unit sekolah <span class="text-danger">*</span></label>
        <select class="form-select select2" id="pg-unit" name="unit_id" required>
            <option value="">- Pilih unit -</option>
            <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="pg-guru">Guru <span class="text-danger">*</span></label>
        <select class="form-select select2" id="pg-guru" name="guru_id" required>
            <option value="">- Pilih guru -</option>
        </select>
        <div class="form-text">Hanya guru yang ditempatkan di unit ini pada tahun ajaran semester tersebut.</div>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="pg-mapel">Mata pelajaran <span class="text-danger">*</span></label>
        <select class="form-select select2" id="pg-mapel" name="mapel_semester_id" required>
            <option value="">- Pilih mapel -</option>
        </select>
        <div class="form-text">Mapel yang sudah dibuka pada semester ini.</div>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="pg-kelas">Kelas <span class="text-danger">*</span></label>
        <select class="form-select select2" id="pg-kelas" name="kelas_id[]" multiple data-placeholder="Pilih satu atau beberapa kelas"></select>
        <div class="form-text">Saat menambah boleh memilih beberapa kelas sekaligus; saat mengubah hanya satu kelas.</div>
    </div>
</div>
