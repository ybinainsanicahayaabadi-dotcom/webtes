<div class="card panel">
    <div class="card-body">
        <div class="card-head-bar">
            <h2 class="panel-title"><i class="fa-solid fa-school me-2"></i>Daftar Unit Sekolah</h2>
            <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-unit"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
        </div>
        <table id="tabel-unit" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th></th>
                    <th class="min-tablet">Logo</th>
                    <th class="all">Unit</th>
                    <th class="min-tablet">Jenjang</th>
                    <th class="min-desktop">Kab/Kota</th>
                    <th class="min-desktop">Telepon</th>
                    <th class="min-desktop text-center">Urutan</th>
                    <th class="min-tablet">Status</th>
                    <th class="all text-end">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="alert alert-light border mt-3 small mb-0">
    <i class="fa-solid fa-circle-info me-1"></i>
    Entri berjenjang <strong>YAYASAN</strong> dipakai untuk logo/profil yayasan (hanya satu). Unit yang sudah memiliki kelas, guru, akun, atau
    penilaian tidak bisa dihapus; nonaktifkan saja bila tidak dipakai lagi.
</div>

<?php UI::modalForm('modal-unit', 'form-unit', 'Unit Sekolah', 'admin/unit/_fields_unit', 'modal-lg'); ?>
