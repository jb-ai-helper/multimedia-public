<?php

$directory = __DIR__ . '/../../files';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['file'])) {
    http_response_code(400);
    echo "Nom de fichier manquant.";
    exit;
}

$userFile = basename($_POST['file']); // nom lisible fourni par le client
$found = false;

// Tentative de suppression d’un fichier non protégé
$directPath = $directory . '/' . $userFile;
if (is_file($directPath)) {
    unlink($directPath);
    $found = true;
} else {
    // Recherche d’un fichier protégé correspondant
    $base = pathinfo($userFile, PATHINFO_FILENAME);
    $ext = pathinfo($userFile, PATHINFO_EXTENSION);
    foreach (glob("$directory/{$base}__*.{$ext}") as $match) {
        unlink($match);
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo "Fichier introuvable.";
    exit;
}

echo "OK";
