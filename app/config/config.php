<?php
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = rtrim(dirname($script), '/');
if ($base === '/' || $base === '\\') $base = '';
// CORETAN: kalau akses via /api/... -> strip suffix /api
if (str_ends_with($base, '/api')) {
    $base = rtrim(dirname($base), '/');
    if ($base === '/' || $base === '\\') $base = '';
}
// CORETAN: di localhost kita pakai subfolder /aizudemy
if (str_starts_with($script, '/aizudemy-gemini')) {
    $base = '/aizudemy-gemini';
} elseif (str_starts_with($script, '/aizudemy')) {
    $base = '/aizudemy';
}
define('BASE_URL', $scheme.'://'.$host.$base);
define('SITE_NAME', 'AizuDemy');
define('SITE_DESC', 'Platform belajar digital — Tutorial, Teknologi & Pendidikan');
define('UPLOAD_PATH', __DIR__.'/../../assets/uploads/');
session_start();
require_once __DIR__.'/database.php';
require_once __DIR__.'/../helpers/functions.php';
