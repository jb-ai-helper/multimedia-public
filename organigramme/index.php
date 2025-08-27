<?php
// /organigramme/index.php

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Include Common Functions
require '../src/php/commontools.php';

// Charger le fichier organigramme.json
$organigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme.json';
if (!file_exists($organigrammePath)) {
    die('Le fichier organigramme.json est introuvable.');
}
$jsonContent = file_get_contents($organigrammePath);
$jsonData = json_decode($jsonContent, true);
if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
    die('Erreur de décodage JSON : ' . json_last_error_msg());
}
// On suppose que le JSON contient une clé "groups"
$groups = isset($jsonData['groups']) ? $jsonData['groups'] : [];

// Récupération des fonctions
$functions = [];
function addJobs(array $groupe, array &$functions) {
    if (isset($groupe['jobs'])) {
        foreach ($groupe['jobs'] as $job) {
            $functions[$job['code']] = $job['job'];
        }
    }
    if (isset($groupe['subgroups']) && is_array($groupe['subgroups'])) {
        foreach ($groupe['subgroups'] as $subgroup) {
            addJobs($subgroup, $functions);
        }
    }
}
foreach ($jsonData['groups'] as $groupe) {
    addJobs($groupe, $functions);
}

// Met des espaces insécables devant les mots de liaison (moins de 6 lettres) pour des césures correctes
function SafeCesure($texte) {
    return preg_replace('/(\b\p{L}{1,6}\b)\s+/u', '$1&nbsp;', $texte);
}

// Fonction récursive pour construire l'arborescence des groupes
function BuildGroupTree($groups) {
    $tree = [];
    foreach ($groups as $group) {
        $node = [
            'group'     => $group['group'],
            'code'      => $group['code'],
            'subgroups' => isset($group['subgroups']) ? BuildGroupTree($group['subgroups']) : [],
            'agents'    => [] // On remplira cette clé avec les agents
        ];
        $tree[$group['code']] = $node;
    }
    return $tree;
}
$groupTree = BuildGroupTree($groups);

// Parcourir le dossier des agents pour ajouter les agents aux groupes correspondants
$agentsDir = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/agents/';
$xmlFiles = glob($agentsDir . '*.xml');
foreach ($xmlFiles as $xmlFile) {
    $xml = simplexml_load_file($xmlFile);
    if ($xml) {
        $prenom = (string)$xml->prenom;
        $nom = (string)$xml->nom;
        $rattachement = (string)$xml->rattachement;
        $poste = (string)$xml->poste;
        $function = $functions[$poste];
        $display = $prenom . ' ' . mb_strtoupper($nom, 'UTF-8');
        $photo = '../trombinoscope/photos/' . nettoyerChaine($nom) . '_' . nettoyerChaine($prenom) . '.jpg';
        
        // Deal with non-existing photos
        if(!file_exists($photo)){ $photo = '../trombinoscope/src/img/placeholder.svg'; }
        
        $agent = [
            'prenom' => $prenom,
            'nom' => $nom,
            'photo' => $photo,
            'display' => $display,
            'function' => $function,
        ];
        // Ajouter l'agent au groupe correspondant
        AddAgent($groupTree, $rattachement, $agent);
    }
}

// Fonction pour associer une fiche de poste à un agent
function getFicheAgent($agent) {
    $dossier = __DIR__ . '/postes';
    if (!is_dir($dossier)) {
        return false;
    }
    $fichiers = scandir($dossier);
    $agentID = nettoyerChaine($agent['nom']) . '_' . nettoyerChaine($agent['prenom']);
    $agentLower = strtolower($agentID);
    $agentNormalise = str_replace(['_', '-'], ' ', $agentLower);
    foreach ($fichiers as $fichier) {
        if ($fichier === '.' || $fichier === '..') {
            continue;
        }
        $fichierLower = strtolower($fichier);
        $fichierNormalise = str_replace(['_', '-'], ' ', $fichierLower);
        if (strpos($fichierNormalise, $agentNormalise) !== false) {
            return "postes/" . $fichier;
        }
    }
    return "#";
}

// Fonction récursive pour ajouter un agent à un groupe dans l'arborescence
function AddAgent(&$tree, $rattachement, $agent) {
    foreach ($tree as &$node) {
        if ($node['code'] === $rattachement) {
            $node['agents'][] = $agent;
            return true;
        }
        if (!empty($node['subgroups'])) {
            if (AddAgent($node['subgroups'], $rattachement, $agent)) {
                return true;
            }
        }
    }
    return false;
}

// Nouvelle fonction de rendu de l'organigramme avec gestion du groupe racine
function RenderOrganigramme($tree, $indent = 0, $isRoot = false) {
    $html = "";
    if ($isRoot) {
        // On suppose que $tree contient au moins un élément ; on prend le premier groupe comme racine.
        foreach ($tree as $node) {
            $tab = str_repeat("\t", $indent+3);
            $html .= $tab . '<div id="root" class="group central">' . "\n";
            // Pas de div "name" pour le groupe racine
            // Afficher les agents du groupe racine
            if (!empty($node['agents'])) {
                foreach ($node['agents'] as $agent) {
                    $ficheAgent = getFicheAgent($agent);
                    $html .= $tab . "\t<div class=\"agent start\" style=\"background-image: url('" . htmlspecialchars($agent['photo']) . "')\">";
                    $html .= "<a target=\"_blank\" href=\"" . $ficheAgent . "\"><span>" 
                           . htmlspecialchars($agent['display']) . "<br /><i>" 
                           . SafeCesure(htmlspecialchars($agent['function'])) . "</i></span></a>";
                    $html .= "</div>\n";
                }
            }
            // Afficher les sous-groupes du groupe racine avec rendu standard
            if (!empty($node['subgroups'])) {
                $html .= RenderOrganigramme($node['subgroups'], $indent + 1, false);
            }
            $html .= $tab . '</div>' . "\n";
            break; // On ne traite que le premier groupe comme racine
        }
    } else {
        $tab = str_repeat("\t", $indent+3);
        foreach ($tree as $node) {
            $html .= $tab . '<div class="group start">' . "\n";
            $html .= $tab . "\t<div class=\"name\" data-short=\"" . htmlspecialchars($node['code']) . "\"><span>" 
                   . SafeCesure(htmlspecialchars($node['group'])) . "</span></div>\n";
            if (!empty($node['agents'])) {
                foreach ($node['agents'] as $agent) {
                    $ficheAgent = getFicheAgent($agent);
                    $html .= $tab . "\t<div class=\"agent start\" style=\"background-image: url('" . htmlspecialchars($agent['photo']) . "')\">";
                    $html .= "<a target=\"_blank\" href=\"" . $ficheAgent . "\"><span>" 
                           . htmlspecialchars($agent['display']) . "<br /><i>" 
                           . SafeCesure(htmlspecialchars($agent['function'])) . "</i></span></a>";
                    $html .= "</div>\n";
                }
            }
            if (!empty($node['subgroups'])) {
                $html .= RenderOrganigramme($node['subgroups'], $indent + 1, false);
            }
            $html .= $tab . '</div>' . "\n";
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Organigramme de l'ENPJJ</title>
    <link rel="stylesheet" href="src/css/organigramme.css">
    <script language="JavaScript" src="src/js/organigramme.js" type="text/javascript"></script>
</head>
<body onLoad="SetUp()">
    <!-- Structure principale, désormais générée par RenderOrganigramme -->
    <div id="organigramme">
        <div onClick="GoBack()" id="back"></div>
        <form name="exportform" method="post" action="src/php/export.php">
            <button id="exporter" type="submit">Exporter</button>
        </form>
        <?php
            // On appelle RenderOrganigramme en mode "root"
            echo RenderOrganigramme($groupTree, 0, true);
        ?>
    </div>
</body>
</html>
