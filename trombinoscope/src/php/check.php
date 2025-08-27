<?php
// src/php/check.php
header('Content-Type: application/json');
if (!isset($_GET['prenom']) || !isset($_GET['nom'])) {
    echo json_encode(['exists' => false]);
    exit;
}
$prenom = $_GET['prenom'];
$nom = $_GET['nom'];
function nettoyerChaine($chaine) {
    $chaine = mb_strtoupper($chaine, 'UTF-8');
    $chaine = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $chaine);
    $chaine = preg_replace('/[^A-Z0-9\-]/', '_', $chaine);
    $chaine = preg_replace('/_+/', '_', $chaine);
    return trim($chaine, '_');
}
$prenomNettoye = nettoyerChaine($prenom);
$nomNettoye = nettoyerChaine($nom);
$xmlFile = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/agents/' . $nomNettoye . '_' . $prenomNettoye . '.xml';
$ref = pathinfo($xmlFile, PATHINFO_FILENAME);
$photoFile = $_SERVER['DOCUMENT_ROOT'] . '/trombinoscope/photos/' . $ref . '.jpg';
$photoExists = file_exists($photoFile);

if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    echo json_encode([
       'exists' => true,
       'rattachement' => (string)$xml->rattachement,
       'poste' => (string)$xml->poste,
       'dateArrivee' => (string)$xml->dateArrivee,
       'fichePoste' => ((string)$xml->fichePoste == '1') ? $ref : '',
       'photo' => $photoExists ? $ref : ''
    ]);
} else {
    echo json_encode(['exists' => false]);
}
?>
