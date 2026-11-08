<div class="card panel">
    <div class="card-body">
        <div class="card-head-bar">
            <h2 class="panel-title"><i class="fa-solid fa-door-open me-2"></i>Daftar Kelas</h2>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-salin-kelas"><i class="fa-regular fa-copy me-1"></i>Salin dari tahun lain</button>
                <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-kelas"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
            </div>
        </div>

        <div class="filter-bar mb-3">
            <select id="filter-tahun" class="form-select form-select-sm" aria-label="Filter tahun ajaran">
                <option value="">Semua tahun ajaran</option>
                <?php foreach ($tahunList as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tahunAktif ? 'selected' : '' ?>><?= e($t['nama']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filter-unit" class="form-select form-select-sm" aria-label="Filter unit">
                <option value="">Semua unit</option>
                <?php foreach ($unitList as $u): ?>
                    <option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <table id="tabel-kelas" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th></th>
                    <th class="min-tablet">Tahun ajaran</th>
                    <th class="min-tablet">Unit</th>
                    <th class="min-desktop text-center">Tingkat</th>
                    <th class="all">Kelas</th>
                    <th class="min-desktop">Wali kelas</th>
                    <th class="min-desktop text-center">Siswa</th>
                    <th class="all text-end">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="kelas-config" hidden data-tingkat="<?= e(json_encode($tingkatMap)) ?>"></div>

<div class="alert alert-light border mt-3 small mb-0">
    <i class="fa-solid fa-circle-info me-1"></i>
    Kelas berlaku per tahun ajaran. Wali kelas dipilih dari guru yang ditempatkan di unit tersebut pada tahun ajaran yang sama
    (penempatan guru diatur di menu Guru).
</div>

<?php UI::modalForm('modal-kelas', 'form-kelas', 'Kelas', 'admin/kelas/_fields_kelas', '', ['unitList' => $unitList, 'tahunList' => $tahunList]); ?>

<div class="modal fade" id="modal-salin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="form-salin" method="post" novalidate autocomplete="off">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Salin Kelas Antar Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Nama, tingkat, dan unit disalin. Wali kelas dan jumlah siswa dikosongkan. Kelas yang namanya sudah ada di tahun tujuan dilewati.</p>
                <div class="mb-3">
                    <label class="form-label" for="salin-dari">Tahun asal <span class="text-danger">*</span></label>
                    <select class="form-select" id="salin-dari" name="dari_tahun_id" required>
                        <option value="">- Pilih -</option>
                        <?php foreach ($tahunList as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="salin-ke">Tahun tujuan <span class="text-danger">*</span></label>
                    <select class="form-select" id="salin-ke" name="ke_tahun_id" required>
                        <option value="">- Pilih -</option>
                        <?php foreach ($tahunList as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="salin-unit">Unit</label>
                    <select class="form-select" id="salin-unit" name="unit_id">
                        <option value="">Semua unit</option>
                        <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-gradient"><i class="fa-regular fa-copy me-1"></i>Salin</button>
            </div>
        </form>
    </div>
</div>
