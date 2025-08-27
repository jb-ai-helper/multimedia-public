<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Manage Presets</title>
</head>

<body>
<?php
//Include Common Functions
include('../../../../src/php/commontools.php');

//Get Action Type
$action = $_GET["action"];
if($action=="save"){ SavePreset(); }
elseif($action=="delete"){ DeleteData(); }

function SavePreset(){

	$str = $_GET["str"];
	$ref = $_POST["ref"];
	$type = $_POST["type"];
	$url = $_POST["url"];

	//Create REF if it doesn't exist
	if(!is_dir("../../../data/".$ref)){
		mkdir("../../../data/".$ref."/"."names/", 0777, true);
		mkdir("../../../data/".$ref."/"."chapters/", 0777, true);
		mkdir("../../../data/".$ref."/"."streams/", 0777, true);
		mkdir("../../../data/".$ref."/"."banners/", 0777, true);
		$url = "../../apps/dgc.php?str=".$str."&collection=".$ref;
	}
	
	//Save Stream Info
	if($type == "StreamInfo"){
		$folder = "../../../data/".$ref."/streams/";
        $file = generateFileName($folder);
        $file_url = $folder.$file.".txt";
		$message = "Votre évènement";
        $content = FormatStreamInfo($_POST);
	}
	//Save Lower Third
	if($type == "LowerThird"){
		$folder = "../../../data/".$ref."/names/";
		$file = generateFileName($folder);
		$file_url = $folder.$file.".txt";
		$message = 'Votre participant \"'.$_POST["name"].'\"';
        $content = formatLowerThird($_POST);
	}

	//Save Chapter
	elseif($type == "ChapterTransition"){
        $folder = "../../../data/".$ref."/chapters/";
        $file = generateFileName($folder);
        $file_url = $folder.$file.".txt";
        $message = "Votre titre de chapitre";
        $content = formatTransition($_POST["chapter"]);
	}
	
	//Save Scrolling Banner
	if($type == "ScrollingBanner"){
		$folder = "../../../data/".$ref."/banners/";
		$file = generateFileName($folder);
		$file_url = $folder.$file.".txt";
		$message = "Votre bandeau";
        $content = formatScrollingBanner($_POST);
	}
	
	//Save Content
	$open = fopen($file_url, "w");
	if(fwrite($open,$content)){
		$message.= ' a bien été sauvegardé dans la collection '.'\"'.$ref.'\"'.' ('.$file.').';
		$reload = 'parent.window.location.assign("'.$url.'");'; }
	else{ $message = 'Erreur lors de la sauvegarde de '.strtolower($message).' dans la collection '.'\"'.$ref.'\"'.".".'\r\n\r\n'.'Merci de recommencer...'; }
	fclose($open);
	
	echo '<script type="text/javascript">';
	echo 'alert("'.$message.'");';
	echo $reload;
	echo '</script>';
}

function DeleteData()
{
	$str = $_GET["str"];
	$ref = $_POST["ref"];
	$url = "../../apps/dgc.php?str=".$str."&collection=".$ref;
	
	//If PRESET
	if(!empty($_POST["file"]) && !empty($_POST["type"])){
		$type = $_POST["type"];
		$file = $_POST["file"];
		
		//Set folder depending on type
		if($type == "LowerThird"){ $type = 'names'; }
		elseif($type == "StreamInfo"){ $type = 'streams'; }
		elseif($type == "ScrollingBanner"){ $type = 'banners'; }
		elseif($type == "ChapterTransition"){ $type = 'chapters'; }

		$path = "../../../data/".$ref."/".$type."/".$file.".txt";
		
		if(file_exists($path)){
			if (unlink($path)){ $message = 'Votre sauvegarde N°'.$file.' a bien été supprimée.'; }
			else{ $message = 'Erreur : votre sauvegarde N°'.$file.' n\'a pas pu être supprimée.'; }
		}
		else{ $message = 'La sauvegarde N°'.$file.' n\'exist pas.'; }
	}
	
	//If COLLECTION
	else{
		$path = "../../../data/".$ref;
		DeleteCollection($path);
		if(is_dir($path)){ $message = 'Erreur : La collection \""'.$file.'\"" n\'a pas pu être supprimée.'; }
		else{ $message = 'La collection \"'.$ref.'\" a bien été supprimée.'; }
		$ref = 'ENPJJ';
	}
	
	//If Erreur
	if(preg_match("/Erreur/", $message) > 0){ $reload = ""; }
	else{ $reload = 'parent.window.location.assign("'.$url.'");'; }
	
	echo '<script type="text/javascript">';
	echo 'alert("'.$message.'");';
	echo $reload;
	echo '</script>';
}

function DeleteCollection($target)
{
	if(is_dir($target)){
		$files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
		foreach( $files as $file ){ DeleteCollection( $file ); }
		rmdir( $target );
	}
	elseif(is_file($target)) { unlink( $target ); }
}

?>
</body>
</html>