<?php
// Définir le type de réponse en JSON
header('Content-Type: application/json');

// Chemins des répertoires à lire
$directories = [
    "videos" => "/var/www/multimedia.enpj.fr/www/signage/src/vid/playlist/",
    "vertical" => "/var/www/multimedia.enpj.fr/www/signage/src/vid/vertical/",
    "horizontal" => "/var/www/multimedia.enpj.fr/www/signage/src/vid/horizontal/"
];

$result = [];

// Pour chaque répertoire, lire les fichiers et les ajouter au résultat
foreach ($directories as $key => $directory) {
    $files = [];
    if (is_dir($directory)) {
        $dir = opendir($directory);
        while ($file = readdir($dir)) {
            if (strpos($file, '.mp4') !== false) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                array_push($files, $filename);
            }
        }
        closedir($dir);
        sort($files); // Trier les fichiers
    }
    $result[$key] = $files;
}

// Renvoyer les résultats en JSON
echo json_encode($result);
?>