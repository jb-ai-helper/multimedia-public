<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Delete Request</title>
</head>

<body>
<?php

//Include Common Functions
require '../../../src/php/commontools.php';
    
$ref = $_POST["ref"];
$file = '../../events/'.$ref.'.xml';

if(unlink($file)) {
    $message = 'La demande n°'.$ref.' a bien été supprimée.';
    $reload = 'parent.location.reload();';
    echo '<script type="text/javascript">';
    echo 'alert("'.$message.'");';
    echo $reload;
    echo '</script>';
}
else{
    $message = 'Erreur lors de la suppression de la demande de prestation multimédia.\r\n\r\n'.'Merci de recommencer...';
    $reload = 'parent.location.reload();';
    echo '<script type="text/javascript">';
    echo 'alert("'.$message.'");';
    echo $reload;
    echo '</script>';
}
    
?>
</body>
</html>
