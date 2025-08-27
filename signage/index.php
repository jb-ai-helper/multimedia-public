<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
//Get Location
if(!empty($_GET["site"])){
    $site = $_GET["site"];
} else { 
    $site = "sc";
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">   
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
     <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Cache-Control" content="post-check=0, pre-check=0">
    <meta http-equiv="Pragma" content="no-cache">
    <title>ENPJJ - <?php echo strtoupper($site) ?></title>
    <link rel="stylesheet" href="/signage/src/css/signage.css" type="text/css"/>
    <link rel="stylesheet" href="/src/css/gov.css" type="text/css"/>
    <script src="/src/js/commontools.js" type="text/javascript"></script>
    <script type="text/javascript">var site = "<?php echo $site ?>";</script>
    <script src="/signage/src/js/signage.js" type="text/javascript"></script>
    <link rel="icon" href="/signage/favicon.ico" />
</head>
<body>
    <div id="Overlay"></div>
    <iframe onLoad="StartSignage()" id="Layout" src="/signage/location/<?php echo $site ?>/"></iframe>
</body>
</html>
