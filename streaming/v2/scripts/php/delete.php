<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Delete Data</title>
</head>

<body>
<?php
//Include Common Functions
require '../../../../src/php/commontools.php';

//Other Functions
function deleteCollection($target)
{
    if (is_dir($target)) {
        $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned
        foreach ( $files as $file ) {
            deleteCollection($file); 
        }
        rmdir($target);
    } elseif (is_file($target)) {
        unlink($target); 
    }
}

//Main Function
$str = $_GET["str"];
$ref = $_POST["ref"];
$url = "../../apps/dgc.php?str=".$str."&collection=".$ref;

//If PRESET
if (!empty($_POST["file"]) && !empty($_POST["type"])) {
    $type = $_POST["type"];
    $file = $_POST["file"];

    //Set folder depending on type
    if ($type == "LowerThird") {
        $type = 'names'; 
    } elseif ($type == "StreamInfo") {
        $type = 'streams'; 
    } elseif ($type == "ScrollingBanner") {
        $type = 'banners'; 
    } elseif ($type == "ChapterTransition") {
        $type = 'chapters'; 
    }

    $path = "../../../data/".$ref."/".$type."/".$file.".txt";

    if (file_exists($path)) {
        if (unlink($path)) {
            $message = 'Votre sauvegarde N°'.$file.' a bien été supprimée.'; 
        } else {
            $message = 'Erreur : votre sauvegarde N°'.$file.' n\'a pas pu être supprimée.'; 
        }
    } else {
        $message = 'La sauvegarde N°'.$file.' n\'exist pas.'; 
    }
} else {
    //If COLLECTION
    $path = "../../../data/".$ref;
    deleteCollection($path);
    if (is_dir($path)) {
        $message = 'Erreur : La collection \""'.$file.'\"" n\'a pas pu être supprimée.'; 
    } else {
        $message = 'La collection \"'.$ref.'\" a bien été supprimée.'; 
    }
    $ref = 'ENPJJ';
}

//If Erreur
if (preg_match("/Erreur/", $message) > 0) {
    $reload = ""; 
} else {
    $reload = 'parent.window.location.assign("'.$url.'");'; 
}

echo '<script type="text/javascript">';
echo 'alert("'.$message.'");';
echo $reload;
echo '</script>';

?>
</body>
</html>
