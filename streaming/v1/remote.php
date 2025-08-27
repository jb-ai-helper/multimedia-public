<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<?php
	//Zoom Level
	if(!empty($_GET['zm'])){ $zoom = $_GET['zm']; } 
	else{ $zoom = '0.5'; }
	//Streaming Key
	if(!empty($_GET['str'])){ $Stream = $_GET['str']; } 
	else{ $Stream = ''; }
	?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Remote Controls</title>
	<link rel="stylesheet" href="src/css/preview.css">
	<? if($Stream == '' || $Stream == 'preview'){
	echo 	'<script>'.
			'var STR = prompt("Préciser le lieu du stream :", "'.$Stream.'");'.
			'if(!window.location.href.includes("str=preview")) window.location.href = "remote.php?str="+STR;'.
			'</script>';
	}
	?>
</head>
<body>
	<!-- <iframe id="ChatFrame" src="src/php/google-calendar.php?info=chat&cal=<?php echo $Stream ?>"></iframe> -->
	<iframe id="GraphicsFrame" src="apps/graphics.php?mode=light&amp;str=<?php echo $Stream ?>"></iframe>
	<iframe id="MessageFrame" class="half" src="apps/messages.php?str=<?php echo $Stream ?>"></iframe></body></div>
</html>