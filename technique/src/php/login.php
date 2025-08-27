<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    // Decode the JSON data
    $data = json_decode($rawData, true);

    if ($data === null) {
        echo json_encode(["success" => false, "message" => "Invalid JSON data!"]);
        exit;
    }

    $ref = isset($data['ref']) ? $data['ref'] : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if (empty($ref) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Référence ou mot de passe manquant !"]);
        exit;
    }

    // Load the keys
    $boss_key = file_get_contents('../keys/boss.txt');
    $team_key = file_get_contents('../keys/team.txt');

    $token = '';
    if ($password === $boss_key) {
        $token = hash('sha256', $boss_key . $ref);
    } elseif ($password === $team_key) {
        $token = hash('sha256', $team_key . $ref);
    } else {
        echo json_encode(["success" => false, "message" => "Clé d'identification incorrecte !"]);
        exit;
    }

    echo json_encode(["success" => true, "token" => $token]);
} else {
    echo json_encode(["success" => false, "message" => "Méthode de requête invalide !"]);
}
?>
