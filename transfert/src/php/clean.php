<?php

$directory = __DIR__ . '/../../files';
$files = scandir($directory);

// Récupère la date courante (à minuit)
$today = new DateTime('today'); // 00:00:00 aujourd'hui

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;

    $path = $directory . '/' . $file;

    if (!is_file($path)) continue;

    $modTime = new DateTime();
    $modTime->setTimestamp(filemtime($path));

    // Si le fichier a été modifié avant aujourd'hui
    if ($modTime < $today) {
        unlink($path);
    }
}
