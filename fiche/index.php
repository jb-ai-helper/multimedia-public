<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
    //Set Default Streaming Folder
    $StreamingFolder = "../streaming/v2/";
    //Commun Arrays & Variables
    $french_month = array("mois", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
    //Set Default Values
    $onLoad = "UpdateAll();";
    $VideoDate = date("d ") . $french_month[(int)date("m")] . date(" Y");
    $VideoDateData = $ShootingDateData = date("Y-m-d");
    $ShootingDate = date("d/m/Y");
    $VideoTitle = "Titre de la vidéo";
    $VideoVisibilityData = "unlisted";
    $VideoDescription = "Description de la vidéo...";
    $EventTitle = "Titre du Stream";
    $EventSubTitle = "Bienvenue";
    $EventStyle = "ENPJJ";
    $EventStyleData = "enpjj";
    $EventLocation = "Amphi Condorcet Bas";
    $OtherLocation = "Merci d'indiquer le lieu de tournage souhaité.";
    $OnSiteAttendance = $RemoteAttendance = "0";
    $BroadcastMethode = "Synchrone";
    $OtherLocation_class = $BroadcastMethode_suboptions = "hidden";
    $StartTime = $EndTime = "09:00";
    $Speakers = $Sequences = "";
    $PageTitle = "Demande de prestation multimédia";
    $GoogleAPI = $PopUp = "";
    $SaveButton = "button";

    //Get REF or set default
if (empty($_GET["ref"])) {
    $ref = time();
    $str = "temp";
} else {
    $ref = $_GET['ref'];
    $str = $ref;
    $xml = "events/" . $ref . ".xml";
    //Load if REF Exits
    if (file_exists($xml)) {
        include 'src/php/load.php';
        $Now = strtotime("today midnight");
        $RefDate = strtotime($ShootingDateData);
        if ($RefDate < $Now) {
            $SaveButton = "hidden";
        }
    } else {
        $onLoad = "ResetFiche();";
    }
    //SetUp Page Title
    $PageDate = explode("/", $ShootingDate);
    $PageTitle = $PageDate[2] . $PageDate[1] . $PageDate[0] . "_" . $PageTitle . " n°" . $ref; //Build up title
}

    //Get ACTION to listen to cenvert commande
if (!empty($_GET["action"]) && $_GET['action'] == "convert") {

    //Load Google API
    $GoogleAPI = '<script src="https://accounts.google.com/gsi/client"></script>';
    $GoogleAPI .= '<script src="src/js/html2canvas.min.js"></script>';
    //Setup Iframe for PHP Script
    $PopUp = '<iframe class="hidden" id="manager" name="manager"></iframe>';
    //Initialize Visible PopUp
    $PopUp .= '<div id="Convert_options" onclick="window.location.assign(\'https://multimedia.enpjj.fr/fiche/?ref=' . $ref . '\')">';
    //Step One
    $PopUp .= '<div id="Convert_StepOne" class="options" onclick="event.stopPropagation()">';
    $PopUp .= '<form id="saveform" action="src/php/convert.php" method="post" target="manager">';
    $PopUp .= '<input value="' . $ref . '" name="ref" class="hidden">';
    $PopUp .= '<label for="collection">[Multimédia] Indiquez la collection dans laquelle<br>vous souhaitez convertir cette fiche&nbsp;:</label>';
    $PopUp .= '<input type="text" value="ENPJJ" id="collection" name="collection" onkeydown="return CorrectKeys(event)" onkeyup="this.value = this.value.toUpperCase()" onclick="this.select()" autocomplete="off">';
    $PopUp .= '<input type="submit" value="CONVERTIR"></form>';
    $PopUp .= '</div>';
    //Step Two
    $PopUp .= '<div id="Convert_StepTwo" class="options hidden" onclick="event.stopPropagation()">';
    $PopUp .= '<label>[Multimédia] Pour programmer automatiquement<br />votre évènement sur Google, la connexion<br />à votre compte est requise :</label>';
    $PopUp .= '<input id="CreateLive" onclick="getAccessToken(\'YouTube\')" type="button" value="1) Programmer sur YouTube" disabled>';
    $PopUp .= '<input id="ScheduleLive" onclick="getAccessToken(\'Calendar\')" type="button" value="2) Ajouter au Calendrier">';
    //End of Convert PopUp
    $PopUp .= '</div>';
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="/src/css/fonts.css">
    <link rel="stylesheet" href="src/css/fiche.css">
    <script src="/src/js/commontools.js"></script>
    <script src="src/js/fiche.js"></script>
    <?php echo $GoogleAPI ?>
    <link rel="icon" href="favicon.ico" />
    <title><?php echo $PageTitle ?></title>
</head>
<?php
    //Add automatic Data

    //Visibility
if ($VideoVisibilityData == "public") { $VideoVisibility = "Publique";
} elseif ($VideoVisibilityData == "unlisted") { $VideoVisibility = "Non répertoriée";
} elseif ($VideoVisibilityData == "private") { $VideoVisibility = "Privée";
}

    //Location Previews
    $LocationPreviews = array(
        'Amphi Condorcet Bas' => "src/img/amphi-condorcet-bas.png",
        'Amphi Condorcet Haut' => "src/img/amphi-condorcet-haut.png",
        'Amphi Michelet' => "src/img/amphi-michelet.png",
        'Amphi Costa' => "src/img/amphi-costa.png",
        'Lab LEBAS' => "src/img/lab-lebas.png",
        'Lab Médiathèque' => "src/img/lab-mediatheque.png",
        'Studio' => "src/img/studio.png",
        'Autre...' => "src/img/other.png"
    );

    if (!array_key_exists(trim($EventLocation), $LocationPreviews)) {
        $OtherLocation = $EventLocation;
        $OtherLocation_class = "";
        $EventLocation = "Autre...";
    }
    $EventLocationData = $LocationPreviews[trim($EventLocation)];
    $EventLocation_Preview = $EventLocationData;
    ?>
<body onload="CheckBrowser();<?php echo $onLoad ?>">
    <div id="Menu">
        <div id="Burger" onClick="ToggleActions(this)" data-state="off" style="float: left"></div>
        <div id="Burger_actions" data-state="off">
            <div onClick="NewFiche()" class="action">Nouveau</div>
            <div onClick="OpenFiche()" class="action">Ouvrir</div>
            <div onClick="EnvoyerFiche()" class="action">Envoyer</div>
            <div onClick="CopyFiche()" class="action">Dupliquer</div>
            <div onClick="window.print()" class="action">Imprimer</div>
        </div>
        <div id="REF"><?php echo $ref ?></div>
        <div onClick="SaveFiche('<?php echo $ref ?>')" class="<?php echo $SaveButton ?>" style="float: right">Sauvegarder</div>
    </div>
    <div id="YouTube_Section">
        <svg id="YouTube_Logo" viewBox="0 0 90 20">
            <path d="M27.9727 3.12324C27.6435 1.89323 26.6768 0.926623 25.4468 0.597366C23.2197 2.24288e-07 14.285 0 14.285 0C14.285 0 5.35042 2.24288e-07 3.12323 0.597366C1.89323 0.926623 0.926623 1.89323 0.597366 3.12324C2.24288e-07 5.35042 0 10 0 10C0 10 2.24288e-07 14.6496 0.597366 16.8768C0.926623 18.1068 1.89323 19.0734 3.12323 19.4026C5.35042 20 14.285 20 14.285 20C14.285 20 23.2197 20 25.4468 19.4026C26.6768 19.0734 27.6435 18.1068 27.9727 16.8768C28.5701 14.6496 28.5701 10 28.5701 10C28.5701 10 28.5677 5.35042 27.9727 3.12324Z" fill="#FF0000" class="style-scope yt-icon"></path>
            <path d="M11.4253 14.2854L18.8477 10.0004L11.4253 5.71533V14.2854Z" fill="white" class="style-scope yt-icon"></path>
        </svg>
        <div id="YouTube_Preview">
            <iframe id="Thumbnail" class="screen" src="../streaming/v2/apps/dsk.php?mode=miniature&str=<?php echo $str ?>"></iframe>
        </div>
        <div id="MetaData">
            <div id="VideoTitle" onClick="EditInput(this,100,'smalltext')" class="editable"><?php echo $VideoTitle ?></div><br />
            <div id="VideoVisibility" onClick="ShowOptions(this)" class="editable" data-visibility="<?php echo $VideoVisibilityData ?>"><?php echo $VideoVisibility ?></div><br />
                <div id="VideoVisibility_options" onClick="HideOptions(this)" class="hidden">
                    <div class="options">
                        <div onClick="SelectOption(this)" class="option alt" data-visibility="public" data-alt="Tout utilisateur de YouTube peut regarder les vidéos publiques, et celles-ci peuvent également être partagées avec tous les internautes qui utilisent la plate-forme.">Publique</div><br />
                        <div onClick="SelectOption(this)" class="option alt" data-visibility="unlisted" data-alt="Les vidéos non répertoriées peuvent être regardées et partagées par tous les utilisateurs disposant du lien.">Non répertoriée</div><br />
                        <div onClick="SelectOption(this)" class="option alt" data-visibility="private" data-alt="Vos vidéos ne peuvent être regardées que par vous et les utilisateurs de votre choix.">Privée</div>
                    </div>
                </div>
            <div id="VideoDate" onClick="EditDate(this,1)" data-date="<?php echo $VideoDateData ?>" class="editable"><?php echo $VideoDate ?></div><br />
            <div id="VideoDescription" onClick="EditInput(this,5000,'longtext')" class="editable"><?php echo $VideoDescription ?></div>
        </div>
    </div>
    <div id="DSK_Section">
        <img id="DSK_Logo" src="favicon.ico" />
        <h1>Habillage Graphique</h1>
        <div id="DSK_Preview">
            <img id="EventLocation_preview" class="screen" src="<?php echo $EventLocation_Preview ?>" />
            <iframe class="screen" src="../streaming/v2/apps/dsk.php?mode=demo&str=<?php echo $str ?>"></iframe>
        </div>
        <div id="EventData">
        <h2>Informations Techniques</h2><br />
            <span>Titre&nbsp;:&nbsp;</span><div id="EventTitle" onClick="EditInput(this,100,'longtext')" class="editable"><?php echo $EventTitle ?></div><br />
            <span>Sous-titre / Message d'accueil&nbsp;:&nbsp;</span><div id="EventSubTitle" onClick="EditInput(this,100,'longtext')" class="editable"><?php echo $EventSubTitle ?></div><br />
            <span>Style de l'habillage graphique&nbsp;:&nbsp;</span><div id="EventStyle" onClick="ShowOptions(this)" data-css="<?php echo $EventStyleData ?>" class="editable"><?php echo $EventStyle ?></div><br />
            <div id="EventStyle_options" onClick="HideOptions(this)" class="hidden">
                <div class="options">
                    <div onClick="SelectOption(this),UpdatePreview(this)" style="display: inline-block" class="option" data-css="enpjj">ENPJJ</div><div style="display: inline-block; color: black">&nbsp;(par défaut)</div>
                <?php
                //Get StyleFiles
                $dir = $StreamingFolder . "styles/";
                $folder = opendir($dir);

                while ($file = readdir($folder)) {
                    if (strpos($file, '.css') == true) {
                        $StyleFiles[] = basename($file, "." . "css");
                    }
                }

                closedir($folder);
                unset($file);

                //Get StyleInfo
                foreach ($StyleFiles as $FileRef) {
                    $FilePath = $dir . $FileRef . ".css";
                    $OpenFilePath = fopen($FilePath, "r");
                    while (!feof($OpenFilePath)) {
                        $Content = fgets($OpenFilePath);
                        $Line[] = $Content;

                        //Format Name
                        $title = trim($Line[0]);
                        $title = str_replace("/* ", "", $title);
                        $title = str_replace(" */", "", $title);
                        //Build Array "StyleInfo"
                        $StyleInfo[$FileRef] = $title;
                    }
                    fclose($OpenFilePath);
                    unset($Line);
                    unset($title);
                }

                //Sort array according to value
                asort($StyleInfo);

                foreach ($StyleInfo as $StyleRef => $StyleName) {
                    if ($StyleRef != "enpjj" && $StyleRef != "perso") { echo '<div onClick="SelectOption(this),UpdatePreview(this)" class="option" data-css="' . $StyleRef . '">' . $StyleName . '</div>' . "\r\n";
                    }
                    //Stop Tab if last style
                    if (--$nbStyle > 0) {
                        echo "\t\t\t\t\t\t";
                    }
                }
                ?>
                    <div style="display: inline-block; color: black">ou&nbsp;</div><div style="display: inline-block" onClick="SelectOption(this),UpdatePreview(this)" class="option" data-css="perso">création d'un habillage personnalisé</div>
                </div>
            </div>
            <span>Lieu du tournage&nbsp;:&nbsp;</span><div id="EventLocation" onClick="ShowOptions(this)" class="editable" data-preview="<?php echo $EventLocationData ?>"><?php echo $EventLocation ?></div><br />
                <div id="EventLocation_options" onClick="HideOptions(this)" class="hidden">
                    <div class="options">
                        <?php foreach ($LocationPreviews as $Location => $Preview) {
                            echo '<div onClick="SelectOption(this),UpdatePreview(this)" class="option" data-preview="' . $Preview . '">' . $Location . '</div>';
                        } ?>
                    </div>
                </div>
                <div data-trigger="Autre" class="<?php echo $OtherLocation_class ?> EventLocation_suboptions">
                    <div id="OtherLocation" onClick="EditInput(this,250,'smalltext')" class="editable"><?php echo $OtherLocation ?></div><br />
                </div>
            <span>Public prévisionnel&nbsp;:&nbsp;</span>
            <div id="OnSiteAttendance" onClick="EditInput(this,3,'number')" class="editable"><?php echo $OnSiteAttendance ?></div><span>&nbsp;en présentiel et&nbsp;</span>
            <div id="RemoteAttendance" onClick="EditInput(this,4,'number')" class="editable"><?php echo $RemoteAttendance ?></div><span>&nbsp;en distanciel.</span><br />
            <span>Mode de diffusion&nbsp;:&nbsp;</span>
            <div id="BroadcastMethode" onClick="ShowOptions(this)" class="editable"><?php echo $BroadcastMethode ?></div><br />
                <div id="BroadcastMethode_options" onClick="HideOptions(this)" class="hidden">
                    <div class="options">
                        <div class="option alt" onClick="SelectOption(this);ResetShootingDate()" data-alt="Diffusion en direct.">Synchrone</div><br />
                        <div class="option alt" onClick="SelectOption(this)" data-alt="Tournage en amont de la diffusion.">Asynchrone</div>
                    </div>
                </div>
                <div data-trigger="Asynchrone" class="BroadcastMethode_suboptions <?php echo $BroadcastMethode_suboptions ?>">
                    <span>Date du tournage&nbsp;:&nbsp;</span><div id="ShootingDate" onClick="EditDate(this,0)" data-date="<?php echo $ShootingDateData ?>" class="editable"><?php echo $ShootingDate ?></div><br />
                </div>
        </div><br />
        <hr>
        <h2>Intervenants</h2>
        <div onClick="AddSpeaker(this)" class="button" style="float: right">+ Intervenant</div><br />
        <div id="Speakers"><?php echo $Speakers ?></div>
        <span class="">
            Double-clicker sur un intervenant pour permuter son statut (en présentiel / en distanciel).<br />
            Utilisez "Shift" [ ⇧ ] + "Retour à la ligne" [ ↲ ] pour forcer le retour à la ligne.<br />
            Double-clicker sur le nom pour le passer en majuscule.
        </span>
        <hr>
        <div class="section">
            <h2>Déroulé</h2>
            <div onClick="AddPause()" class="button alt" data-alt="Temps de pause sans aucune intervention." style="float: right">+ Pause</div>
            <div onClick="AddSpeech()" class="button alt" data-alt="Temps de prise de parole (conférence, table ronde, etc.)." style="float: right">+ Allocution(s)</div>
            <div onClick="AddTransition()" class="button alt" data-alt="Transition animée pour ponctuer les différents temps de l'évènement." style="float: right">+ Transition</div>
            <input id="StartTime" onChange="UpdateTime(this)" type="time" value="<?php echo $StartTime ?>">
            <div id="Sequences"><?php echo $Sequences ?></div>
            <input id="EndTime" disabled type="time" value="<?php echo $EndTime ?>">
        </div>
    </div>
    <?php echo $PopUp ?>
</body>
</html>
