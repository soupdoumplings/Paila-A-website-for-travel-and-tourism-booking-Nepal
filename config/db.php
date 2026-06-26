<?php
require_once __DIR__ . '/../helpers/security.php';

function paila_env_or_default(string $key, string $default): string {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function paila_detect_base_url(): string {
    $envBaseUrl = getenv('BASE_URL');
    if ($envBaseUrl !== false && trim($envBaseUrl) !== '') {
        return rtrim($envBaseUrl, '/');
    }

    if (empty($_SERVER['HTTP_HOST'])) {
        return '';
    }

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    );

    $scheme = $isHttps ? 'https' : 'http';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    foreach (['/public/', '/admin/', '/actions/'] as $marker) {
        $position = strpos($scriptName, $marker);
        if ($position !== false) {
            $basePath = rtrim(substr($scriptName, 0, $position), '/');
            break;
        }
    }

    if ($basePath === '/' || $basePath === '.') {
        $basePath = '';
    }

    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath;
}

define('DB_HOST', paila_env_or_default('DB_HOST', 'localhost'));
define('DB_USER', paila_env_or_default('DB_USER', 'np03cs4a240006'));
define('DB_PASS', paila_env_or_default('DB_PASS', 'SvoFQrw1PP'));
define('DB_NAME', paila_env_or_default('DB_NAME', 'np03cs4a240006'));
define('BASE_URL', paila_detect_base_url());
define('SITE_NAME', 'PAILA');

$db_error = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}
