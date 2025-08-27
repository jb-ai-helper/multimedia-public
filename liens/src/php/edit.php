<?php
    
$ref = $_POST["ref"];
$file_path = "../../data/".$ref.".txt";

if (is_file($file_path)) {
	if($_POST["link"]){
		$link = $_POST["link"];
		if (file_put_contents($file_path, $link)) {
            echo 'success';
        } else {
            echo 'Le lien n\'a pas pu être modifié !';
        }
	} elseif ($_POST["title"]) {
		$title = $_POST["title"];
        $lines = file($file_path, FILE_IGNORE_NEW_LINES);
        $url = strip_tags($lines[0]);
        $link = '<url title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</url>';
        $lines[0] = $link;
        if (file_put_contents($file_path, implode(PHP_EOL, $lines))) {
            echo 'success';
        } else {
            echo 'Le titre n\'a pas pu être modifié !';
        }
	} elseif ($_POST["rename"]) {
		$rename = $_POST["rename"];
        $rename = preg_replace('/[^A-Z0-9]/', '', strtoupper($rename));;// Makes sure the new name is in uppercase and only letters or numbers
        $new_path = "../../data/".$rename.".txt";
        if (!is_file($new_path)) {
            if (rename($file_path, $new_path)) {
                echo 'success';
            } else {
                echo 'La référence n\a pas pu être renommée !';
            }            
        } else {
            echo 'Cette référence est déjà utilisée !';
        }
    }
} else {
    echo 'La référence spécifiée est introuvable !';
}
?>
