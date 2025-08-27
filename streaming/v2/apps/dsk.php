<!DOCTYPE html>
<?php
    //Default Modes Variables
    $Chapter = 'OFF';
    $LowerThird = 'OFF';
    $PlayState = "autoplay";
    $Title_Chapter = "";
    $BackgroundState = "OFF";
    $IntroductionState = "OFF";
    $Name = "";
    $Function = "";
    $Translation = "";
    $TextBubles = "";
    $Mode_head = "";
    $Mute = "";
    $OnLoad = "";

    //Get & Set Modes
if (!empty($_GET["mode"]) && $_GET["mode"] == 'light') {
    $Mode_head = '<link rel="stylesheet" href="/streaming/v2/scripts/css/light.css">'; $Mute = "muted"; 
} elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'demo') {
    $LowerThird = 'ON';
    $Name = "Prénom NOM";
    $Function = "Fonction Complète";
    $Translation = "Fonction Traduite";
    $TextBubles = '<div class="TextBubble Right">Bonjour à tous !</div>';
    $TextBubles.= '<div class="TextBubble Left">Voici un exemple de commentaire long, constitué de plusieurs phrases. La seconde phrase étant celle-ci.</div>';
} elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'transition') {
    $Chapter = 'IN'; $Title_Chapter = "<h1>Titre du chapitre</h1>"; $PlayState = ""; $BackgroundState = "CUT"; 
} elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'miniature') {
    $IntroductionState = "CUT"; $BackgroundState = "CUT"; $PlayState = "autoplay"; 
} elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'projector') {
    $Mode_head = '<link rel="stylesheet" href="/streaming/v2/scripts/css/projector.css">'; $Mute = "muted"; 
} elseif (!empty($_GET["mode"]) && $_GET["mode"] == 'counter') {
    $Mode_head = '<link rel="stylesheet" href="/streaming/v2/scripts/css/counter.css">'; $Mute = "muted"; 
}

if (isset($_GET["counter"])) {
    $CounterStart = $_GET["counter"];
    if (!is_numeric($CounterStart)) {
        $CounterStart = 0; 
    }
    $OnLoad = "SetCounter('".$CounterStart."');";
}

?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="/streaming/v2/scripts/css/dsk.css">
    <link rel="stylesheet" href="/streaming/v2/scripts/css/music.css">
    <?php echo $Mode_head ?>
    <script src="/src/js/commontools.js"></script>
    <script src="/streaming/v2/scripts/js/dsk.js"></script>
    <script src="/streaming/v2/scripts/js/music.js"></script>
    <title>Down Stream Keyer (DSK)</title>
</head>
<body onLoad="<?php echo $OnLoad ?>">

    <!-- Hidden Parameters -->
    <input class="hidden" type="text" id="style" value="enpjj">
    <input class="hidden" type="text" id="mode" value="<?php  echo $_GET["mode"] ?>">

    <!-- Pause (0) -->
    <div id="Pause" class="OFF">La diffusion en direct va reprendre<span id="countdown" class=""> dans quelques instants...</span></div>

    <!-- Messages (1) -->
    <div id="Messages" class=""><?php echo $TextBubles ?></div>
    <audio id="Notification" <?php echo $Mute ?>><source src="/streaming/v2/sounds/notification.mp3" type="audio/mpeg"></audio>

    <!-- Scrolling Banner (2) -->
    <div id="ScrollingBanner" class="OFF"><div id="BannerWrapper"><p id="Banner"></p></div></div>

    <!-- Lower Third (3) -->
    <div id="LowerThird" class="<?php echo $LowerThird ?>">
        <div id="Name"><?php echo $Name ?></div>
        <div id="Function"><?php echo $Function ?></div>
        <div id="Translation"><?php echo $Translation ?></div>
    </div>

    <!-- Copyright (4) -->
    <div id="Copyright">
        <div id="Title_Copyright">Titre du Stream</div>
        <div id="SubTitle_Copyright"></div>
    </div>

    <!-- Background (5) -->
    <video id="Background" class="fullscreen <?php echo $BackgroundState ?>" <?php echo $PlayState ?> muted loop><source src="../styles/vid/bkg-enpjj.webm" type="video/webm"></video>

    <!-- Introduction (6) -->
    <div id="Introduction" class="fullscreen <?php echo $IntroductionState ?>">
        <div id="Title_Introduction" data-line="1">Titre du Stream</div>
        <div id="SubTitle_Introduction">Bienvenue</div>
        <div id="Date">00 mois 0000</div>
    </div>

    <!-- Chapter (7) -->
    <div id="Chapter" class="<?php echo $Chapter ?>"><div id="Title_Chapter"><?php echo $Title_Chapter ?></div></div>
    <audio id="Transition" <?php echo $Mute ?>><source src="/streaming/v2/sounds/transition.mp3" type="audio/mpeg"></audio>
    <div id="Cover"></div>

    <!-- Logos (8) -->
    <div id="ENPJJ"></div>
    <div id="Marianne"></div>
    <div id="Partenaire"></div>

    <!-- Video Credits (9) -->
    <video id="Credits" preload="auto" class="fullscreen OFF" onended="this.currentTime=this.duration" <?php echo $Mute ?>><source src="" type="video/webm"></video>
    
    <!-- Counter (10) -->
    <div id="Counter" style="display: none"></div>

    </body>
</html>
