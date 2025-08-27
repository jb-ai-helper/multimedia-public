<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="../../../src/css/fonts.css">
	<link rel="stylesheet" href="../src/css/backgrounds.css">
	<script src="../src/js/backgrounds.js"></script>
	<title>Animated Background</title>
</head>
<body class="<?php echo $_GET["bkg"]; ?>">
<?php
    //Pause Screen
	if($_GET["bkg"] == "pause")
	{
		$background = '<div id="video" style="background-color: black; opacity:0.5"></div>';
		$background.= '<div id="pause">La diffusion en direct va reprendre<span id="countdown"> dans quelques instants...</span></div>';
		$background.= '<script src="../src/js/musicplayer.js"></script>';
	}
    //Welcome Screen
	elseif($_GET["bkg"] == "welcome")
	{
		$background.= '<div id="Title" data-line="1" class="welcome"><span>Titre du Stream</span></div>';
		$background.= '<div id="SubTitle" class="welcome">Bienvenue</div>';
		$background.= '<div id="Date">00 mois 0000</div>';
	}
	//Simple Video Background
	else{ $background = '<video id="video" autoplay muted loop  width="100%" height="100%" ><source src="../src/vid/bkg-enpjj.webm" type="video/webm"></video>'; }	
    
    //Get Music Status
    if (!empty($_GET)){ $music = $_GET["zic"]; }
    if ($music=="on"){ echo '<script src="../src/js/musicplayer.js"></script>'; }
    
    //Create Background
	echo $background;

?>
</body>
</html>