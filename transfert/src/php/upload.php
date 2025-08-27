<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/secret.php';

$directory = __DIR__ . '/../../files';

// Vérification basique
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
    http_response_code(400);
    echo "Aucun fichier reçu.";
    exit;
}

if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo "Erreur PHP : " . $_FILES['file']['error'];
    exit;
}

$originalName = $_POST['name'] ?? $_FILES['file']['name'];
$password = $_POST['password'] ?? '';
$tmpPath = $_FILES['file']['tmp_name'];
$originalExt = pathinfo($originalName, PATHINFO_EXTENSION);

// Limite de taille par fichier (100 Mo)
$maxSize = 100 * 1024 * 1024;
if ($_FILES['file']['size'] > $maxSize) {
    http_response_code(413);
    echo "Fichier trop volumineux (limite de 100 Mo).";
    exit;
}

// Limite de quota total (1 Go dans /files/)
function getDirectorySize($path) {
    $size = 0;
    foreach (glob($path . '/*') as $file) {
        if (is_file($file)) $size += filesize($file);
    }
    return $size;
}

$quota = 1 * 1024 * 1024 * 1024;
$current = getDirectorySize($directory);
$incoming = $_FILES['file']['size'];

if ($current + $incoming > $quota) {
    http_response_code(507); // Insufficient Storage
    echo "Espace insuffisant sur le serveur (quota de 1 Go dépassé).";
    exit;
}

// Nettoyage du nom
$safeName = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $originalName));
$safeName = preg_replace('/[^a-z0-9\._\- ]+/i', '', $safeName);
$safeName = str_replace(["'", ' '], ['-', '_'], $safeName);
$safeName = preg_replace('/_+/', '_', $safeName);
$safeName = preg_replace('/__+/', '_', $safeName);
$targetPath = $directory . '/' . $safeName;

// Gestion des doublons
$version = 1;
while (file_exists($targetPath)) {
    $base = pathinfo($safeName, PATHINFO_FILENAME);
    $ext = pathinfo($safeName, PATHINFO_EXTENSION);
    if (preg_match('/_v(\d+)$/', $base, $m)) {
        $version = (int)$m[1] + 1;
        $base = preg_replace('/_v\d+$/', '', $base);
    }
    $safeName = $base . '_v' . $version . '.' . $ext;
    $targetPath = $directory . '/' . $safeName;
}

// Chiffrement
$iv = random_bytes(16);
$key = !empty($password) ? $password : getDailyKey();
$content = file_get_contents($tmpPath);
$ciphertext = openssl_encrypt($content, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
if ($ciphertext === false) {
    http_response_code(500);
    echo "Erreur de chiffrement.";
    exit;
}
$hash = hash('sha256', $key);
$base = pathinfo($safeName, PATHINFO_FILENAME);
$ext = pathinfo($safeName, PATHINFO_EXTENSION);
$encryptedName = $base . '__' . $hash . '.' . $ext;
$targetPath = $directory . '/' . $encryptedName;

file_put_contents($targetPath, $iv . $ciphertext);

echo "OK";