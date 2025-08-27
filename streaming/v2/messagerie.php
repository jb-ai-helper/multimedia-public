<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <?php
    //Streaming Key
    if (!empty($_GET['str'])) {
        $stream = $_GET['str']; 
    } else {
        $stream = 'preview'; 
    }
    ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Messagerie</title>
    <link rel="stylesheet" href="scripts/css/index.css">
</head>
<body>
    <iframe id="GraphicsFrame" class="FullWidth" src="apps/dsk.php?mode=light&amp;str=<?php echo $stream ?>"></iframe>
    <iframe id="MessageFrame" class="FullWidth" src="apps/dmc.php?str=<?php echo $stream ?>"></iframe></body></div>
</html>
