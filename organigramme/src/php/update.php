<?php
// /organigramme/src/php/update.php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mapping_groups'])) {
    $mapping_groups = $_POST['mapping_groups'];
    $mapping_jobs   = isset($_POST['mapping_jobs']) ? $_POST['mapping_jobs'] : [];

    // Dossier où sont stockés les fichiers XML des agents (dans l'application organigramme)
    $agentsDir = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/agents/';
    // Récupérer tous les fichiers XML directement depuis le dossier agents
    $xmlFiles = glob($agentsDir . '*.xml');

    foreach ($xmlFiles as $xmlFile) {
        $xml = simplexml_load_file($xmlFile);
        if ($xml !== false) {
            // Mise à jour du rattachement (groupe) si le champ existe
            if (isset($xml->rattachement)) {
                $oldGroupCode = trim((string)$xml->rattachement);
                if (isset($mapping_groups[$oldGroupCode]) && $mapping_groups[$oldGroupCode] !== '') {
                    $xml->rattachement = $mapping_groups[$oldGroupCode];
                }
            }
            // Mise à jour du poste (job) si le champ existe
            if (isset($xml->poste)) {
                $oldJobCode = trim((string)$xml->poste);
                if (isset($mapping_jobs[$oldJobCode]) && $mapping_jobs[$oldJobCode] !== '') {
                    $xml->poste = $mapping_jobs[$oldJobCode];
                }
            }
            $xmlString = $xml->asXML();
            file_put_contents($xmlFile, $xmlString);
        }
    }

    // Remplacer l'ancien fichier organigramme.json par le nouveau
    $tempOrganigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme_temp.json';
    $organigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme.json';
    rename($tempOrganigrammePath, $organigrammePath);

    // Nettoyer la session
    unset($_SESSION['ancienGroupes']);
    unset($_SESSION['nouveauOrganigramme']);
    unset($_SESSION['groupesSupprimes']);

    header('Location: /organigramme/manager.php?message=Organigramme mis à jour avec succès.');
    exit();
} else {
    header('Location: /organigramme/manager.php?message=Erreur lors de la mise à jour.');
    exit();
}
?>
