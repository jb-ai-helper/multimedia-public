<!DOCTYPE html>
<?php  if(!empty($_GET['str'])){ $Stream = $_GET['str']; } else{ $Stream = ''; } ?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Distant Graphics Controls</title>
	<link rel="stylesheet" href="../src/css/controls.css">
	<script src="../src/js/jquery.js"></script>
	<script src="../src/js/controls.js"></script>
</head>

<body class="panel">

<!-- Stream Key -->
<input class="hidden" id="stream-key" value="<?php  echo $Stream; ?>">
<button class="half" onClick="OpenLink(this)" value="../remote.php?str=<?php echo $Stream ?>">Ouvrir l'application séparément</button>
<button class="half" onClick="Seperate(this)" value="graphics.php?mode=light&cnt=on&str=<?php echo $Stream ?>">Ouvrir les messages internes</button>
<div class="spacer"></div>

<!-- Side Messages -->
<div id="PublicMessages">
	<div class="subsection">Messages Public<?php  if($Stream != "") echo " (".strtoupper($Stream).")"; ?></div>
	<!-- <input class="full" id="note-life" type="time" step="2" value="00:10:00"> -->
	<button class="half" id="" onclick="Insert()">Insérer une image (URL)</button>
	<button class="half" id="" onclick="Insert('QR')">Insérer un code QR (URL)</button>
	<textarea onKeyUp="Preview(this)" class="full" id="public-html" placeholder="Contenu HTML"></textarea>
	<button class="full" onclick="Transfer(this)">Envoyer</button>
	<div class="spacer"></div>
</div>

<!-- Internal Messages --> 
<div id="InternalMessages">
	<div class="subsection">Messages Interne<?php  if($Stream != "") echo " (".strtoupper($Stream).")"; ?></div>
	<textarea onKeyUp="Preview(this)" class="full" id="internal-html" placeholder="Contenu HTML"></textarea>
	<button class="full" onclick="Transfer(this)">Envoyer</button>
	<div class="spacer"></div>
</div>

<!-- Global Messages Controls  --> 
<div id="PreviousMessage">
	<?php
	$RamPath = "../ram/".$Stream.".txt";
	if(is_file($RamPath)){
		$RamFile = fopen($RamPath, "r");
		while(!feof($RamFile)) { $RamData = fgets($RamFile); }
		fclose($RamFile);
		$RamContent = explode('|', $RamData);
		$CurrentID = $RamContent[0];
		$CurrentStamp = $RamContent[1];
		$CurrentContent = $RamContent[2];
        //Create New ID after CLEAR ALL
        if($CurrentID == ""){ $CurrentID = time(); }
	}
	else{ $CurrentLife = $CurrentContent = ""; }
	 ?>
	<div class="subsection">Message Précédent<?php  if($Stream != "") echo " (".strtoupper($Stream).")"; ?></div>
	<input class="half" id="current-stamp" value="<?php  echo $CurrentStamp; ?>">
    <input class="half" disabled id="current-id" value="<?php  echo $CurrentID; ?>">
	<textarea class="full" disabled id="current-content"><?php  echo $CurrentContent; ?></textarea>
	<button class="half" onclick="RecallMessage(this)">Renvoyer</button>
	<button class="half" onclick="DeleteMessage(this)">Supprimer</button>
	<button class="full" onclick="ClearAllMessages(this)">Vider</button>
	<div class="spacer"></div>
</div>
</body>
</html>