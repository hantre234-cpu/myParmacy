<?php
// ============================================================
//  Database Configuration
//  Edit these values to match your local server settings
// ============================================================

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'pharmacy_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',   'PharmaCare');
define('APP_VERSION','1.0.0');

// ─────────────────────────────────────────
//  PDO Singleton
// ─────────────────────────────────────────
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('<div class="alert alert-error">Database connection failed: '
                . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }
    return $pdo;
}

// ─────────────────────────────────────────
//  Helper: flash messages via session
// ─────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// ─────────────────────────────────────────
//  Helper: safe redirect
// ─────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ─────────────────────────────────────────
//  Helper: sanitize output
// ─────────────────────────────────────────
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
