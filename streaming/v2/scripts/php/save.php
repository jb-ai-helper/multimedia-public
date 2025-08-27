<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Save Presets</title>
</head>

<body>
<?php
//Include Common Functions
require '../../../../src/php/commontools.php';

//Main Function
$str = $_GET["str"];
$ref = $_POST["ref"];
$type = $_POST["type"];
$url = $_POST["url"];

//Create REF if it doesn't exist
if (!is_dir("../../../data/".$ref)) {
    mkdir("../../../data/".$ref."/"."names/", 0777, true);
    mkdir("../../../data/".$ref."/"."chapters/", 0777, true);
    mkdir("../../../data/".$ref."/"."streams/", 0777, true);
    mkdir("../../../data/".$ref."/"."banners/", 0777, true);
    $url = "../../apps/dgc.php?str=".$str."&collection=".$ref;
}

//Save Stream Info
if ($type == "StreamInfo") {
    $folder = "../../../data/".$ref."/streams/";
    $file = generateFileName($folder);
    $file_url = $folder.$file.".txt";
    $message = "Votre évènement";
    $content = formatStreamInfo($_POST);
}
//Save Lower Third
if ($type == "LowerThird") {
    $folder = "../../../data/".$ref."/names/";
    $file = generateFileName($folder);
    $file_url = $folder.$file.".txt";
    $message = 'Votre participant \"'.$_POST["name"].'\"';
    $content = formatLowerThird($_POST);
} elseif ($type == "ChapterTransition") { //Save Chapter
    $folder = "../../../data/".$ref."/chapters/";
    $file = generateFileName($folder);
    $file_url = $folder.$file.".txt";
    $message = "Votre titre de chapitre";
    $content = formatTransition($_POST["chapter"]);
}

//Save Scrolling Banner
if ($type == "ScrollingBanner") {
    $folder = "../../../data/".$ref."/banners/";
    $file = generateFileName($folder);
    $file_url = $folder.$file.".txt";
    $message = "Votre bandeau";
    $content = formatScrollingBanner($_POST);
}

//Save Content
$open = fopen($file_url, "w");
if (fwrite($open, $content)) {
    $message.= ' a bien été sauvegardé dans la collection '.'\"'.$ref.'\"'.' ('.$file.').';
    $reload = 'parent.window.location.assign("'.$url.'");'; 
} else {
    $message = 'Erreur lors de la sauvegarde de '.strtolower($message).' dans la collection '.'\"'.$ref.'\"'.".".'\r\n\r\n'.'Merci de recommencer...'; 
}
fclose($open);

echo '<script type="text/javascript">';
echo 'alert("'.$message.'");';
echo $reload;
echo '</script>';

?>
</body>
</html>
