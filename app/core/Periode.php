<?php
/**
 * Filter periode global (tahun ajaran + semester) yang tersimpan di sesi.
 */
final class Periode
{
    private static ?array $cur = null;

    private const SELECT = 'SELECT s.id, s.nama AS semester, s.is_aktif, s.is_terkunci,
        t.id AS tahun_id, t.nama AS tahun
        FROM semester s JOIN tahun_ajaran t ON t.id = s.tahun_ajaran_id';

    public static function semesters(): array
    {
        try {
            return Database::all(self::SELECT . ' ORDER BY t.nama DESC, s.nama ASC');
        } catch (Throwable $e) {
            return [];
        }
    }

    /** @return array|null semester terpilih (atau yang aktif) */
    public static function current(): ?array
    {
        if (self::$cur !== null) {
            return self::$cur ?: null;
        }
        $row = null;
        try {
            if (!empty($_SESSION['semester_id'])) {
                $row = Database::row(self::SELECT . ' WHERE s.id = ?', [(int) $_SESSION['semester_id']]);
            }
            if (!$row) {
                $row = Database::row(self::SELECT . ' WHERE s.is_aktif = 1 ORDER BY s.id DESC LIMIT 1')
                    ?: Database::row(self::SELECT . ' ORDER BY t.nama DESC, s.nama ASC LIMIT 1');
            }
        } catch (Throwable $e) {
            $row = null;
        }
        self::$cur = $row ?: [];
        return $row;
    }

    public static function setSemester(int $id): bool
    {
        $ok = Database::value('SELECT COUNT(*) FROM semester WHERE id = ?', [$id]);
        if ($ok) {
            $_SESSION['semester_id'] = $id;
            self::$cur = null;
            return true;
        }
        return false;
    }

    public static function isLocked(): bool
    {
        $c = self::current();
        return $c ? (bool) $c['is_terkunci'] : false;
    }
}
