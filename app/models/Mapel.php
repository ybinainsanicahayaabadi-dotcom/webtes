<?php
/**
 * Model katalog mata pelajaran per unit (tabel `mapel`). Mapel yang dibuka tiap semester ada di MapelSemester.
 */
final class Mapel
{
    public static function find(int $id): ?array
    {
        return Database::row('SELECT * FROM mapel WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    /** Untuk pilihan di formulir (disaring per unit di sisi klien) */
    public static function opsi(): array
    {
        return Database::all(
            "SELECT m.id, m.unit_id, m.kode, m.nama FROM mapel m
             JOIN unit_sekolah u ON u.id = m.unit_id AND u.deleted_at IS NULL AND u.is_aktif = 1
             WHERE m.deleted_at IS NULL ORDER BY m.nama"
        );
    }

    public static function create(array $d): int
    {
        return Database::insert('INSERT INTO mapel (unit_id, kode, nama) VALUES (?,?,?)', [(int) $d['unit_id'], $d['kode'], $d['nama']]);
    }

    public static function update(int $id, array $d): void
    {
        Database::exec('UPDATE mapel SET unit_id = ?, kode = ?, nama = ? WHERE id = ?', [(int) $d['unit_id'], $d['kode'], $d['nama'], $id]);
    }

    public static function delete(int $id): void
    {
        Database::exec('DELETE FROM mapel WHERE id = ?', [$id]);
    }

    public static function dependencies(int $id): array
    {
        $n = (int) Database::value('SELECT COUNT(*) FROM mapel_semester WHERE mapel_id = ?', [$id]);
        return $n > 0 ? ['Mapel semester' => $n] : [];
    }
}
