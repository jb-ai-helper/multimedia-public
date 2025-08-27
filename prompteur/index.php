<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="src/css/prompteur.css">
    <script src="src/js/prompteur.js"></script>
    <link rel="icon" href="favicon.ico" />
    <title>Prompteur</title>
</head>
<body>
    <span style="position: fixed; left: 5px; top: 5px;" class="material-icons" onClick="document.getElementById('file').click();">file_upload</span>
    <span style="position: fixed; right: 5px; top: 5px;" class="material-icons" onClick="FullScreenIN(this)">open_in_full</span>
    <span id="playPauseButton" style="position: fixed; right: 50px; top: 5px;" class="material-icons" onclick="togglePlayPause()">play_arrow</span>
    <div id="parameters">
        Défilement&nbsp;:&nbsp;<input id="speed" onChange="Update(this)" type="range" max="100" min="-100" value="0" />&nbsp;<output id="speed_output">0</output><br />
        Taille&nbsp;:&nbsp;<input id="size" onChange="Update(this)" type="range" max="200" min="50" value="100" />&nbsp;<output id="size_output">100</output>%<br />
        <form name="load" action="index.php" method="post" enctype="multipart/form-data"><input id="file" onChange="load.submit();" type="file" name="file" style="display: none;" /></form>
    </div>
    <?php
        $script = "Double-cliquer pour modifier le script...";
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $file = $_FILES["file"]["tmp_name"];
        $opened = fopen($file, "r");
        $script = fread($opened, filesize($file));
        $script = str_replace(array("\r\n","\n"), '<br>', $script);
        fclose($opened);
    }
    ?>
    <div id="script" onClick="FlipScript()" onDblClick="ToggleEdit()" style="font-size: 10vw; transform: translateX(-50%)"><?php echo $script; ?></div>
</body>
</html>
