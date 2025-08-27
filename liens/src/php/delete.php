<?php

$ref = isset($_POST["ref"]) ? $_POST["ref"] : '';
$file = '../../data/' . $ref . '.txt';

if (unlink($file)) {
    echo 'success';
} else {
    echo 'Le lien n\'a pas pu être supprimé !';
}
?>
