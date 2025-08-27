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

//Custom Error Function
function error_function($error_level,$error_message)
{
	echo '<script type="text/javascript">';
	echo 'alert("['.$error_level.'] '.$error_message.'");';
	echo '</script>';
	die;
}
	
$action = $_GET["action"];
if($action=="save"){ SavePreset(); }
elseif($action=="transfer"){ TransferData(); }
elseif($action=="delete"){ DeleteData(); }
elseif($action=="clear"){ ClearRam(); }

function GennerateFileName($folder){
	//Make a list of existing files
	$dir = opendir ($folder);
	while ($liste = readdir ($dir)){ if (strpos($liste, '.txt') == true) { $file_list[]=basename($liste); }}
	closedir ($dir);    
	unset($liste);
	
	//Generate New File Name
	$file = ""; $i=0;
	while ( $i < 4 )
		{
		$randomdigit = mt_rand(0, 9);
		$file.= $randomdigit;
		$i++;
	}
	
	//If New File Name doesn't already exist -> send or redo
	if(!in_array($file, $file_list)) return $file;
	else GennerateFileName($folder); //Redo  
}

function SavePreset(){
	$ref = $_POST["ref"];
	$type = $_POST["type"];
	$url = $_POST["url"];

	//Create REF if it doesn't exist
	if(!is_dir("../../data/".$ref)){
		mkdir("../../data/".$ref."/"."names/", 0777, true);
		mkdir("../../data/".$ref."/"."chapters/", 0777, true);
		mkdir("../../data/".$ref."/"."streams/", 0777, true);
		mkdir("../../data/".$ref."/"."banners/", 0777, true);
		$url = "../../apps/controls.php?collection=".$ref;
	}
	
	//Save Lower Third
	if($type == "LowerThird"){
		$folder = "../../data/".$ref."/"."names/";
		$file = GennerateFileName($folder);
		$file_url = $folder.$file.".txt";
		$message = "Votre participant : ".$_POST["name"];
	//Generate Content Saved
	$content = strip_tags($_POST["name"])."\r\n".strip_tags($_POST["function"],"<br>")."\r\n".strip_tags($_POST["translation"],"<br>");
	}

	//Save Side Messages
	elseif($type == "ChapterTransition"){
		$folder = "../../data/".$ref."/"."chapters/";
		$file = GennerateFileName($folder);
		$file_url = $folder.$file.".txt";
		$message = "Votre titre de chapitre";
	//Generate Content Saved
	/*$content = strip_tags($_POST["note"],['i','b','br','h1','h2','h3']); //PHP 7.4.0 and above*/
	$content = strip_tags($_POST["chapter"],"<h1>|<h2>|<h3>|<i>|<b>|<br>"); //for older PH versions
	}
	
	//Save Stream Info
	if($type == "StreamInfo"){
		$folder = "../../data/".$ref."/"."streams/";
		$file = GennerateFileName($folder);
		$file_url = $folder.$file.".txt";
		$message = "Votre évènement";
	//Generate Content Saved
	$Title = strip_tags($_POST["title"],'<br>|<sup>');
	$SubTitle = strip_tags($_POST["subtitle"],'<br>|<sup>');
	$Date = $_POST["date"];
	$Style = $_POST["style"];
	$content = $Title."\r\n".$SubTitle."\r\n".$Date."\r\n".$Style;
	}
	
	//Save Scrolling Banner
	if($type == "ScrollingBanner"){
		$folder = "../../data/".$ref."/"."banners/";
		$file = GennerateFileName($folder);
		$file_url = $folder.$file.".txt";
		$message = "Votre bandeau";
	//Generate Content Saved
	$content = strip_tags($_POST["message"],"<i>|<b>")."\r\n".$_POST["class"];
	}
	
	//Save Content
	$open = fopen($file_url, "w");
	if(fwrite($open,$content)){ $message.= ' a bien été sauvegardé dans la collection '.'\"'.$ref.'\"'.' ('.$file.').'; $Reload = 'parent.window.location.assign("'.$url.'");'; }
	else{ $message = 'Erreur lors de la sauvegarde dans la collection '.'\"'.$ref.'\"'.".".'\r\n'.'Merci de recommencer...'; }
	fclose($open);

	echo '<script type="text/javascript">';
	echo 'alert("'.$message.'");';
	echo $Reload;
	echo '</script>';
}

function TransferData(){
	$key = $_POST["key"];
	$type = $_POST["type"];
	$stamp = $_POST["stamp"];
	$id = $_POST["id"];
	$content = $_POST["content"];

	//Transfer Note
	if($type == "PublicMessages" || $type == "InternalMessages"){
		$ram_file = "../../ram/".$key.".txt";
	//Generate Content Saved
	$content = $id."|".$stamp."|".$content;
	}
		
	//Save Content
	unlink($ram_file);
	$open = fopen($ram_file, "w");
	if(fwrite($open,$content)){
		//reformat to avoid errors
		$content = addslashes($content);
		$message = 'Saved Content: '.$content;
	}
	else{ $message = 'Error saving RAM '.$key.'.'; }
	fclose($open);
	
	//Set File Permissions
	chmod($ram_file, 0777);

	echo '<script type="text/javascript">';
	echo 'console.log ("'.$message.'");';
	echo 'parent.window.location.assign("../../apps/messages.php?str='.$key.'")';
	echo '</script>';
}

function DeleteData(){
	$ref = $_POST["ref"];
	
	//If PRESET
	if(!empty($_POST["file"]) && !empty($_POST["type"])){
		$type = $_POST["type"];
		$file = $_POST["file"];
		
		//Set folder depending on type
		if($type == "LowerThird"){ $type = 'names'; }
		elseif($type == "StreamInfo"){ $type = 'streams'; }
		elseif($type == "ScrollingBanner"){ $type = 'banners'; }
		elseif($type == "ChapterTransition"){ $type = 'chapters'; }

		$path = "../../data/".$ref."/".$type."/".$file.".txt";
		
		if(file_exists($path)){
			if (unlink($path)){ $message = 'Votre sauvegarde N°'.$file.' a bien été effacée.'; }
			else{ $message = 'Erreur : votre sauvegarde N°'.$file.' n\'a pas pu être effacée.'; }
		}
		else{ $message = 'La sauvegarde N°'.$file.' n\'exist pas.'; }
	}
	//If COLLECTION
	else{
		$path = "../../data/".$ref;
		DeleteCollection($path);
		$message = 'La collection \"'.$ref.'\" a bien été effacée.';
		$ref = 'ENPJJ';
	}

	echo '<script type="text/javascript">';
	echo 'alert("'.$message.'");';
	echo 'parent.window.location.assign("../../apps/controls.php?collection='.$ref.'");';
	echo '</script>';
}

function DeleteCollection($target){
	
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