<?php
// /organigramme/src/php/organize.php

session_start();

$organigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichierTxt'])) {
    if (!file_exists($organigrammePath)) {
        $ancienOrganigramme = [];
    } else {
        $ancienOrganigramme = json_decode(file_get_contents($organigrammePath), true);
    }
    
    $fichierTxt = $_FILES['fichierTxt']['tmp_name'];
    $contenuTxt = file_get_contents($fichierTxt);
    
    $nouvelOrganigramme = convertirTxtEnJson($contenuTxt);
    
    $tempOrganigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme_temp.json';
    file_put_contents($tempOrganigrammePath, json_encode(['groups' => $nouvelOrganigramme], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $anciensNomsGroupes = obtenirListeGroupes($ancienOrganigramme);
    $nouveauxNomsGroupes = obtenirListeGroupes(['groups' => $nouvelOrganigramme]);
    $groupesSupprimes = array_diff($anciensNomsGroupes, $nouveauxNomsGroupes);
    
    if (empty($groupesSupprimes)) {
        rename($tempOrganigrammePath, $organigrammePath);
        header('Location: /organigramme/manager.php?message=Organigramme mis à jour avec succès.');
        exit();
    } else {
        $_SESSION['ancienGroupes'] = $ancienOrganigramme;
        $_SESSION['nouveauOrganigramme'] = ['groups' => $nouvelOrganigramme];
        $_SESSION['groupesSupprimes'] = $groupesSupprimes;
        header('Location: /organigramme/mapping.php');
        exit();
    }
} else {
    header('Location: /organigramme/manager.php?message=Erreur lors de la mise à jour.');
    exit();
}

function convertirTxtEnJson($contenuTxt) {
    $lignes = explode("\n", $contenuTxt);
    $stack = [];
    $root = [];
    foreach ($lignes as $ligne) {
        $ligne = rtrim($ligne, "\r\n");
        $indentation = 0;
        // Compter les tabulations en début de ligne pour déterminer le niveau
        while (substr($ligne, 0, 1) === "\t") {
            $indentation++;
            $ligne = substr($ligne, 1);
        }
        $ligne = trim($ligne);
        if ($ligne === '') continue;
        
        // Traitement des lignes de "job" (commençant par "-")
        if (substr($ligne, 0, 1) === '-') {
            $jobLine = ltrim(substr($ligne, 1));
            if (preg_match('/^(.*)\s*\(([^)]+)\)$/', $jobLine, $matches)) {
                $jobName = trim($matches[1]);
                $jobCode = genererCodeJob(trim($matches[2]));
            } else {
                $jobName = $jobLine;
                $jobCode = genererCodeJob($jobName);
            }
            $job = [
                'job'   => $jobName,
                'code'  => $jobCode,
                'level' => strval($indentation)
            ];
            // Rattacher le job au groupe courant, c'est-à-dire le dernier groupe ajouté dans le stack
            $currentGroupIndex = count($stack) - 1;
            if ($currentGroupIndex < 0) {
                // Aucun groupe n'a été défini, créer un groupe par défaut
                $dummy = [
                    'group'     => 'ENPJJ',
                    'code'      => 'ENPJJ',
                    'level'     => '0',
                    'jobs'      => [],
                    'subgroups' => []
                ];
                $root[] = $dummy;
                $stack[0] = &$root[count($root) - 1];
                $currentGroupIndex = 0;
            }
            $stack[$currentGroupIndex]['jobs'][] = $job;
        } else {
            // Traitement des lignes de "group"
            if (preg_match('/^(.*)\s*\(([^)]+)\)$/', $ligne, $matches)) {
                $groupName = trim($matches[1]);
                $groupCode = trim($matches[2]);
            } else {
                $groupName = $ligne;
                $groupCode = '';
            }
            $group = [
                'group'     => $groupName,
                'code'      => $groupCode,
                'level'     => strval($indentation),
                'jobs'      => [],
                'subgroups' => []
            ];
            if ($indentation == 0) {
                $root[] = $group;
                $stack[0] = &$root[count($root) - 1];
            } else {
                if (isset($stack[$indentation - 1])) {
                    $stack[$indentation - 1]['subgroups'][] = $group;
                    $stack[$indentation] = &$stack[$indentation - 1]['subgroups'][count($stack[$indentation - 1]['subgroups']) - 1];
                }
            }
            $stack = array_slice($stack, 0, $indentation + 1);
        }
    }
    return $root;
}

function obtenirListeGroupes($data, $prefixe = '') {
    $nomsGroupes = [];
    if (isset($data['groups'])) {
        foreach ($data['groups'] as $groupe) {
            // On combine le nom et le level pour que la comparaison détecte également les différences de niveau.
            $nomComplet = $prefixe ? $prefixe . ' > ' . $groupe['group'] . '|' . $groupe['level'] 
                                    : $groupe['group'] . '|' . $groupe['level'];
            $nomsGroupes[] = $nomComplet;
            if (!empty($groupe['subgroups'])) {
                $nomsGroupes = array_merge($nomsGroupes, obtenirListeGroupes(['groups' => $groupe['subgroups']], $nomComplet));
            }
        }
    } else {
        foreach ($data as $groupe) {
            $nomComplet = $prefixe ? $prefixe . ' > ' . $groupe['group'] . '|' . $groupe['level'] 
                                    : $groupe['group'] . '|' . $groupe['level'];
            $nomsGroupes[] = $nomComplet;
            if (!empty($groupe['subgroups'])) {
                $nomsGroupes = array_merge($nomsGroupes, obtenirListeGroupes(['groups' => $groupe['subgroups']], $nomComplet));
            }
        }
    }
    return $nomsGroupes;
}

function genererCodeJob($jobName) {
    // Convertir en minuscules
    $jobName = mb_strtolower($jobName, 'UTF-8');
    
    // Remplacer les apostrophes typographiques par l'apostrophe simple
    $jobName = str_replace(array("’", "‘"), "'", $jobName);
    
    // Supprimer les contractions "l'" et "d'" en début de mot
    $jobName = preg_replace("/\b[lL]'(?=\p{L})/u", "", $jobName);
    $jobName = preg_replace("/\b[dD]'(?=\p{L})/u", "", $jobName);
    
    // Convertir les caractères accentués en leur équivalent non accentué
    $jobName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $jobName);
    
    // Liste des mots de liaison à ignorer
    $stopWords = array('de', 'la', 'des', 'et', 'du', 'à', 'le', 'les');
    
    // Découper la chaîne en mots
    $words = preg_split('/\s+/', $jobName);
    $resultWords = array();
    
    foreach ($words as $word) {
        // Autoriser les lettres, les underscores et le slash (pour conserver d'éventuels underscores déjà présents)
        $word = preg_replace('/[^a-zA-Z_\/]/', '', $word);
        
        // Si le mot contient un slash, prendre uniquement la partie avant le slash
        if (strpos($word, '/') !== false) {
            $parts = explode('/', $word);
            $word = $parts[0];
        }
        
        // Ignorer les mots de liaison
        if (in_array($word, $stopWords)) {
            continue;
        }
        
        if (!empty($word)) {
            $resultWords[] = $word;
        }
    }
    
    if (empty($resultWords)) {
        return "";
    }
    
    $code = implode('_', $resultWords);
    return mb_strtoupper($code, 'UTF-8');
}
?>
