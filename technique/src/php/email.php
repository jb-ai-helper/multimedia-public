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
        error_log("Invalid JSON data!");
        exit;
    }

    $to = isset($data['to']) ? $data['to'] : '';
    $from = isset($data['from']) ? $data['from'] : '';
    $subject = isset($data['subject']) ? $data['subject'] : '';
	$encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $content = isset($data['content']) ? $data['content'] : '';

    // Validate email addresses
    if (empty($to) || empty($from) || empty($subject) || empty($content)) {
        echo "Tous les champs sont obligatoires !";
        error_log("Tous les champs sont obligatoires !");
        exit;
    }

    // Headers for email
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "Content-type: text/html\r\n";

    // Send email
    if (mail($to, $encoded_subject, $content, $headers)) {
        echo "Email envoyé avec succès à " . $to;
    } else {
        echo "Erreur lors de l'envoi de l'email à " . $to;
    }
} else {
    echo "Méthode de requête invalide !";
}
?>
