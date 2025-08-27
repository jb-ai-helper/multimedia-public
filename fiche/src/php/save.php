<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Save Request</title>
</head>

<body>
<?php

//Include Common Functions
require '../../../src/php/commontools.php';
    
$ref = $_POST["ref"];
$XML = $_POST["xml"];
$XML_file = "../../events/".$ref.".xml";

//Format XLM Correctly
$xml_string = simplexml_load_string($XML);
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml_string->asXML());
//Save formated XLM
$xml_tree = new SimpleXMLElement($dom->saveXML());
if($xml_tree->saveXML($XML_file)) {
    $message = 'Votre demande de prestation multimédia a bien été sauvegardée.\r\nNotez bien son numéro ('.$ref.') afin de pouvoir y accéder ultérieurement.';
    //$message.= '\r\n\r\nSi votre demande est complète, vous pouvez l\'envoyer au service multimédia en cliquant sur \"Envoyer\" dans le menu en haut à gauche.';
    $reload = 'parent.window.location.assign("/fiche/?ref='.$ref.'");';
}
else{
    $message = 'Erreur lors de la sauvegarde de votre demande de prestation multimédia.\r\n\r\n'.'Merci de recommencer...';
    $reload = 'parent.ClearSaveForm();';
}
echo '<script type="text/javascript">';
echo 'alert("'.$message.'");';
echo $reload;
echo '</script>';
    
?>
</body>
</html>
