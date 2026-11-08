<div class="card panel">
    <div class="card-body">
        <div class="card-head-bar">
            <h2 class="panel-title"><i class="fa-solid fa-users-gear me-2"></i>Akun Pengguna</h2>
            <button type="button" class="btn btn-gradient btn-sm" id="btn-tambah-akun"><i class="fa-solid fa-plus me-1"></i>Tambah</button>
        </div>
        <div class="filter-bar mb-3">
            <select id="filter-role" class="form-select form-select-sm" aria-label="Filter peran">
                <option value="">Semua peran</option>
                <option value="admin">Admin</option>
                <option value="ketua_bidang">Ketua Bidang Akademik</option>
                <option value="kepala_sekolah">Kepala Sekolah</option>
                <option value="wakil_kepala">Wakil Kepala Sekolah</option>
                <option value="guru">Guru</option>
            </select>
            <select id="filter-status" class="form-select form-select-sm" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
        <table id="tabel-akun" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th></th>
                    <th class="all">Pengguna</th>
                    <th class="min-tablet">Peran</th>
                    <th class="min-desktop">Unit</th>
                    <th class="min-tablet">Status</th>
                    <th class="min-desktop">Login terakhir</th>
                    <th class="min-desktop text-center">TTD</th>
                    <th class="all text-end">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="akun-config" hidden data-role-guru="<?= e(json_encode($roleGuru)) ?>"></div>

<div class="alert alert-light border mt-3 small mb-0">
    <i class="fa-solid fa-circle-info me-1"></i>
    Kepala sekolah, wakil kepala, dan guru harus dihubungkan ke data guru; unit mengikuti penempatan pada tahun ajaran aktif.
    Menonaktifkan atau mengubah password akan mengeluarkan pengguna dari semua perangkat. Akun yang dihapus disembunyikan, riwayat penilaian tetap tersimpan.
</div>

<?php UI::modalForm('modal-akun', 'form-akun', 'Akun', 'admin/akun/_fields_akun', 'modal-lg', ['guruList' => $guruList, 'passwordMin' => $passwordMin]); ?>
