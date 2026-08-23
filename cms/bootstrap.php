<?php
declare(strict_types=1);

const CMS_ROLE_SUPER = 'super_admin';
const CMS_ROLE_ADMIN = 'admin';
const CMS_MAX_UPLOAD = 20 * 1024 * 1024;

function cms_root(): string
{
    $configured = getenv('RCECA_CMS_DATA_DIR');
    return $configured !== false && $configured !== ''
        ? rtrim($configured, '/')
        : dirname(__DIR__) . '/var';
}

function cms_upload_root(): string
{
    $configured = getenv('RCECA_UPLOAD_DIR');
    return $configured !== false && $configured !== ''
        ? rtrim($configured, '/')
        : dirname(__DIR__) . '/uploads';
}

function cms_bootstrap_storage(): void
{
    foreach ([cms_root(), cms_upload_root()] as $directory) {
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create runtime directory.');
        }
    }
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    cms_bootstrap_storage();
    $pdo = new PDO('sqlite:' . cms_root() . '/rceca.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    return $pdo;
}

function cms_schema(): void
{
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) {
        throw new RuntimeException('Schema file is unavailable.');
    }
    db()->exec($sql);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($items) ? $items : [];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function require_csrf(): void
{
    $provided = (string) ($_POST['csrf'] ?? '');
    if ($provided === '' || !hash_equals(csrf_token(), $provided)) {
        http_response_code(419);
        exit('The form has expired. Please go back and try again.');
    }
}

function start_secure_session(): void
{
    if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('rceca_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
    if (isset($_SESSION['last_seen']) && time() - (int) $_SESSION['last_seen'] > 1800) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_seen'] = time();
}

function current_user(): ?array
{
    static $cached = false;
    static $user;
    if ($cached) {
        return $user;
    }
    $cached = true;
    $id = (int) ($_SESSION['user_id'] ?? 0);
    if ($id < 1) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, username, display_name, role, status, must_change_password FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch() ?: null;
    if ($user === null || $user['status'] !== 'active') {
        session_unset();
        return null;
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('/admin/login.php');
    }
    if ((int) $user['must_change_password'] === 1 && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'profile.php') {
        flash('warning', '首次登录请先修改临时密码。');
        redirect('/admin/profile.php');
    }
    return $user;
}

function require_super(): array
{
    $user = require_login();
    if ($user['role'] !== CMS_ROLE_SUPER) {
        http_response_code(403);
        exit('Forbidden');
    }
    return $user;
}

function audit(string $action, string $entityType, ?int $entityId = null, array $details = []): void
{
    $user = current_user();
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli');
    $stmt = db()->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details_json, ip_hash, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $user['id'] ?? null,
        $action,
        $entityType,
        $entityId,
        json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        hash('sha256', $ip),
        now_utc(),
    ]);
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? COLLATE NOCASE LIMIT 1');
    $stmt->execute([trim($username)]);
    $user = $stmt->fetch();
    $generic = '$2y$12$AOfP6Yx0Jw8Zzo7ZjVhxvOePqH.8F6VG2Y1C8p2RB3dY/RJ5WvbuK';
    $hash = $user['password_hash'] ?? $generic;
    $valid = password_verify($password, $hash);
    $locked = $user && $user['locked_until'] && strtotime($user['locked_until'] . ' UTC') > time();
    if (!$user || !$valid || $locked || $user['status'] !== 'active') {
        if ($user && !$locked) {
            $attempts = (int) $user['failed_attempts'] + 1;
            $lockUntil = $attempts >= 5 ? gmdate('Y-m-d H:i:s', time() + 900) : null;
            if ($lockUntil !== null) {
                $attempts = 0;
            }
            $update = db()->prepare('UPDATE users SET failed_attempts = ?, locked_until = ?, updated_at = ? WHERE id = ?');
            $update->execute([$attempts, $lockUntil, now_utc(), $user['id']]);
        }
        audit('login_failed', 'session', null, ['username' => mb_substr(trim($username), 0, 64)]);
        usleep(250000);
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['last_seen'] = time();
    $update = db()->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login_at = ?, updated_at = ? WHERE id = ?');
    $update->execute([now_utc(), now_utc(), $user['id']]);
    audit('login_success', 'session');
    return true;
}

function valid_password(string $password): bool
{
    return mb_strlen($password) >= 12 && preg_match('/[A-Za-z]/', $password) && preg_match('/\d/', $password);
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=60');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (PHP_SAPI !== 'cli' && str_starts_with($requestPath, '/admin/')) {
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    start_secure_session();
}
cms_schema();
