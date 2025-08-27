<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file = '/var/www/multimedia.enpj.fr/www/signage/src/vid/'.$_GET['dir']."/".$_GET['name'].'.mp4';
$data = $_FILES["file"]["tmp_name"];

if(!file_exists($file)){
    if(move_uploaded_file($data, $file)) echo 'The video was successfully uploaded!';
    else{ echo 'Failed to upload the video...'; }
}
else{ echo 'The video already exists on the server...'; }
?>