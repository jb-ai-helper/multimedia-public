<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>OBS Preview</title>
	<link rel="stylesheet" href="src/css/preview.css">
	<link rel="icon" href="src/img/favicon.ico" />
</head>
<body>
	<?php
	//Set up Calendar
	if(!empty($_GET['str'])) { $Stream = $_GET['str']; }
	else { $Stream = 'preview'; }
	//Zoom Level
	if(!empty($_GET['zm'])){ $zoom = $_GET['zm']; } 
	else{ $zoom = '0.5'; }
	//Copyrifght Elements
	if(!empty($_GET['el'])){ $elements = $_GET['el']; } 
	else{ $elements = 'marianne;enpjj;banner'; }
	?>
	<div id="WrapPreview" style="transform: translate(-50%,-50%) scale(<?php echo $zoom ?>);">
		<iframe class="screen" src="apps/backgrounds.php"></iframe>
		<iframe class="screen" src="apps/backgrounds.php?bkg=welcome&amp;zic=off"></iframe>
		<iframe class="screen" src="apps/graphics.php?str=<?php echo $Stream ?>"></iframe>
		<iframe class="screen" src="apps/copyrights.php?el=<?php echo $elements ?>"></iframe>
		<div id="transparent"></div>
	</div>
	<div id="WrapControl" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="controls" src="apps/controls.php?collection=auto&amp;str=<?php echo $Stream ?>"></iframe></body></div>
	<div id="WrapMessage" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="messages" src="apps/messages.php?str=<?php echo $Stream ?>"></iframe></body></div>
</html>