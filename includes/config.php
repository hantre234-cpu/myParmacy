<?php
// ============================================================
//  config.php — Central configuration
//  PharmaCare | DAW Mini-Project | UHBC
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacare');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',    'PharmaCare');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    'http://localhost/pharmacy');

// ── PDO singleton ────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
                       DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ── Helpers ──────────────────────────────────────────────────
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $key, string $msg = null): ?string {
    if ($msg !== null) {
        $_SESSION[$key] = $msg;
        return null;
    }
    $val = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $val;
}

function paginate(int $total, int $perPage, int $page): array {
    $pages = (int) ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    return ['pages' => $pages, 'offset' => max(0, $offset), 'per_page' => $perPage];
}

session_start();
