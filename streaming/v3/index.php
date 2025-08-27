<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<?php
	//Set up Calendar
	if(!empty($_GET['str'])) { $stream = $_GET['str']; }
	else { $stream = 'preview'; }
	//Zoom Level
	if(!empty($_GET['zm'])){ $zoom = $_GET['zm']; } 
	else{ $zoom = '0.5'; }
    //Set up Mode
    if (!empty($_GET["mode"])){ $mode = $_GET["mode"]; }
    else{ $mode = 'stream'; }
	?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Preview</title>
	<link rel="icon" href="../favicon.ico" />
	<link rel="stylesheet" href="scripts/css/index.css">
    <script src="/src/js/detection_erreur.js"></script>
    <script src="/src/js/commontools.js"></script>
    <script src="scripts/js/dsk.js"></script>
    <script src="styles/enpjj.js"></script>
    <!--<script src="styles/jvr23.js"></script>-->
</head>
<body>
    <div id="center"></div>
    <div id="middle"></div>
	<div id="WrapPreview" style="transform: translate(-50%,-50%) scale(<?php echo $zoom ?>);">
		<canvas id="dsk" width="1920" height="1080" class="screen"></canvas>
        <script> initDSK('dsk', '<?php echo $mode ?>'); </script>
		<div id="transparent"></div>
	</div>
    <!--<a href="pilotage.php?str=<?php echo $stream ?>" target="_blank" id=PilotageApp>Ouvrir l'application "Pilotage"</a>-->
    <!--<div id="WrapControl" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="controls" src="apps/dgc.php?str=<?php echo $stream ?>"></iframe></body></div>-->
	<!--<div id="WrapMessage" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="messages" src="apps/dmc.php?str=<?php echo $stream ?>"></iframe></body></div>-->
</html>