<!DOCTYPE html>
<?php
	//Stream & Messages
	if (!empty($_GET["str"])) { $Stream = $_GET["str"]; $MessageScript = '<script src="../src/js/message.js"></script>'; }
	else { $Stream = ""; $MessageScript = ""; }
	//Modes
	$ChapterTransition = 'OFF';
	$ChapterTitle = '';
	$LowerThird = 'OFF';
	$Name = $Function = $Translation = $TextBubles = "";
	$PlayState = "autoplay";
	

	if (!empty($_GET["mode"]) && $_GET["mode"] == 'light'){ $Mode_CSS = '<link rel="stylesheet" href="../src/css/light.css">'; }
	elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'demo'){
		$LowerThird = 'ON';
		$Name = "Prénom NOM";
		$Function = "Fonction Complète";
		$Translation = "Fonction Traduite";
		$TextBubles = '<div class="TextBuble Right">Bonjour à tous !</div>';
		$TextBubles.= '<div class="TextBuble Left">Voici un exemple de commentaire long, constitué de plusieurs phrases. La seconde phrase étant celle-ci.</div>';
	}
	elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'transition'){ $ChapterTransition = 'ON'; $ChapterTitle = "<h1>Titre du chapitre</h1>"; $PlayState = ""; }
	//Counter
	if (!empty($_GET["cnt"]) && $_GET["cnt"] == "on") { $Counter = '<script src="../src/js/counter.js"></script>'; } else { $Counter = ""; }
?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="../src/css/graphics.css">
	<?php  echo $Mode_CSS ?>
	<link rel="stylesheet" href="../../../src/css/fonts.css">
	<script src="../src/js/graphics.js"></script>
	<?php echo $MessageScript ?>
	<?php  echo $Counter ?>
	<title>Graphics</title>
</head>
<body>
	<!-- Counter -->
    <div id="counter"></div>
	<!-- Hiddent Parameters -->
	<input class="hidden" id="mode" value="<?php  echo $_GET["mode"] ?>">
	<!-- Lower Third -->
	<div id="LowerThird" class="<?php echo $LowerThird ?>">
		<div class="speaker">
			<div id="name"><?php echo $Name ?></div>
			<div id="function"><?php echo $Function ?></div>
			<div id="translation"><?php echo $Translation ?></div>
		</div>
	</div>
	<!-- Side Messages -->
	<div id="SideMessages" data-stream-key="<?php  echo $Stream ?>"class="OFF"><div id="Messages"><?php echo $TextBubles ?></div></div>
	<audio id="notification"><source src="../src/son/notification.mp3" type="audio/mpeg"></audio>
	<!-- Banner Info -->
	<div id="ScrollingBanner" class="OFF"><div id="BannerWrapper"><p id="Banner"></p></div></div>
	<!-- Part Title -->
	<div id="ChapterTransition" class="<?php echo $ChapterTransition ?>">
		<div id="Title" class="chapter"><?php echo $ChapterTitle ?></div>
		<video id="video" muted <?php echo $PlayState ?> loop class="background" width="100%" height="100%" ><source src="../src/vid/bkg-enpjj.webm" type="video/webm"></video>
		<audio id="transition"><source src="../src/son/transition.mp3" type="audio/mpeg"></audio>
	</div>
</body>
</html>