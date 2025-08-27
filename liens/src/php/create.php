<?php
//Include Common Functions
require '../../../src/php/commontools.php';
    
$ref = generateFileName("../../data/");
$link = $_POST["link"];
$file_path = "../../data/".$ref.".txt";

$file = fopen($file_path, "w");

if($file) {
    if (fwrite($file, $link)) {
        echo 'success';
    } else {
        echo 'Impossible d\'inscrire le lien pour cette référence !';
    }
    $message = 'Votre lien a bien été créé !';
}
else{ echo 'Impossible de créer les données dans la base !';
}
?>