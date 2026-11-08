<?php
/**
 * Autentikasi: login, sesi 30 hari, token "ingat saya" (selector + validator ter-hash),
 * pembatasan percobaan login, dan manajemen perangkat.
 */
final class Auth
{
    private const COOKIE_REMEMBER = 'SISLIA_REM';

    private static ?array $user = null;
    private static bool $resolved = false;

    // ---------------------------------------------------------------- pengguna aktif

    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;

        if (!empty($_SESSION['uid'])) {
            $ua = hash('sha256', user_agent());
            if (($_SESSION['ua'] ?? '') === $ua) {
                self::$user = self::loadUser((int) $_SESSION['uid']);
            }
            if (self::$user === null) {
                self::clearSession();
            }
        }
        if (self::$user === null && !empty($_COOKIE[self::COOKIE_REMEMBER])) {
            self::tryRemember();
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): int
    {
        return (int) (self::user()['id'] ?? 0);
    }

    public static function hasRole(string ...$roles): bool
    {
        $u = self::user();
        return $u !== null && in_array($u['role'], $roles, true);
    }

    private static function loadUser(int $id): ?array
    {
        return Database::row(
            'SELECT u.*, s.nama AS unit_nama, s.logo AS unit_logo
             FROM users u LEFT JOIN unit_sekolah s ON s.id = u.unit_id
             WHERE u.id = ? AND u.is_aktif = 1 AND u.deleted_at IS NULL',
            [$id]
        );
    }

    public static function hashPassword(string $plain): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        return password_hash($plain, $algo);
    }

    // ---------------------------------------------------------------- login

    /** @return array{0:bool,1:string} [berhasil, pesan galat] */
    public static function attempt(string $username, string $password): array
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            return [false, 'Username dan password wajib diisi.'];
        }
        if (self::terkunci($username)) {
            return [false, 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . LOGIN_KUNCI_MENIT . ' menit.'];
        }

        $u = Database::row(
            'SELECT * FROM users WHERE username = ? AND is_aktif = 1 AND deleted_at IS NULL LIMIT 1',
            [$username]
        );
        if ($u) {
            $ok = password_verify($password, $u['password_hash']);
        } else {
            password_hash($password, PASSWORD_DEFAULT); // samakan waktu respons
            $ok = false;
        }

        Database::exec(
            'INSERT INTO login_attempt (username, ip_address, berhasil) VALUES (?,?,?)',
            [substr($username, 0, 60), client_ip(), $ok ? 1 : 0]
        );
        if (random_int(1, 50) === 1) {
            Database::exec('DELETE FROM login_attempt WHERE created_at < (NOW() - INTERVAL 2 DAY)');
        }
        if (!$ok) {
            return [false, 'Username atau password salah.'];
        }

        if (password_needs_rehash($u['password_hash'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT)) {
            Database::exec('UPDATE users SET password_hash = ? WHERE id = ?', [self::hashPassword($password), $u['id']]);
        }
        self::login((int) $u['id']);
        Database::exec('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$u['id']]);
        audit('login', 'users', (int) $u['id']);
        return [true, ''];
    }

    private static function terkunci(string $username): bool
    {
        $win = 'created_at > (NOW() - INTERVAL ' . (int) LOGIN_KUNCI_MENIT . ' MINUTE) AND berhasil = 0';
        $byUser = (int) Database::value("SELECT COUNT(*) FROM login_attempt WHERE $win AND username = ?", [substr($username, 0, 60)]);
        $byIp = (int) Database::value("SELECT COUNT(*) FROM login_attempt WHERE $win AND ip_address = ?", [client_ip()]);
        return $byUser >= LOGIN_MAX_GAGAL || $byIp >= LOGIN_MAX_GAGAL * 5;
    }

    /** Mulai sesi login untuk user (dipakai login biasa dan remember-me) */
    private static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['uid'] = $userId;
        $_SESSION['ua'] = hash('sha256', user_agent());
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        self::$user = self::loadUser($userId);
        self::$resolved = true;
        self::issueRemember($userId);
    }

    // ---------------------------------------------------------------- token ingat saya

    private static function cookieOptions(int $expires): array
    {
        return [
            'expires'  => $expires,
            'path'     => '/',
            'secure'   => Security::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    private static function issueRemember(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));   // 24 karakter
        $validator = bin2hex(random_bytes(32));
        $exp = time() + SESSION_LIFETIME;

        Database::exec('DELETE FROM user_token WHERE user_id = ? AND expires_at < ?', [$userId, date('Y-m-d H:i:s')]);
        Database::exec(
            'INSERT INTO user_token (user_id, selector, token_hash, user_agent, ip_address, expires_at, last_used_at) VALUES (?,?,?,?,?,?,NOW())',
            [$userId, $selector, hash('sha256', $validator), user_agent(), client_ip(), date('Y-m-d H:i:s', $exp)]
        );
        setcookie(self::COOKIE_REMEMBER, $selector . ':' . $validator, self::cookieOptions($exp));
        $_COOKIE[self::COOKIE_REMEMBER] = $selector . ':' . $validator;
    }

    private static function tryRemember(): void
    {
        $parts = explode(':', (string) $_COOKIE[self::COOKIE_REMEMBER], 2);
        if (count($parts) !== 2 || !preg_match('/^[a-f0-9]{24}$/', $parts[0]) || !preg_match('/^[a-f0-9]{64}$/', $parts[1])) {
            self::forgetCookie();
            return;
        }
        $row = Database::row('SELECT * FROM user_token WHERE selector = ? AND expires_at > ?', [$parts[0], date('Y-m-d H:i:s')]);
        if (!$row) {
            self::forgetCookie();
            return;
        }
        if (!hash_equals($row['token_hash'], hash('sha256', $parts[1]))) {
            // validator salah: kemungkinan token dicuri, cabut semua token user
            Database::exec('DELETE FROM user_token WHERE user_id = ?', [$row['user_id']]);
            self::forgetCookie();
            return;
        }
        $user = self::loadUser((int) $row['user_id']);
        if (!$user) {
            Database::exec('DELETE FROM user_token WHERE id = ?', [$row['id']]);
            self::forgetCookie();
            return;
        }
        Database::exec('DELETE FROM user_token WHERE id = ?', [$row['id']]); // rotasi token
        self::login((int) $row['user_id']);
    }

    private static function forgetCookie(): void
    {
        setcookie(self::COOKIE_REMEMBER, '', self::cookieOptions(time() - 3600));
        unset($_COOKIE[self::COOKIE_REMEMBER]);
    }

    public static function currentSelector(): string
    {
        $c = (string) ($_COOKIE[self::COOKIE_REMEMBER] ?? '');
        return strpos($c, ':') === 24 ? substr($c, 0, 24) : '';
    }

    // ---------------------------------------------------------------- logout & perangkat

    public static function logout(): void
    {
        $uid = self::id();
        $sel = self::currentSelector();
        if ($uid && $sel !== '') {
            Database::exec('DELETE FROM user_token WHERE user_id = ? AND selector = ?', [$uid, $sel]);
        }
        if ($uid) {
            audit('logout', 'users', $uid);
        }
        self::forgetCookie();
        self::clearSession();
    }

    private static function clearSession(): void
    {
        self::$user = null;
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /** Cabut satu perangkat milik user */
    public static function revokeToken(int $tokenId, int $userId): bool
    {
        return Database::exec('DELETE FROM user_token WHERE id = ? AND user_id = ?', [$tokenId, $userId]) > 0;
    }

    /** Keluar dari semua perangkat lain (token saat ini dipertahankan) */
    public static function logoutOthers(int $userId): int
    {
        return Database::exec('DELETE FROM user_token WHERE user_id = ? AND selector <> ?', [$userId, self::currentSelector()]);
    }

    public static function devices(int $userId): array
    {
        return Database::all(
            'SELECT id, selector, user_agent, ip_address, created_at, last_used_at, expires_at
             FROM user_token WHERE user_id = ? AND expires_at > ? ORDER BY created_at DESC',
            [$userId, date('Y-m-d H:i:s')]
        );
    }
}
