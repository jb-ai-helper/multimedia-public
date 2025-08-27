<?php
// Étape 1 : Définir la timezone pour l'export
date_default_timezone_set('Europe/Paris');

// Étape 2 : Créer le dossier temporaire pour l'export
$exportDir = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/exports';
$tmpDirName = 'organigramme_' . date('Ymd') . '_' . date('His');
$tmpDir = $exportDir . '/' . $tmpDirName;
mkdir($tmpDir . '/assets', 0777, true);

// Étape 3 : Capturer précisément le HTML rendu par la page PHP
ob_start();
chdir($_SERVER['DOCUMENT_ROOT'] . '/organigramme');
include($_SERVER['DOCUMENT_ROOT'] . '/organigramme/index.php');
$html = ob_get_clean();

// Supprimer le formulaire d'export identifié par "exportform"
$html = preg_replace('/<form[^>]*name="exportform"[^>]*>.*?<\/form>/s', '', $html);

// Étape 4 : Extraire toutes les ressources du HTML (href, src, url())
preg_match_all('/(?:href|src)=["\']([^"\']+)["\']|url\(["\']?([^"\')]+)["\']?\)/i', $html, $matches);
$resourcesHTML = array_filter(array_merge($matches[1], $matches[2]));

// Étape 5 : Transformer chaque ressource HTML en chemin absolu (depuis index.php)
$originalResources = [];
foreach ($resourcesHTML as $res) {
    if (strpos($res, '/') === 0) {
        // Chemin absolu : depuis la racine du serveur
        $absPath = realpath($_SERVER['DOCUMENT_ROOT'] . $res);
    } else {
        // Chemin relatif : depuis le dossier organigramme
        $absPath = realpath($_SERVER['DOCUMENT_ROOT'] . '/organigramme/' . $res);
    }

    if ($absPath && file_exists($absPath)) {
        $originalResources[$res] = $absPath;
    }
}

// Étape 6 & 7 : Identifier les fichiers CSS et extraire leurs ressources secondaires
function extractCSSResources($cssPath, &$cssResources) {
    $cssContent = file_get_contents($cssPath);
    preg_match_all('/@import ["\']([^"\']+)["\']|url\(["\']?([^"\')]+)["\']?\)/i', $cssContent, $cssMatches);
    $foundResources = array_filter(array_merge($cssMatches[1], $cssMatches[2]));
    foreach ($foundResources as $res) {
        // Vérifie si le chemin est absolu ou relatif
        if (strpos($res, '/') === 0) {
            $absRes = realpath($_SERVER['DOCUMENT_ROOT'] . $res);
        } else {
            $absRes = realpath(dirname($cssPath) . '/' . $res);
        }

        if ($absRes && file_exists($absRes) && !isset($cssResources[$res])) {
            $cssResources[$res] = $absRes;
            if (preg_match('/\.css$/i', $absRes)) {
                extractCSSResources($absRes, $cssResources); // récursif
            }
        }
    }
}

$cssResources = [];
foreach ($originalResources as $res => $absPath) {
    if (preg_match('/\.css$/i', $absPath)) {
        extractCSSResources($absPath, $cssResources);
    }
}

// Fusionner ressources primaires et secondaires
$originalResources = array_merge($originalResources, $cssResources);

// Étape 8 & 9 : Créer une seconde liste de ressources pour l'export avec nouvelle arborescence
$exportResources = [];
foreach ($originalResources as $original => $absPath) {
    $relativePath = str_replace(
        [$_SERVER['DOCUMENT_ROOT'] . '/organigramme', $_SERVER['DOCUMENT_ROOT'] . '/trombinoscope', $_SERVER['DOCUMENT_ROOT']],
        '',
        $absPath
    );
    $relativePath = preg_replace('#^/#', '', $relativePath);
    $exportPath = 'assets/' . $relativePath;
    $exportResources[$original] = $exportPath;
}

// Étape 10 & 11 : Copier chaque ressource dans le dossier export
foreach ($exportResources as $original => $newPath) {
    $destination = $tmpDir . '/' . $newPath;
    if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0777, true);
    copy($originalResources[$original], $destination);

    // Si c'est un fichier CSS, corriger aussi les chemins internes
    if (preg_match('/\.css$/i', $destination)) {
        $cssContent = file_get_contents($destination);

        // Parcourir toutes les ressources CSS secondaires pour les remplacer
        foreach ($exportResources as $origSecondary => $newSecondary) {
            // Calculer les chemins relatifs entre les CSS exportés
            $relativeFromCSS = getRelativePath(dirname($newPath), $newSecondary);

            // Remplacer précisément les références CSS internes
            $pattern = '#(["\'])' . preg_quote($origSecondary, '#') . '(["\'])#';
            $replacement = '$1' . $relativeFromCSS . '$2';
            $cssContent = preg_replace($pattern, $replacement, $cssContent);
        }

        // Sauvegarder le CSS ajusté
        file_put_contents($destination, $cssContent);
    }
}

// Fonction pour obtenir le chemin relatif entre deux ressources exportées
function getRelativePath($from, $to) {
    $from = explode('/', $from);
    $to = explode('/', $to);
    while(count($from) && count($to) && ($from[0] == $to[0])) {
        array_shift($from);
        array_shift($to);
    }
    return str_pad('', count($from)*3, '../').implode('/', $to);
}

// Étape 12 : Ajuster les liens dans le HTML capturé avec les nouveaux chemins d'export
foreach ($exportResources as $original => $newPath) {
    $pattern = '#(["\'])' . preg_quote($original, '#') . '(["\'])#';
    $replacement = '$1' . $newPath . '$2';
    $html = preg_replace($pattern, $replacement, $html);
}

// Sauvegarder le HTML ajusté comme index.html
file_put_contents($tmpDir . '/index.html', $html);

// Étape 13 : Générer proprement imsmanifest.xml pour SCORM
$manifestFiles = "    <file href=\"index.html\"/>\n";
foreach ($exportResources as $newPath) {
    $manifestFiles .= "    <file href=\"" . htmlspecialchars($newPath, ENT_XML1) . "\"/>\n";
}

$manifest = '<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="com.enpjj.organigramme" version="1.0"
          xmlns="http://www.imsglobal.org/xsd/imscp_v1p1"
          xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3">
  <metadata>
    <schema>ADL SCORM</schema>
    <schemaversion>1.2</schemaversion>
  </metadata>
  <organizations default="ORG1">
    <organization identifier="ORG1">
      <title>Organigramme interactif</title>
      <item identifier="ITEM1" identifierref="RES1">
        <title>Organigramme</title>
      </item>
    </organization>
  </organizations>
  <resources>
    <resource identifier="RES1" adlcp:scormType="sco" type="webcontent" href="index.html">
' . $manifestFiles . '    </resource>
  </resources>
</manifest>';
file_put_contents($tmpDir . '/imsmanifest.xml', $manifest);

// Étapes 14 à 16 : Génération du ZIP, suppression temporaire et téléchargement auto
$zipName = $exportDir . '/' . $tmpDirName . '.zip';
$zip = new ZipArchive();
$zip->open($zipName, ZipArchive::CREATE);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir));
foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($tmpDir) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}
$zip->close();

// Supprimer le dossier temporaire
function viderDossier($dossier) {
    if (!is_dir($dossier)) return;
    $fichiers = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dossier, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($fichiers as $fichier) {
        if ($fichier->isDir()) rmdir($fichier->getRealPath());
        else unlink($fichier->getRealPath());
    }
    rmdir($dossier);
}

viderDossier($tmpDir);

// Télécharger automatiquement le ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
header('Content-Length: ' . filesize($zipName));
readfile($zipName);
//exit('Export réalisé le ' . date('d/m/Y à H:i:s'));
