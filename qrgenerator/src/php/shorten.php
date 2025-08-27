<?php
//Include Common Functions
require '../../../src/php/commontools.php';
    
$ref = generateFileName("../../../liens/data/");
$file_path = "../../../liens/data/".$ref.".txt";

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$link = $data['link'];

$file = fopen($file_path, "w");

if($file) {
    fwrite($file, $link);
    echo "qr.enpjj.fr/".$ref;
}
else{ echo "Error creating link!"; 
}
    
?>
