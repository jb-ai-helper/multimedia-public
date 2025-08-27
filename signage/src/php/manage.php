<?php $site = basename(dirname($_SERVER['PHP_SELF'])); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="/src/css/fonts.css" type="text/css">
    <link rel="stylesheet" href="../../src/css/manager.css" type="text/css">
    <script src="/src/js/commontools.js" type="text/javascript"></script>
    <script type="text/javascript">var site = "<?php echo $site ?>";</script>
    <script src="../../src/js/signage.js" type="text/javascript"></script>
    <script src="../../src/js/manager.js" type="text/javascript"></script>
    <link rel="icon" href="../../favicon.ico" />
<title>Screen Manager (<?php echo strtoupper($site) ?>)</title>
</head>
<body onLoad="SetUpPage()">
    <h1><?php echo strtoupper($site) ?>'s Screen Manager</h1>
    
    <?php $admin = basename($_SERVER['PHP_SELF']) != 'manager.php' ? 'none' : ''; ?>
    
    <div style="display: <?php echo $admin; ?>">
        <h3>Actions / Alertes</h3>
        <span class="description">Permet d\'actualiser tous les écrans ou de déclancher des alertes.</span>
        <center>
            <div class="button incendie" onClick="Trigger('firealarm')">Alerte Incendie</div>
            <div class="button reset" onClick="Trigger('fullrefresh')">Réinitialisation</div>
            <div class="button intrusion" onClick="Trigger('intrusionalert')">Alerte Intrusion</div>
        </center>
    </div>
    
    <h3>Style éphémère</h3>
    <span class="description">Permet la mise en place d'un style d'affichage particulier.</span>
    <label for="style_select">Appliquer un style particulier aux écrans :</label>
    <select id="style_select">
        <option value="">Aucun</option>
        <option value="mourning">Deuil</option>
        <option value="pinkribbon">Octobre Rose</option>
        <option value="xmas">Noël</option>
    </select>
    <br /><br />
    <label for="style_start">Début du style éphémère :</label>
    <input id="style_start" type="date" value="" />
    <br />
    <label for="style_end">Fin du style éphémère :</label>
    <input id="style_end" type="date" value="" />
    <br /><br />
    <div class="button" onClick="Save('style')">Enregistrer</div>
    
    <div style="display: <?php echo $admin; ?>">
        <h3>Mise en avant</h3>
        <span class="description">Permet la mise en avant d'une vidéo en plein écran.</span>
        <div id="vertical_option" class="group">
            <input type="checkbox" disabled id="vertical_checkbox" onChange="Unable(['vertical_checkbox', 'horizontal_checkbox'], 'playlist_checkbox')">
            <label for="vertical_checkbox">Afficher une vidéo verticale en plein écran :</label>
            <select id="vertical_select" onChange="Check(this, 'vertical_checkbox')"><option value="">Aucun</option></select>
            <label class="upload" for="vertical_event"></label>
            <input id="vertical_event" type="file" accept=".mp4" onChange="Upload(this)" />
            <div class="delete" onclick="Delete(this)"></div>
            <div class="preview" onclick="Preview(this)"></div>
        </div>
        <br />
        <div id="horizontal_option" class="group">
            <input type="checkbox" disabled id="horizontal_checkbox" onChange="Unable(['vertical_checkbox', 'horizontal_checkbox'], 'playlist_checkbox')">
            <label for="horizontal_checkbox">Afficher une vidéo horizontal en plein écran :</label>
            <select id="horizontal_select" onChange="Check(this, 'horizontal_checkbox')"><option value="">Aucun</option></select>
            <label class="upload" for="horizontal_event"></label>
            <input id="horizontal_event" type="file" accept=".mp4" onChange="Upload(this)" />
            <div class="delete" onclick="Delete(this)"></div>
            <div class="preview" onclick="Preview(this)"></div>
        </div>
        <br />
        <div id="playlist_option" class="group">
            <input type="checkbox" disabled id="playlist_checkbox" onChange="Hide(this, 'playlist_module')">
            <label for="horizontal_checkbox">Afficher les vidéos mise en avant en boucle</label>
        </div>
        <br /><br />
        <label for="event_start">Début de la mise en avant :</label>
        <input id="event_start" type="date" value="" />
        <br />
        <label for="event_end">Fin de la mise en avant :</label>
        <input id="event_end" type="date" value="" />
        <br /><br />
        <div class="button" onClick="Save('event')">Enregistrer</div>
    </div>
    
    <div id="playlist_module" class="">
        <h3>Liste de lecture</h3>
        <span class="description">Permet la diffusion d'une liste de vidéos horizontales (affiches silencieuses) dans la partie dédiée de la mise en page.</span>
        <select id="playlist_add" class="button" onChange="AddEntry(this)">
            <option value="">Ajouter une vidéo depuis la biblothèque</option>
        </select>
        <select id="playlist_delete" class="button" onChange="Delete(this)">
            <option value="">Supprimer une vidéo de la bibliothèque</option>
        </select>
        <div class="group">
            <label class="upload" for="playlist_upload"></label>
            <input id="playlist_upload" type="file" accept=".mp4" onChange="Upload(this)" />
        </div>
        <br />
        <ol id="playlist"></ol>
        <div class="button" onClick="Save('playlist')">Enregistrer</div>
    </div>
</body>

</html>
