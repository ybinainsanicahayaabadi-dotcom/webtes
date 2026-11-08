<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $periode = Periode::current();
        $taId = (int) ($periode['tahun_id'] ?? 0);
        $scope = Rbac::unitScope();   // null = semua unit

        $sql = "SELECT u.id, u.kode, u.nama, u.jenjang, u.logo,
                    (SELECT COUNT(*) FROM guru_penempatan gp WHERE gp.unit_id = u.id AND gp.tahun_ajaran_id = ? AND gp.status = 'aktif') AS jml_guru,
                    (SELECT COUNT(*) FROM kelas k WHERE k.unit_id = u.id AND k.tahun_ajaran_id = ? AND k.deleted_at IS NULL) AS jml_kelas
                FROM unit_sekolah u
                WHERE u.deleted_at IS NULL AND u.is_aktif = 1 AND u.jenjang <> 'YAYASAN'";
        $params = [$taId, $taId];
        if ($scope !== null) {
            $sql .= ' AND u.id = ?';
            $params[] = $scope;
        }
        $units = Database::all($sql . ' ORDER BY u.urutan, u.id', $params);

        $ringkas = [
            'unit'  => count($units),
            'guru'  => array_sum(array_column($units, 'jml_guru')),
            'kelas' => array_sum(array_column($units, 'jml_kelas')),
        ];

        $jam = (int) date('G');
        $salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

        $this->render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'periode'   => $periode,
            'units'     => $units,
            'ringkas'   => $ringkas,
            'salam'     => $salam,
        ]);
    }
}
