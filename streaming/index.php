<?php
    $URL = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    header('Location: '.$URL.'v2');
    exit();
?>