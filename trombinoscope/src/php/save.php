<?php
// /trombinoscope/src/php/save.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Include Common Functions
require '../../../src/php/commontools.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['prenom'], $_POST['nom'], $_POST['rattachement'], $_POST['poste'], $_POST['dateArrivee'])) {
        die('Données manquantes.');
    }
    
    $prenom = htmlspecialchars($_POST['prenom']);
    $nom = htmlspecialchars($_POST['nom']);
    $rattachement = htmlspecialchars($_POST['rattachement']);
    $poste = htmlspecialchars($_POST['poste']);
    $dateArrivee = htmlspecialchars($_POST['dateArrivee']);
    
    $prenomNettoye = nettoyerChaine($prenom);
    $nomNettoye = nettoyerChaine($nom);
    
    $fileName = $nomNettoye . '_' . $prenomNettoye . '.jpg';
    
    $targetDir = __DIR__ . '/../../photos/';
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            die('Erreur lors de la création du dossier de destination.');
        }
    }
    $targetFile = $targetDir . $fileName;
    
    // Traitement de la photo
    $photoSaved = false;
    if (isset($_POST['photo'])) {
        $data = $_POST['photo'];
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg'])) {
                die('Type de fichier non supporté.');
            }
            $data = base64_decode($data);
            if ($data === false) {
                die('Décodage Base64 échoué.');
            }
        } else {
            die('Data URL invalide.');
        }
        if (file_put_contents($targetFile, $data)) {
            $photoSaved = true;
        } else {
            error_log('Erreur lors de l\'enregistrement de la photo : ' . $fileName, 3, __DIR__ . '/error_log.txt');
            die('Erreur lors de l\'enregistrement de la photo.');
        }
    }
    
    // Traitement de la fiche de poste personnalisée (optionnel)
    $fichePoste = false;
    if (isset($_FILES['fichePoste']) && $_FILES['fichePoste']['error'] === UPLOAD_ERR_OK) {
        $ficheTmpName = $_FILES['fichePoste']['tmp_name'];
        $ficheType = mime_content_type($ficheTmpName);
        if ($ficheType !== 'application/pdf') {
            die('Type de fichier non supporté pour la fiche de poste.');
        }
        $functionsDir = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/postes/';
        if (!is_dir($functionsDir)) {
            if (!mkdir($functionsDir, 0777, true)) {
                die('Erreur lors de la création du dossier postes.');
            }
        }
        $ficheFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
        $ficheTarget = $functionsDir . $ficheFileName;
        if (!move_uploaded_file($ficheTmpName, $ficheTarget)) {
            die('Erreur lors de l\'enregistrement de la fiche de poste.');
        }
        $fichePoste = true;
    }
    
    // Création du fichier XML dans le dossier agents
    $agentsDir = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/agents/';
    if (!is_dir($agentsDir)) {
        if (!mkdir($agentsDir, 0777, true)) {
            die('Erreur lors de la création du dossier agents.');
        }
    }
    $xmlFile = $agentsDir . pathinfo($fileName, PATHINFO_FILENAME) . '.xml';
    
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xmlContent .= "<agent>\n";
    $xmlContent .= "  <prenom>" . htmlspecialchars($prenom, ENT_XML1, 'UTF-8') . "</prenom>\n";
    $xmlContent .= "  <nom>" . htmlspecialchars($nom, ENT_XML1, 'UTF-8') . "</nom>\n";
    $xmlContent .= "  <rattachement>" . htmlspecialchars($rattachement, ENT_XML1, 'UTF-8') . "</rattachement>\n";
    $xmlContent .= "  <poste>" . htmlspecialchars($poste, ENT_XML1, 'UTF-8') . "</poste>\n";
    $xmlContent .= "  <dateArrivee>" . htmlspecialchars($dateArrivee, ENT_XML1, 'UTF-8') . "</dateArrivee>\n";
    $xmlContent .= "  <fichePoste>" . $fichePoste . "</fichePoste>\n";
    $xmlContent .= "</agent>\n";
    
    if (file_put_contents($xmlFile, $xmlContent) === false) {
        die('Erreur lors de l\'enregistrement du fichier XML.');
    }
    
    echo 'Fiche agent enregistrée avec succès.';
} else {
    echo 'Méthode non autorisée.';
}
?>
