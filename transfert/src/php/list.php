<?php

$directory = __DIR__ . '/../../files';
$baseUrl = 'https://multimedia.enpjj.fr/transfert/?file=';

$files = array_diff(scandir($directory), ['.', '..']);
$result = [];

foreach ($files as $file) {
    $path = $directory . '/' . $file;
    if (!is_file($path)) continue;

    // Extraire le nom lisible (sans suffixe __$hash)
    if (preg_match('/^(.+?)__(.+)\.(.+)$/', $file, $matches)) {
        $safeName = $matches[1] . '.' . $matches[3]; // nom.ext
    } else {
        $safeName = $file;
    }

    $result[] = [
        'name' => $safeName,                      // nom visible
        'realName' => $file,                      // nom réel (sur disque)
        'url' => $baseUrl . urlencode($safeName)  // URL publique
    ];
}

header('Content-Type: application/json');
echo json_encode($result);
