<?php
/**
 * Hak akses berbasis peran. Matriks ada di config/permissions.php.
 */
final class Rbac
{
    private static ?array $matrix = null;

    private static function matrix(): array
    {
        if (self::$matrix === null) {
            self::$matrix = require ROOT_PATH . '/config/permissions.php';
        }
        return self::$matrix;
    }

    public static function can(string $modul, string $aksi, ?string $role = null): bool
    {
        $role = $role ?? (Auth::user()['role'] ?? '');
        $m = self::matrix()[$role] ?? null;
        if ($m === null) {
            return false;
        }
        if (isset($m['*']) && (in_array('*', $m['*'], true) || in_array($aksi, $m['*'], true))) {
            return true;
        }
        return isset($m[$modul]) && in_array($aksi, $m[$modul], true);
    }

    /** NULL = semua unit (admin, ketua bidang); angka = hanya unit itu */
    public static function unitScope(): ?int
    {
        $u = Auth::user();
        if (!$u || in_array($u['role'], ['admin', 'ketua_bidang'], true)) {
            return null;
        }
        return $u['unit_id'] !== null ? (int) $u['unit_id'] : 0;
    }

    public static function canAccessUnit(int $unitId): bool
    {
        $scope = self::unitScope();
        return $scope === null || $scope === $unitId;
    }
}
