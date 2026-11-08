<div class="row g-3">
    <div class="col-xl-5">
        <div class="card panel h-100">
            <div class="card-body">
                <div class="card-head-bar">
                    <h2 class="panel-title"><i class="fa-regular fa-calendar me-2"></i>Tahun Ajaran</h2>
                    <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-tahun"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
                </div>
                <table id="tabel-tahun" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="all">Tahun ajaran</th>
                            <th class="min-tablet">Mulai</th>
                            <th class="min-tablet">Selesai</th>
                            <th class="min-desktop text-center">Semester</th>
                            <th class="min-tablet">Status</th>
                            <th class="all text-end">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card panel h-100">
            <div class="card-body">
                <div class="card-head-bar">
                    <h2 class="panel-title"><i class="fa-solid fa-list-check me-2"></i>Semester</h2>
                    <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-semester"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
                </div>
                <table id="tabel-semester" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="all">Tahun ajaran</th>
                            <th class="all">Semester</th>
                            <th class="min-tablet">Mulai</th>
                            <th class="min-tablet">Selesai</th>
                            <th class="min-tablet">Status</th>
                            <th class="min-desktop">Kunci</th>
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
    Hanya satu semester yang aktif; mengaktifkan semester otomatis mengaktifkan tahun ajarannya.
    Semester terkunci tidak bisa diubah sampai kuncinya dibuka. Data yang sudah dipakai modul lain tidak bisa dihapus.
</div>

<?php
UI::modalForm('modal-tahun', 'form-tahun', 'Tahun Ajaran', 'admin/periode/_fields_tahun');
UI::modalForm('modal-semester', 'form-semester', 'Semester', 'admin/periode/_fields_semester', '', ['tahunList' => $tahunList]);
?>
