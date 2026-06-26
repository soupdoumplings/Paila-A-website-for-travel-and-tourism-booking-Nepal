<?php
require_once __DIR__ . '/../helpers/security.php';

$pailaLocalConfig = [];
$pailaLocalConfigPath = __DIR__ . '/db.local.php';
if (is_file($pailaLocalConfigPath)) {
    $loadedConfig = require $pailaLocalConfigPath;
    if (is_array($loadedConfig)) {
        $pailaLocalConfig = $loadedConfig;
    }
}

function paila_config_value(string $key, string $default): string {
    $value = getenv($key);
    if ($value !== false && trim($value) !== '') {
        return $value;
    }

    global $pailaLocalConfig;
    if (isset($pailaLocalConfig[$key]) && trim((string)$pailaLocalConfig[$key]) !== '') {
        return (string)$pailaLocalConfig[$key];
    }

    return $default;
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

define('DB_HOST', paila_config_value('DB_HOST', 'localhost'));
define('DB_USER', paila_config_value('DB_USER', 'root'));
define('DB_PASS', paila_config_value('DB_PASS', ''));
define('DB_NAME', paila_config_value('DB_NAME', 'nepal_tours'));
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
