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

    $ref = isset($data['ref']) ? $data['ref'] : time();
    $service = isset($data['service']) ? $data['service'] : '';
    $agent = isset($data['agent']) ? $data['agent'] : '';
    $email = isset($data['email']) ? $data['email'] : '';
    $date = isset($data['date']) ? $data['date'] : '';
    $delivery = isset($data['delivery']) ? $data['delivery'] : '';
    $description = isset($data['description']) ? $data['description'] : '';
    $location = isset($data['location']) ? $data['location'] : '';
    $status = isset($data['status']) ? $data['status'] : 'pending';

    // Validation check
    if (empty($ref) || empty($service) || empty($agent) || empty($email) || empty($date) || empty($delivery) || empty($location) || empty($description)) {
        echo "Certains champs sont vides !";
        exit;
    }

    $xmlFile = "../../interventions/" . $ref . ".xml";

    $xml = new SimpleXMLElement('<intervention/>');
    $applicant = $xml->addChild('applicant');
    $applicant->addChild('service', $service);
    $applicant->addChild('name', $agent);
    $applicant->addChild('email', $email);
    $applicant->addChild('date', $date);

    $object = $xml->addChild('object');
    $object->addChild('description', $description);
    $object->addChild('delivery', $delivery);
    $object->addChild('location', $location);
    $object->addChild('status', $status);

    if ($xml->asXML($xmlFile)) {
        echo $ref;
    } else {
        echo "Erreur lors de l'enregistrement de la demande !";
    }
} else {
    echo "Méthode de requête invalide !";
}
?>
