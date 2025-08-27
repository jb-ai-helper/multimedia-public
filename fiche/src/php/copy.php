<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Request Manager</title>
</head>

<body>
<?php

//Include Common Functions
require '../../../src/php/commontools.php';
    
$oldref = $_POST["oldref"];
$old_file = "../../events/".$oldref.".xml";
$newref = $_POST["newref"];
$new_file = "../../events/".$newref.".xml";
//Load Old REF

//Copy Old REF to New REF
if(copy($old_file, $new_file)) { 
    //Change Shooting Date to Now
    $Now = date('Y-m-d');
    $NewXML = simplexml_load_file($new_file);
    $NewXML->video->date = $Now;
    $NewXML->event->date = $Now;
    //Save Changes
    if($NewXML->event->date == $Now && $NewXML->saveXML($new_file)) { $message = 'Votre demande de prestation multimédia a bien été dupliquée.\r\nNotez bien son nouveau numéro ('.$newref.') afin de pouvoir y accéder ultérieurement.'; 
    }
    else{ $message = 'Votre demande de prestation multimédia a bien été dupliquée,\r\nmais la date n\'a pas pu être changée.\r\nSi celle-ci est déjà passée, vous la retrouverez avec les demandes archivées.'; 
    }
    $reload = 'parent.window.location.assign("/fiche/?ref='.$newref.'");';
}
else{
    $message = 'Erreur lors de la duplication de votre demande de prestation multimédia.\r\n\r\n'.'Merci de recommencer...';
    $reload = 'parent.ClearSaveForm();';
}

echo '<script type="text/javascript">';
echo 'alert("'.$message.'");';
echo $reload;
echo '</script>';
    
?>
</body>
</html>
