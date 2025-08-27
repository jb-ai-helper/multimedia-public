<?php
//Include Common Functions
require '../../../src/php/commontools.php';

$zip = time();
$working_dir = "../../files/";
clearDirectory($working_dir);

$images_dir = $working_dir.$zip."/";
if (!is_dir($images_dir)) {
    mkdir($images_dir, 0777, true);
} else {
    rmdir($images_dir); mkdir($images_dir, 0777, true);
}

if ($_FILES['list']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['list']['tmp_name'])) {
    $list = file($_FILES['list']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);    
    if (count($list) > 0) {
        $files = array();
        $format = $_POST['format'];
        $short =  $_POST['short'];
        foreach ($list as $link) {
            $link = trim($link);
            //Shorten link if asked for
            if ($short == "true") {
                $ref = generateFileName("../../../liens/data/");
                $file_path = "../../../liens/data/".$ref.".txt";
                $file = fopen($file_path, "w");
                if ($file) {
                    fwrite($file, $link);
                    fclose($file);
                    $link = "qr.enpjj.fr/" . $ref;
                } else {
                    $link = null;
                }
            }
            //Generate QR Code
            if ($link) {
                $src = "https://api.qrserver.com/v1/create-qr-code/?data=".$link."&size=1000x1000&charset-source=UTF-8&ecc=L&margin=0&format=".$format;
                $name = $images_dir.basename($link).".".$format;
                $image = fopen($name, "w");
                if ($image && fwrite($image, file_get_contents($src))) {
                    fclose($image);
                    chmod($name, 0777);
                    array_push($files, $name);
                } else {
                    echo "Failed to create QR Code for link: ".$link;
                }
            } else {
                echo "Failed to shorten the link: " . $link;
            }
        }
        $destination = $working_dir.$zip.'.zip';
        $overwrite = true;
        if (file_exists($destination)) {
            unlink($destination);
        }
        $result = Create_zip($files, $destination, $overwrite);
        if ($result == true) {
            echo $zip.".zip";
        } else {
            echo "Failed to create ZIP file"; 
        }
    } else {
        echo "The uploaded file is empty"; 
    }
} else {
    echo "Error uploading file";
}

function Create_zip($files = array(), $destination = '', $overwrite = false)
{
    // Validate files array
    if (!is_array($files) || empty($files)) {
        return false;
    }
    
    // Account for overwrite == false
    if (file_exists($destination) && !$overwrite) {
        return false; 
    }
    
    // Create ZIP file
    $zip = new ZipArchive();
    $mode = $overwrite ? (ZipArchive::CREATE | ZipArchive::OVERWRITE) : ZipArchive::CREATE;
    if ($zip->open($destination, $mode) !== true) {
        return false; 
    }
    
    // Add files to ZIP
    foreach ($files as $file) {
        if (file_exists($file)) {
            $zip->addFile($file, basename($file));
        }
    }
    
    // Close ZIP archive
    $zip->close();
    
    // Return TRUE if ZIP has been created
    return file_exists($destination);
}

//Delete everything in a directory
function clearDirectory($dir)
{
    if (!file_exists($dir) || !is_dir($dir)) {
        return false; 
    }

    //Loop through each item in the directory
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        //If the item is a directory, recursively delete its contents
        if (is_dir($path)) {
            clearDirectory($path);
            rmdir($path);//Delete the subdirectory itself
        } else {
            unlink($path);
        }
    }
    return true;
}

?>
