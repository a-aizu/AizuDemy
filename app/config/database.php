<?php
// Konfigurasi database — kredensial dibaca dari environment variable.
// JANGAN menulis password asli di file ini.
define('DB_HOST', getenv('AIZUDEMY_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('AIZUDEMY_DB_USER') ?: '');
define('DB_PASS', getenv('AIZUDEMY_DB_PASS') ?: '');
define('DB_NAME', getenv('AIZUDEMY_DB_NAME') ?: '');

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $pdo;
}
