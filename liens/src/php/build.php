<?php

function GetLinks($data) {
    
    $files = scandir($data);
    $links = [];

    foreach ($files as $txt) {
        if ($txt !== "." && $txt !== ".." && pathinfo($txt, PATHINFO_EXTENSION) === 'txt') {
            $file_path = $data . "/" . $txt;
            $lines = file($file_path, FILE_IGNORE_NEW_LINES);
            $clicks = count($lines) - 1; // moins la première ligne
            $firstline = $lines[0];
            $class = "";

            if (preg_match('/<url title="(.+?)">(.+?)<\/url>/', $firstline, $matches)) {
                $title = $matches[1];
                $url = $matches[2];
            } else {
                $url = trim($firstline);
                $title = getPageTitle($url);
                $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                // Transforme l'URL en <url title="TITRE">URL</url>
                $firstline = '<url title="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'</url>';
                $lines[0] = $firstline;
                // Réécrit le fichier avec le nouveau format
                file_put_contents($file_path, implode(PHP_EOL, $lines));
                $class = "new";
            }

            $ref = pathinfo($txt, PATHINFO_FILENAME);

            // Add the data to the array
            $links[] = [
                'class' => $class,
                'title' => $title,
                'url' => $url,
                'ref' => $ref,
                'clicks' => $clicks
            ];
        }
    }

    // Sort the array by title
    usort($links, function($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    return $links;
}

?>