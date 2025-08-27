<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Preview</title>
    <link rel="stylesheet" href="scripts/css/index.css">
    <script src="scripts/js/index.js"></script>
    <link rel="icon" href="../favicon.ico" />
</head>
<body onLoad="Initialize()">
    <div id="WrapControl" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="controls" src="apps/dgc.php?str=preview"></iframe></body></div>
    <div id="WrapEditor" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="editor" src="apps/dse.php?str=preview"></iframe></body></div>
    <div id="WrapMessage" class="" onDblClick="this.classList.toggle('LOCKED')"><iframe id="messages" src="apps/dmc.php?str=preview"></iframe></body></div>
    <a href="pilotage.php?str=preview" target="_blank" id=PilotageApp>Ouvrir l'application "Pilotage"</a>
    <div id="WrapPreview">
        <iframe class="screen" src="apps/dsk.php?str=preview"></iframe>
        <video id="transparent"></video>
    </div>
</html>
