<!DOCTYPE html>
<?php /*Get Link Key*/ if(!empty($_GET['key'])){ $key = $_GET['key']; } else{ $key = ''; } ?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="../../../src/css/fonts.css">
	<link rel="stylesheet" href="../src/css/dsk.css">
	<script src="../../src/js/dsk.js"></script>
	<title>ENPJJ - DSK v1.0</title>
</head>
<body>

	<!-- Copyright -->
	<div id="Copyright">
		<div id="Title_Copyright">Titre du Stream</div>
		<div id="SubTitle_Copyright">Bienvenue</div>
	</div>
	
	<div id="ENPJJ"></div>
	<div id="Marianne"></div>
	<div id="Partenaire"></div>

	<!-- Lowerthird -->
	<div id="LowerThird" class="OFF">
		<div id="name"></div>
		<div id="function"></div>
		<div id="translation"></div>
	</div>

	<!-- Messages -->
	<div id="Messages" data-stream-key="<?php  echo $key ?>"class="OFF"></div>
	<audio id="notification"><source src="../src/son/notification.mp3" type="audio/mpeg"></audio>

	<!-- Scrolling Banner -->
	<div id="ScrollingBanner" class="OFF"><div id="BannerWrapper"><p id="Banner"></p></div></div>

	<!-- Transition -->
	<div id="ChapterTransition" class="OFF"><div id="Title" class="chapter"></div></div>
	<audio id="transition"><source src="../src/son/transition.mp3" type="audio/mpeg"></audio>

	<!-- Introduction -->
	<div id="Introduction" class="OFF">
		<div id="Title_Introduction" data-line="1"><span>Titre du Stream</span></div>
		<div id="SubTitle_Introduction">Bienvenue</div>
		<div id="Date">00 mois 0000</div>
	</div>

	<!-- Pause -->
	<div id="pause" class="OFF">La diffusion en direct va reprendre<span id="countdown"> dans quelques instants...</span></div>
	<div class="fullscreen noir OFF"></div>

	<!-- Background -->
	<video id="video" class="fullscreen OFF" autoplay muted loop width="100%" height="100%" ><source src="../src/vid/bkg-enpjj.webm" type="video/webm"></video>

	<!-- BK Audio -->
	<script src="../src/js/musicplayer.js"></script>

	</body>
</html>