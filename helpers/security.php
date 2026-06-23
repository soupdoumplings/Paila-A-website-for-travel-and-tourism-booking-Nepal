<?php
// Shared security helpers for sessions, headers, and CSRF protection.

function is_https_request() {
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
}

function configure_secure_session() {
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $params['path'] ?: '/',
        'domain' => $params['domain'] ?? '',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (session_name() === 'PHPSESSID') {
        session_name('PAILA_SESSID');
    }
}

function secure_session_start() {
    configure_secure_session();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $now = time();
    if (!isset($_SESSION['_created_at'])) {
        $_SESSION['_created_at'] = $now;
    }

    if (!isset($_SESSION['_last_regenerated'])) {
        $_SESSION['_last_regenerated'] = $now;
    } elseif (($now - $_SESSION['_last_regenerated']) > 1800) {
        session_regenerate_id(true);
        $_SESSION['_last_regenerated'] = $now;
    }

    if (isset($_SESSION['_last_seen']) && ($now - $_SESSION['_last_seen']) > 7200) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?? '',
                'secure' => is_https_request(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        secure_session_start();
        return;
    }

    $_SESSION['_last_seen'] = $now;
}

function apply_security_headers() {
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(self)');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'; img-src 'self' data: https:; media-src 'self' https:; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline'; connect-src 'self'");
}

function csrf_token() {
    secure_session_start();
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token($token) {
    secure_session_start();
    return is_string($token) && isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function require_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($_POST['_csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Security check failed. Please refresh the page and try again.');
    }
}

function require_post_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

configure_secure_session();
apply_security_headers();
