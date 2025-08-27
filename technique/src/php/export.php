<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=interventions.csv');

$csvOutput = fopen('php://output', 'w');

// Add BOM to fix UTF-8 in Excel
fprintf($csvOutput, chr(0xEF).chr(0xBB).chr(0xBF));

// Output the column headings
fputcsv($csvOutput, ['Creation Date', 'Service', 'Name', 'Email', 'Location', 'Description', 'Delivery Date', 'Status']);

$files = glob('../../interventions/*.xml');

foreach ($files as $file) {
    $xml = simplexml_load_file($file);

    $date = (string)$xml->applicant->date;
    $service = (string)$xml->applicant->service;
    $name = (string)$xml->applicant->name;
    $email = (string)$xml->applicant->email;
    $location = (string)$xml->object->location;
    $description = (string)$xml->object->description;
    $delivery = (string)$xml->object->delivery;
    $status = (string)$xml->object->status;
    
    // Replace new lines in text nodes with space
    $description = str_replace(["\r", "\n"], ' ', $description);
    
    fputcsv($csvOutput, [$date, $service, $name, $email, $location, $description, $delivery, $status]);
}

fclose($csvOutput);
?>
