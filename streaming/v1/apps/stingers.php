<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="../src/css/stingers.css">
	<title>Animated Stinger</title>
</head>
<body>
<?php
    //Get Auto Title & Collection
	if(!empty($_GET['str'])){ $Stream = $_GET['str']; } else{ $Stream = ''; }
	$_GET["info"] = 'title'; $_GET["cal"] = $Stream; $title_ref = include('../src/php/google-calendar.php');
	$_GET["info"] = 'collection'; $_GET["cal"] = $Stream; $collection = include('../src/php/google-calendar.php');
    $StreamInfo = "../data/".$collection."/streams/".$title_ref.".txt";
    				
    //If TITLE doesn't exist
    if(is_file($StreamInfo)){

        //Get Auto Stream Info
        $AutoMetaData = "../data/".$collection."/streams/".$title_ref.".txt";
        $AutoStream = fopen($AutoMetaData, "r");
        while(!feof($AutoStream))
        {
            $AutoContent = fgets($AutoStream);
            $AutoLine[] = $AutoContent;
        }
        fclose($AutoMetaData);
        //Get Style
        $stream_style = trim($AutoLine[3]);
        //In case style is undefined

    }
    //Set Default Style
    if($stream_style == ""){ $stream_style = "enpjj"; }
    //Build Stinger if Video exists
    if($_GET["trs"] == "in" || $_GET["trs"] == "out")
        {
        $video_URL = '../styles/src/vid/'.$_GET["trs"].'-'.$stream_style.'.webm';
        if(file_exists($video_URL)){ $stinger = '<video autoplay onended="this.currentTime=this.duration" id="video" width="100%" height="100%" ><source src="'.$video_URL.'" type="video/webm"></video><input id="type" value="in">'; }
        else{ $stinger = '<video autoplay onended="this.currentTime=this.duration" id="video" width="100%" height="100%" ><source src="../src/vid/'.$_GET["trs"].'-enpjj.webm" type="video/webm"></video><input id="type" value="in">'; }
        }
    elseif(!empty($_GET["trs"]))
        {
        $video_URL = '../src/vid/'.$_GET["trs"].'.webm';
        if(file_exists($video_URL)){ $stinger = '<video autoplay onended="this.currentTime=0" id="video" width="100%" height="100%" ><source src="'.$video_URL.'" type="video/webm"></video><input id="type" value="">'; }
        else{ $stinger = '<video autoplay onended="this.currentTime=0" id="video" width="100%" height="100%" ><source src="" type="video/webm"></video><input id="type" value="">'; }
        }
	else{ $stinger = '<video autoplay onended="this.currentTime=this.duration" id="video" width="100%" height="100%" ><source src="" type="video/webm"></video><input id="type" value="">'; }

    //Show Stinger
    echo $stinger;
?>
</body>
</html>