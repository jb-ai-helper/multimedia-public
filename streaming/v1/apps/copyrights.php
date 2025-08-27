<!DOCTYPE html>
<?php
    $HTML = '<!-- Copyright Elements -->'."\r\n";
	//Get Elements
	if (!empty($_GET["el"])){ $el = $_GET["el"]; $elements = explode(";",$el); }
	//Top Banner
	if(in_array("banner",$elements)) { $SubClass = ""; $HTML.= '<div id="TopBanner"><div id="Title" class="banner">Titre du Stream</div><div id="SubTitle" class="banner">Bienvenue</div></div>'; }
    else { $SubClass = "welcome"; }
	//Logo ENPJJ
    if(in_array("enpjj",$elements)){ $HTML.= '<div id="ENPJJ" class="'.$SubClass.'"></div>'; }
    //Marianne
    if(in_array("marianne",$elements)){ $HTML.= '<div id="Marianne" class="'.$SubClass.'"></div>'; }
    //Partenaire
    $HTML.='<div id="Partenaire" class="'.$SubClass.'"></div>';
?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="../src/css/copyrights.css">
	<link rel="stylesheet" href="../../../src/css/fonts.css">
	<script src="../src/js/copyrights.js"></script>
	<title>Copyrights</title>
</head>
<body>
    <?php echo $HTML; ?>
</body>
</html>