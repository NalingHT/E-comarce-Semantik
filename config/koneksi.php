<?php
$db_config = __DIR__ . '/database.local.php';
if (is_file($db_config)) {
    require $db_config;
} else {
    $host     = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    $database = getenv('DB_NAME') ?: 'db_marketplace';
}

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');

$docroot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/') : '';
$approot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
$base_url = ($docroot !== '' && strpos($approot, $docroot) === 0) ? substr($approot, strlen($docroot)) : '';

define('BASE_URL', $base_url);
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
