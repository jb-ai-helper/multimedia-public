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
<title>Short Links Manager</title>
</head>
<body>
    <h1>Short Links Manager</h1>
    <h3>Liens Actifs<div onClick="AddLink()" id="AddLink">+</div></h3>
    <table id="ActiveLinks">
        <thead>
            <tr>
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

            $links = GetLinks("data");

            $table = "<tbody>";
            foreach ($links as $link) {
                $title = $link['title'];
                $title_safe = str_replace("&#034;", "\"",$title);// Escape Double Quotes for JS
                $title_safe = str_replace("&#039;", "\'",$title);// Escape Single Quotes for JS

                $table.= "<tr class=\"".$link['class']."\">";
                $table.= "\n\t<td class=\"LinkTitle\">".$title;
                $table.= "<div class=\"edit\" onclick=\"EditTitle('".$link['ref']."','".$title_safe."')\">&#9998;</div>";
                $table.= "</td>";
                $table.= "\n\t<td class=\"FullLink\"><a href=\"".$link['url']."\" target=\"_blank\">".$link['url']."</a>";
                $table.= "<div class=\"edit\" onclick=\"EditLink('".$link['ref']."','".$link['url']."')\">&#9998;</div>";
                $table.= "</td>";
                $table.= "\n\t<td class=\"ShortLink\"><em onclick=\"CopyShortLink(this)\">".$link['ref']."</em>";
                $table.= "<div class=\"edit\" onclick=\"EditRef('".$link['ref']."')\">&#9998;</div>";
                $table.= "<div class=\"delete\" onclick=\"DeleteLink('".$link['ref']."')\">&#10006;</div>";
                $table.= "</td>";
                $table.= "\n\t<td class=\"ClickCount\">".$link['clicks']."</td>";
                $table.= "</tr>";
            }
            $table .= "</tbody>";
            echo $table;
            ?>
    </table>
</body>

</html>
