<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Dynamic Graphics Controls (DGC)</title>
    <link rel="stylesheet" href="../scripts/css/ctrl.css">
    <script src="../../../src/js/jquery.js"></script>
    <script src="../../../src/js/commontools.js"></script>
    <script src="../../../src/js/jscolor.js"></script>
    <script src="../scripts/js/ctrl.js"></script>
    <script src="../scripts/js/dse.js"></script>
</head>

<body class="panel" onLoad="CheckStream()">
    <div class="parameter" id="editor"><label class="switch" style="transform: scale(1);"><input onChange="ToggleSelector()" type="checkbox"><span class="slider round"></span></label>Activer l'éditeur de style</div>
    <div class="spacer"></div>
    
    <!-- Titre et nom du style -->
    <div class="subsection">Créer / Modifier un style d'habillage personnalisé :</div>
    <input class="full" id="css-name" onKeyUp="EncodeName()" placeholder="Nom dans le menu déroulant" maxlength="50">
    <input class="full" id="css-code" onKeyUp="CheckCode()" placeholder="Nom du fichier CSS" maxlength="10">
    <div class="spacer"></div>
    
    <!-- Editeur d'élément -->
    <div class="subsection">Personnaliser l'élément sélectionné :</div>
    <input class="full" id="css-selector" placeholder="Élément">
    <div id="css-parameters">
        <input class="full OFF" id="css-color" value="#FFFFFFFF" data-jscolor="{}"><label for="color">Couleur du texte</label>
        <input class="full OFF" id="css-background-color" value="#FFFFFFFF" data-jscolor="{}"><label for="background-color">Couleur du fond</label>
        <div class="parameter OFF" id="css-display"><label class="switch" style="transform: scale(1);"><input type="checkbox"><span class="slider round"></span></label>Désactiver l'éléments</div>
    </div>
    <button class="full" onclick="AddSelector()">Ajouter</button>
    <div class="spacer"></div>
    
    <!-- Feuille de style -->
    <div class="subsection">Contenu du fichier CSS : <span id="css-file"></span></div>
    <textarea id="css" onKeyUp="UpdateCSS()" class="full" placeholder="/* CSS Document */"></textarea>
    <div class="spacer"></div>
    
    <!-- Générques -->
    <div class="subsection">Génériques personnalisés :</div>
    <div class="parameter" id="vid-background"><label class="switch" style="transform: scale(1);"><input onChange="LoadIntro()" type="checkbox"><span class="slider round"></span></label>Fond personnalisée</div>
    <div class="parameter" id="vid-intro"><label class="switch" style="transform: scale(1);"><input onChange="LoadIntro()" type="checkbox"><span class="slider round"></span></label>Intro personnalisée</div>
    <div class="parameter" id="vid-outro"><label class="switch" style="transform: scale(1);"><input onChange="LoadOutro()" type="checkbox"><span class="slider round"></span></label>Outro personnalisée</div>
    <div class="spacer"></div>
    
    <!-- Enregistrement -->
    <button class="full" onclick="SaveCSS()">Enregistrer</button>
</body>
</html>