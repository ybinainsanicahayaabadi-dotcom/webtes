<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-katalog" type="button" role="tab"><i class="fa-solid fa-book me-1"></i>Katalog Mapel</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ms" type="button" role="tab"><i class="fa-solid fa-calendar-check me-1"></i>Mapel per Semester</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pg" type="button" role="tab"><i class="fa-solid fa-person-chalkboard me-1"></i>Penugasan Mengajar</button>
    </li>
</ul>

<div class="tab-content">
    <!-- ============ KATALOG ============ -->
    <div class="tab-pane fade show active" id="tab-katalog" role="tabpanel">
        <div class="card panel"><div class="card-body">
            <div class="card-head-bar">
                <h2 class="panel-title"><i class="fa-solid fa-book me-2"></i>Katalog Mata Pelajaran</h2>
                <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-mapel"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
            </div>
            <div class="filter-bar mb-3">
                <select id="m-filter-unit" class="form-select form-select-sm" aria-label="Filter unit">
                    <option value="">Semua unit</option>
                    <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <table id="tabel-mapel" class="table table-hover align-middle w-100">
                <thead><tr>
                    <th></th>
                    <th class="min-tablet">Unit</th>
                    <th class="min-desktop">Kode</th>
                    <th class="all">Mata pelajaran</th>
                    <th class="min-desktop text-center">Dibuka (semester)</th>
                    <th class="all text-end">Aksi</th>
                </tr></thead>
            </table>
        </div></div>
    </div>

    <!-- ============ MAPEL PER SEMESTER ============ -->
    <div class="tab-pane fade" id="tab-ms" role="tabpanel">
        <div class="card panel"><div class="card-body">
            <div class="card-head-bar">
                <h2 class="panel-title"><i class="fa-solid fa-calendar-check me-2"></i>Mapel yang Dibuka per Semester</h2>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-salin-ms"><i class="fa-regular fa-copy me-1"></i>Salin dari semester lain</button>
                    <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-ms"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
                </div>
            </div>
            <div class="filter-bar mb-3">
                <select id="ms-filter-semester" class="form-select form-select-sm" aria-label="Semester">
                    <?php foreach ($semesterList as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= (int) $s['id'] === (int) $semesterAktif ? 'selected' : '' ?>><?= e($s['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="ms-filter-unit" class="form-select form-select-sm" aria-label="Unit">
                    <option value="">Semua unit</option>
                    <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <table id="tabel-ms" class="table table-hover align-middle w-100">
                <thead><tr>
                    <th></th>
                    <th class="all">Mata pelajaran</th>
                    <th class="min-tablet">Unit</th>
                    <th class="min-tablet text-center">Tingkat</th>
                    <th class="min-desktop text-center">Jam/minggu</th>
                    <th class="min-desktop text-center">Penugasan</th>
                    <th class="all text-end">Aksi</th>
                </tr></thead>
            </table>
        </div></div>
    </div>

    <!-- ============ PENUGASAN ============ -->
    <div class="tab-pane fade" id="tab-pg" role="tabpanel">
        <div class="card panel"><div class="card-body">
            <div class="card-head-bar">
                <h2 class="panel-title"><i class="fa-solid fa-person-chalkboard me-2"></i>Penugasan Mengajar</h2>
                <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-pg"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
            </div>
            <div class="filter-bar mb-3">
                <select id="pg-filter-semester" class="form-select form-select-sm" aria-label="Semester">
                    <?php foreach ($semesterList as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= (int) $s['id'] === (int) $semesterAktif ? 'selected' : '' ?>><?= e($s['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="pg-filter-unit" class="form-select form-select-sm" aria-label="Unit">
                    <option value="">Semua unit</option>
                    <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <table id="tabel-pg" class="table table-hover align-middle w-100">
                <thead><tr>
                    <th></th>
                    <th class="all">Guru</th>
                    <th class="all">Kelas</th>
                    <th class="min-tablet">Mapel</th>
                    <th class="min-desktop">Unit</th>
                    <th class="min-desktop text-center">Jam/minggu</th>
                    <th class="all text-end">Aksi</th>
                </tr></thead>
            </table>
        </div></div>
    </div>
</div>

<div class="alert alert-light border mt-3 small mb-0">
    <i class="fa-solid fa-circle-info me-1"></i>
    Alur: <strong>1)</strong> daftarkan mapel di Katalog, <strong>2)</strong> buka mapel pada semester (tiap semester bisa berganti),
    <strong>3)</strong> tugaskan guru ke kelas. Guru harus sudah ditempatkan di unit pada tahun ajaran terkait, dan kelas sudah dibuat.
    Semester terkunci tidak bisa diubah.
</div>

<div id="mapel-config" hidden
     data-mapel="<?= e(json_encode($mapelList)) ?>"
     data-tingkat="<?= e(json_encode($tingkatMap)) ?>"></div>

<?php
UI::modalForm('modal-mapel', 'form-mapel', 'Mapel', 'admin/mapel/_fields_mapel', '', ['unitList' => $unitList]);
UI::modalForm('modal-ms', 'form-ms', 'Mapel Semester', 'admin/mapel/_fields_ms', '', ['unitList' => $unitList, 'semesterList' => $semesterList]);
UI::modalForm('modal-pg', 'form-pg', 'Penugasan', 'admin/mapel/_fields_pg', 'modal-lg', ['unitList' => $unitList, 'semesterList' => $semesterList]);
?>

<div class="modal fade" id="modal-salin-ms" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="form-salin-ms" method="post" novalidate autocomplete="off">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Salin Mapel Antar Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Mapel yang sudah ada di semester tujuan dilewati. Penugasan hanya disalin bila kelas bernama sama ada di tahun ajaran tujuan dan gurunya masih ditempatkan di unit yang sama.</p>
                <div class="mb-3">
                    <label class="form-label" for="sms-dari">Semester asal <span class="text-danger">*</span></label>
                    <select class="form-select" id="sms-dari" name="dari_semester_id" required>
                        <option value="">- Pilih -</option>
                        <?php foreach ($semesterList as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['label']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sms-ke">Semester tujuan <span class="text-danger">*</span></label>
                    <select class="form-select" id="sms-ke" name="ke_semester_id" required>
                        <option value="">- Pilih -</option>
                        <?php foreach ($semesterList as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['label']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sms-unit">Unit</label>
                    <select class="form-select" id="sms-unit" name="unit_id">
                        <option value="">Semua unit</option>
                        <?php foreach ($unitList as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="sms-tugas" name="salin_penugasan" value="1">
                    <label class="form-check-label" for="sms-tugas">Salin juga penugasan mengajar</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-gradient"><i class="fa-regular fa-copy me-1"></i>Salin</button>
            </div>
        </form>
    </div>
</div>
