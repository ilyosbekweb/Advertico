<?php
declare(strict_types=1);

final class Auth
{
    private const MAX_EMAIL_FAILURES = 5;   // bitta email uchun
    private const MAX_IP_FAILURES = 10;     // bitta IP uchun
    private const LOCK_MINUTES = 15;

    private static ?array $user = null;

    public static function hash(string $password): string
    {
        return password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT);
    }

    public static function passwordProblem(string $password): ?string
    {
        if (mb_strlen($password) < 10) {
            return 'Parol kamida 10 belgidan iborat bo‘lishi kerak.';
        }
        if (!preg_match('/[A-Za-zА-Яа-я]/u', $password) || !preg_match('/\d/', $password)) {
            return 'Parolda kamida bitta harf va bitta raqam bo‘lishi kerak.';
        }
        return null;
    }

    /** Bloklangan bo'lsa, qolgan daqiqalar soni; aks holda 0. */
    public static function lockedMinutes(string $email): int
    {
        $stmt = db()->prepare('SELECT
                SUM(ip = :ip) AS ip_fail, SUM(email = :email) AS email_fail, MAX(created_at) AS last_fail
            FROM login_attempts
            WHERE success = 0 AND created_at > (NOW() - INTERVAL ' . self::LOCK_MINUTES . ' MINUTE)
              AND (ip = :ip2 OR email = :email2)');
        $stmt->execute([':ip' => client_ip(), ':email' => $email, ':ip2' => client_ip(), ':email2' => $email]);
        $row = $stmt->fetch();
        if ((int)$row['ip_fail'] >= self::MAX_IP_FAILURES || (int)$row['email_fail'] >= self::MAX_EMAIL_FAILURES) {
            $elapsed = time() - (int)strtotime((string)$row['last_fail']);
            return max(1, (int)ceil((self::LOCK_MINUTES * 60 - $elapsed) / 60));
        }
        return 0;
    }

    public static function attempt(string $email, string $password): bool
    {
        $email = mb_strtolower(trim($email));
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        // Foydalanuvchi topilmasa ham hash tekshiramiz — javob vaqti bir xil bo'lishi uchun.
        $hash = $admin['password_hash'] ?? '$2y$12$xw9jsz745Nfu/901R18Gvud5Cat3em3gh6zeTGgKu8l3VYs/znW5O';
        $ok = password_verify($password, $hash) && $admin !== false;

        db()->prepare('INSERT INTO login_attempts (email, ip, success) VALUES (:e, :ip, :s)')
            ->execute([':e' => mb_substr($email, 0, 190), ':ip' => client_ip(), ':s' => $ok ? 1 : 0]);

        if (!$ok) {
            return false;
        }

        if (password_needs_rehash($admin['password_hash'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT)) {
            db()->prepare('UPDATE admins SET password_hash = :h WHERE id = :id')->execute([':h' => self::hash($password), ':id' => $admin['id']]);
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['auth_at'] = time();
        $_SESSION['last_seen'] = time();
        $_SESSION['ua'] = hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
        unset($_SESSION['csrf']);

        db()->prepare('UPDATE admins SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id')
            ->execute([':ip' => client_ip(), ':id' => $admin['id']]);
        // Eski urinishlar jadvalini kichik saqlaymiz.
        db()->exec('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 30 DAY)');
        audit('login', 'admin', (int)$admin['id']);
        return true;
    }

    public static function check(): bool
    {
        if (empty($_SESSION['admin_id'])) {
            return false;
        }
        $uaHash = hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
        if (!hash_equals((string)($_SESSION['ua'] ?? ''), $uaHash)
            || time() - (int)($_SESSION['last_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
            self::logout();
            return false;
        }
        if (self::user() === null) {
            self::logout();
            return false;
        }
        $_SESSION['last_seen'] = time();
        return true;
    }

    public static function user(): ?array
    {
        if (self::$user === null && !empty($_SESSION['admin_id'])) {
            $stmt = db()->prepare('SELECT id, name, email, last_login_at, last_login_ip FROM admins WHERE id = :id');
            $stmt->execute([':id' => $_SESSION['admin_id']]);
            self::$user = $stmt->fetch() ?: null;
        }
        return self::$user;
    }

    public static function logout(): void
    {
        self::$user = null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000, 'path' => $p['path'], 'secure' => $p['secure'],
                'httponly' => $p['httponly'], 'samesite' => $p['samesite'] ?? 'Strict',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function adminExists(): bool
    {
        return (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
    }
}
