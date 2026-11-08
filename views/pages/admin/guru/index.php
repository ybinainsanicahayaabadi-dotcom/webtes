<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="btn-tab-guru" data-bs-toggle="tab" data-bs-target="#tab-guru" type="button" role="tab">
            <i class="fa-solid fa-chalkboard-user me-1"></i>Data Guru
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="btn-tab-penempatan" data-bs-toggle="tab" data-bs-target="#tab-penempatan" type="button" role="tab">
            <i class="fa-solid fa-location-dot me-1"></i>Penempatan per Tahun
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- ================= DATA GURU ================= -->
    <div class="tab-pane fade show active" id="tab-guru" role="tabpanel">
        <div class="card panel">
            <div class="card-body">
                <div class="card-head-bar">
                    <h2 class="panel-title"><i class="fa-solid fa-chalkboard-user me-2"></i>Data Guru & Tenaga Kependidikan</h2>
                    <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-guru"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
                </div>
                <div class="filter-bar mb-3">
                    <select id="filter-tahun" class="form-select form-select-sm" aria-label="Tahun ajaran penempatan">
                        <?php foreach ($tahunList as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tahunAktif ? 'selected' : '' ?>>Penempatan <?= e($t['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filter-unit" class="form-select form-select-sm" aria-label="Unit">
                        <option value="">Semua unit</option>
                        <option value="none">Belum ditempatkan</option>
                        <?php foreach ($unitList as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filter-status" class="form-select form-select-sm" aria-label="Status">
                        <option value="">Semua status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <table id="tabel-guru" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="min-tablet">Foto</th>
                            <th class="all">Nama</th>
                            <th class="min-tablet">Unit</th>
                            <th class="min-desktop">Jabatan</th>
                            <th class="min-desktop">Kontak</th>
                            <th class="min-tablet">Status</th>
                            <th class="all text-end">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= PENEMPATAN ================= -->
    <div class="tab-pane fade" id="tab-penempatan" role="tabpanel">
        <div class="card panel">
            <div class="card-body">
                <div class="card-head-bar">
                    <h2 class="panel-title"><i class="fa-solid fa-location-dot me-2"></i>Penempatan Guru per Tahun Ajaran</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-salin-penempatan"><i class="fa-regular fa-copy me-1"></i>Salin dari tahun lain</button>
                        <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-penempatan"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
                    </div>
                </div>
                <div class="filter-bar mb-3">
                    <select id="p-filter-tahun" class="form-select form-select-sm" aria-label="Tahun ajaran">
                        <?php foreach ($tahunList as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tahunAktif ? 'selected' : '' ?>><?= e($t['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="p-filter-unit" class="form-select form-select-sm" aria-label="Unit">
                        <option value="">Semua unit</option>
                        <?php foreach ($unitList as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <table id="tabel-penempatan" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="all">Guru</th>
                            <th class="min-tablet">Tahun</th>
                            <th class="all">Unit</th>
                            <th class="min-desktop">Jabatan</th>
                            <th class="min-desktop">Pangkat/Gol.</th>
                            <th class="min-tablet">Status</th>
                            <th class="all text-end">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-light border mt-3 small mb-0">
    <i class="fa-solid fa-circle-info me-1"></i>
    Satu guru hanya boleh ditempatkan di satu unit pada satu tahun ajaran. Akun kepala sekolah, wakil, dan guru otomatis mengikuti
    penempatan pada tahun ajaran aktif. Penempatan yang sudah dipakai (wali kelas / penugasan mengajar) tidak bisa dipindah atau dihapus.
</div>

<?php
UI::modalForm('modal-guru', 'form-guru', 'Guru', 'admin/guru/_fields_guru', 'modal-lg');
UI::modalForm('modal-penempatan', 'form-penempatan', 'Penempatan', 'admin/guru/_fields_penempatan', '', [
    'guruList' => $guruList, 'tahunList' => $tahunList, 'unitList' => $unitList, 'jabatanList' => $jabatanList,
]);
?>

<div class="modal fade" id="modal-salin-p" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="form-salin-p" method="post" novalidate autocomplete="off">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Salin Penempatan Antar Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Hanya guru aktif yang disalin, dengan unit, jabatan, dan pangkat yang sama. Guru yang sudah punya penempatan di tahun tujuan dan kepala sekolah yang posisinya sudah terisi dilewati. Setelah itu, ubah manual guru yang pindah unit.</p>
                <div class="mb-3">
                    <label class="form-label" for="sp-dari">Tahun asal <span class="text-danger">*</span></label>
                    <select class="form-select" id="sp-dari" name="dari_tahun_id" required>
                        <option value="">- Pilih -</option>
                        <?php foreach ($tahunList as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sp-ke">Tahun tujuan <span class="text-danger">*</span></label>
                    <select class="form-select" id="sp-ke" name="ke_tahun_id" required>
                        <option value="">- Pilih -</option>
                        <?php foreach ($tahunList as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sp-unit">Unit</label>
                    <select class="form-select" id="sp-unit" name="unit_id">
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

<div class="modal fade" id="modal-riwayat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="riwayat-judul">Riwayat Penempatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Tahun</th><th>Unit</th><th>Jabatan</th><th>Status</th></tr></thead>
                        <tbody id="riwayat-isi"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
