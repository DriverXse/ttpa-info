<?php
// config.php
// Minimal, robust config + helpers for TTPA demo (PHP + SQLite)

declare(strict_types=1);

// --- Configuration ---
define('DEBUG_MODE', false); // Set true only on local dev
define('DB_DIR', __DIR__ . '/data');
define('DB_FILE', DB_DIR . '/ttpa_ads.sqlite');

define('EDITORIAL_EMAIL', 'editorial@ttpa-demo.eu');

// Admin credentials (password set on first login)
// Username: admin
define('ADMIN_USERNAME', 'admin');

// Session hardening (works both on localhost and HTTPS; Secure flag set automatically if HTTPS)
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// Basic security headers (safe defaults for small PHP apps)
function apply_security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // CSP: allow inline CSS (since pages are single-file) but block scripts by default
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
}

// HTML escaping helper
function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// CSRF helpers
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function csrf_validate(string $token): void {
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        exit('Invalid request (CSRF).');
    }
}

// Auth helpers
function is_admin(): bool {
    return !empty($_SESSION['is_admin_logged_in']);
}
function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

// Ensure DB directory exists (outside webroot if possible)
if (!is_dir(DB_DIR)) {
    @mkdir(DB_DIR, 0700, true);
}

// --- DB connect ---
try {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Pragmas: better reliability for small concurrent writes
    $pdo->exec("PRAGMA foreign_keys = ON;");
    $pdo->exec("PRAGMA journal_mode = WAL;");
    $pdo->exec("PRAGMA synchronous = NORMAL;");
    $pdo->exec("PRAGMA busy_timeout = 5000;");

    // --- Schema ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS political_ads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,

            sponsor_name TEXT,
            sponsor_entity_type TEXT,
            sponsor_registration_id TEXT,
            sponsor_email TEXT,
            sponsor_phone TEXT,
            sponsor_address TEXT,
            sponsor_country TEXT,
            sponsor_controller TEXT,

            payer_name TEXT,
            payer_type TEXT,
            payer_email TEXT,
            payer_address TEXT,
            payer_country TEXT,

            link_type TEXT,
            link_description TEXT,
            official_info_link TEXT,

            ad_title TEXT,
            ad_description TEXT,
            medium TEXT,

            start_date TEXT,
            end_date TEXT,

            estimated_amount REAL,
            funding_type TEXT,
            funding_sources TEXT,
            funding_origin_eu TEXT,

            targeting_personal_data TEXT,
            targeting_categories TEXT,
            targeting_data_sources TEXT,
            targeting_goals TEXT,
            targeting_parameters TEXT,
            targeting_ai_use TEXT,
            targeting_policy_link TEXT,
            targeting_rights_link TEXT,

            certified_ttpa TEXT,

            invoiced_amount REAL,
            currency TEXT,
            other_benefits TEXT,
            calculation_method TEXT,
            specific_service TEXT,
            previously_suspended TEXT,
            suspension_reason TEXT,
            eu_repository_link TEXT,
            reach TEXT,

            status TEXT,
            created_at TEXT,
            updated_at TEXT
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT
        );
    ");
} catch (Throwable $e) {
    http_response_code(500);
    if (DEBUG_MODE) {
        exit('DB error: ' . $e->getMessage());
    }
    exit('DB error.');
}

function admin_password_hash(PDO $pdo): ?string {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = 'admin_password_hash'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (string)$row['value'] : null;
}

function is_admin_configured(PDO $pdo): bool {
    return admin_password_hash($pdo) !== null;
}

function set_admin_password(PDO $pdo, string $password): void {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO settings (key, value) VALUES ('admin_password_hash', :value)
        ON CONFLICT(key) DO UPDATE SET value = :value
    ");
    $stmt->execute([':value' => $hash]);
}
