<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file = '/var/www/multimedia.enpj.fr/www/signage/src/vid/'.$_GET['dir']."/".$_GET['file'].'.mp4';

if (file_exists($file)) {
    if (unlink($file)) {
        echo "The video was successfully deleted!\r\nPlease save your changes if necessary...";
    } else {
        $error = error_get_last();
        echo 'Failed to delete the video... ' . $error['message'];
    }
} else {
    echo 'The video "' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '" does not exist on the server...';
}
?>
