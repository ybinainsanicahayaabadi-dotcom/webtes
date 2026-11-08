<div class="mb-3">
    <label class="form-label" for="sem-tahun">Tahun ajaran <span class="text-danger">*</span></label>
    <select class="form-select select2" id="sem-tahun" name="tahun_ajaran_id" required>
        <option value="">- Pilih tahun ajaran -</option>
        <?php foreach ($tahunList as $t): ?>
            <option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="sem-nama">Semester <span class="text-danger">*</span></label>
    <select class="form-select" id="sem-nama" name="nama" required>
        <option value="Ganjil">Ganjil</option>
        <option value="Genap">Genap</option>
    </select>
</div>
<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label" for="sem-mulai">Tanggal mulai</label>
        <input type="date" class="form-control" id="sem-mulai" name="tgl_mulai">
    </div>
    <div class="col-6 mb-3">
        <label class="form-label" for="sem-selesai">Tanggal selesai</label>
        <input type="date" class="form-control" id="sem-selesai" name="tgl_selesai">
    </div>
</div>
<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" role="switch" id="sem-aktif" name="is_aktif" value="1">
    <label class="form-check-label" for="sem-aktif">Jadikan semester aktif</label>
</div>
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" role="switch" id="sem-kunci" name="is_terkunci" value="1">
    <label class="form-check-label" for="sem-kunci">Kunci semester (data tidak bisa diubah)</label>
</div>
