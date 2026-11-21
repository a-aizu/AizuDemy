<?php
require_once __DIR__ . '/../app/config/config.php';
$files = glob(__DIR__ . '/../assets/uploads/*.*');
foreach ($files as $f) {
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (in_array($ext, ['png', 'jpg', 'jpeg']) && filesize($f) > 100000) {
        $img = false;
        if ($ext === 'png') $img = @imagecreatefrompng($f);
        else $img = @imagecreatefromjpeg($f);
        if ($img) {
            $w = imagesx($img);
            $h = imagesy($img);
            $newW = ($w > 800) ? 800 : $w;
            $newH = (int)($h * ($newW / $w));
            $scaled = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($scaled, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagejpeg($scaled, $f, 75);
            imagedestroy($img);
            imagedestroy($scaled);
            echo basename($f) . " -> " . round(filesize($f)/1024) . "KB\n";
        }
    }
}
