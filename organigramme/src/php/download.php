<?php
// /organigramme/src/php/download.php

$organigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme.json';
if (!file_exists($organigrammePath)) {
    die("Organigramme non trouvé.");
}
$jsonContent = file_get_contents($organigrammePath);
$data = json_decode($jsonContent, true);
if ($data === null) {
    die("Erreur de décodage JSON.");
}
$groups = isset($data['groups']) ? $data['groups'] : [];

function jsonToTxt($groups) {
    $txt = "";
    foreach ($groups as $group) {
        // Utilisation du champ 'level' pour définir l'indentation
        $groupIndent = str_repeat("\t", intval($group['level']));
        $line = $groupIndent . $group['group'];
        if (!empty($group['code'])) {
            $line .= " (" . $group['code'] . ")";
        }
        $txt .= $line . "\n";
        if (!empty($group['jobs'])) {
            foreach ($group['jobs'] as $job) {
                $jobIndent = str_repeat("\t", intval($job['level']));
                $txt .= $jobIndent . "-" . $job['job'] . " (" . $job['code'] . ")\n";
            }
        }
        if (!empty($group['subgroups'])) {
            $txt .= jsonToTxt($group['subgroups']);
        }
    }
    return $txt;
}

$output = jsonToTxt($groups);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="organigramme.txt"');
echo $output;
?>
