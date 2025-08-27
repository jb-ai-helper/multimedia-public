<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    // Decode the JSON data
    $data = json_decode($rawData, true);

    if ($data === null) {
        echo "Invalid JSON data!";
        exit;
    }

    $ref = isset($data['ref']) ? $data['ref'] : '';
    $type = isset($data['type']) ? $data['type'] : '';

    if (empty($ref) || empty($type)) {
        echo "Ref or type is missing!";
        exit;
    }

    $boss_key = file_get_contents('src/keys/boss.txt');
    $team_key = file_get_contents('src/keys/team.txt');

    if ($type === 'boss') {
        $key = $boss_key;
    } elseif ($type === 'team') {
        $key = $team_key;
    } else {
        echo "Invalid type!";
        exit;
    }

    $token = hash('sha256', $key . $ref);
    echo $token;
} else {
    echo "Méthode de requête invalide !";
}
?>
