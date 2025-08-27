<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*---------- Global Variables ----------*/

// Dossier de DATA
$data = "data";

/*---------- Active Code ----------*/

// Vérifie si une référence est passée dans l'URL
if (isset($_GET['ref'])) {
    $ref = strtoupper($_GET['ref']);
    $file_path = $data . "/" . $ref . ".txt";

    // Vérifie si le fichier existe
    if (file_exists($file_path)) {
        // Récupère la date et l'heure actuelles
        $datetime = date("Y-m-d H:i:s");
        // Ouvre le fichier en lecture
        $file = fopen($file_path, "r+");
        if ($file) {
            // Lit la première ligne du fichier
            $link = fgets($file);
            // Récupère le lien brut
            $link = strip_tags($link); //Get the URL only
            //$link = trim($link);
            $link = html_entity_decode($link); //Make sure no HTML entity are added
            // Déplace le pointeur à la fin du fichier pour ajouter des informations
            fseek($file, 0, SEEK_END);
            // Ajoute une ligne avec la date et l'heure actuelles
            fwrite($file, "\n".$datetime);
            // Ferme le fichier
            fclose($file);

            // Redirige vers l'URL lue
            $link = htmlspecialchars_decode($link, ENT_QUOTES | ENT_HTML5);
            header("Location: " . $link);
            exit(); // Arrête l'exécution du script après la redirection
        }
    } else {
        header("Location: https://multimedia.enpjj.fr/");
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="/src/css/fonts.css">
    <link rel="stylesheet" href="src/css/links.css">
    <script src="/src/js/commontools.js"></script>
    <script src="src/js/links.js"></script>
    <link rel="icon" href="favicon.ico" />
<title>Click Counter</title>
</head>
<body onLoad="<?php echo $onload; ?>">
    <h1>Click Counter</h1>
    <h3>Liens Actifs<div onClick="AddLink()" id="AddLink">+</div></h3>
    <table id="ActiveLinks">
        <thead>
            <tr>
                <!--<th>Titre</th>-->
                <th>Titre</th>
                <th>Adresses</th>
                <th>Références</th>
                <th>Clicks</th>
            </tr>
        </thead>
        <?php
        //Include Common Functions
        require '../src/php/commontools.php';
        require 'src/php/build.php';

        $links = GetLinks($data);
        
        $table = "<tbody>";
        foreach ($links as $link) {
            $table .= "<tr class=\"".$link['class']."\">";
            $table .= "\n\t<td class=\"LinkTitle\">".$link['title']."</td>";
            $table .= "\n\t<td class=\"FullLink\"><a href=\"".$link['url']."\" target=\"_blank\">".$link['url']."</a>";
            $table .= "</td>";
            $table .= "\n\t<td class=\"ShortLink\"><em onclick=\"CopyShortLink(this)\">".$link['ref']."</em>";
            $table .= "</td>";
            $table .= "\n\t<td class=\"ClickCount\">".$link['clicks']."</td>";
            $table .= "</tr>";
        }
        $table .= "</tbody>";        

        echo $table;
        ?>
    </table>
</body>

</html>
