<!DOCTYPE html>
<?php
if (!empty($_GET['str'])) {
    $Stream = $_GET['str'];
} else {
    $Stream = '';
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Distant Message Controls (DMC)</title>
    <link rel="stylesheet" href="../scripts/css/ctrl.css">
    <script src="../../../src/js/jquery.js"></script>
    <script src="../../../src/js/commontools.js"></script>
    <script src="../scripts/js/ctrl.js"></script>
</head>

<body id="dmc" class="panel" onLoad="CheckStream()">

<!-- Stream Key -->
<div class="subsection mergecolumn">Stream Key : <?php
if ($Stream != "") {
    echo strtoupper($Stream); 
}
?></div>
<input class="hidden" id="stream-key" value="<?php  echo $Stream; ?>">

<!-- Side Messages -->
<div id="PublicMessages">
    <div class="spacer"></div>
    <div class="subsection">Messages Publics</div>
    <!-- <input class="full" id="note-life" type="time" step="2" value="00:10:00"> -->
    <button class="half" id="" onclick="Insert()">Insérer une image (URL)</button>
    <button class="half" id="" onclick="Insert('QR')">Insérer un code QR (URL)</button>
    <textarea onKeyUp="Preview(this)" class="full" id="public-html" placeholder="Contenu HTML" style="height: calc(5*var(--element-height) - 2px)"></textarea>
    <button class="full" onclick="Transfer(this)">Envoyer</button>
</div>

<!-- Internal Messages --> 
<div id="InternalMessages">
    <div class="spacer"></div>
    <div class="subsection">Messages Internes</div>
    <textarea onKeyUp="Preview(this)" class="full" id="internal-html" placeholder="Contenu HTML" style="height: calc(6 * var(--element-height))"></textarea>
    <button class="full" onclick="Transfer(this)">Envoyer</button>
</div>

<!-- Global Messages Controls  -->
<div id="PreviousMessage">
    <div class="spacer"></div>
    <div class="subsection">Message Précédent</div>
    <input class="half" disabled id="current-stamp" placeholder="Type" value="">
    <input class="half" disabled id="current-id" placeholder="Identification" value="">
    <textarea class="full" onClick="this.select()" id="current-content" placeholder="Contenu HTML" style="height: calc(4*var(--element-height) - 4px)"></textarea>
    <button class="half" onclick="RecallMessage(this)">Renvoyer</button>
    <button class="half" onclick="DeleteMessage(this)">Supprimer</button>
    <button class="full" onclick="ClearAllMessages(this)">Vider</button>
</div>
</body>
</html>
