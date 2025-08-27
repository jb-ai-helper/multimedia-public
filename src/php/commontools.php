<?php

// A user-defined error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    echo "<b>Custom error:</b> [$errno] $errstr<br>";
    echo " Error on line $errline in $errfile<br>";
}
// Set user-defined error handler function
set_error_handler("myErrorHandler");

//Retourne text sans accents et en majuscule
function nettoyerChaine($chaine) {
    $chaine = mb_strtoupper($chaine, 'UTF-8');
    $chaine = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $chaine);
    $chaine = preg_replace('/[^A-Z0-9\-]/', '_', $chaine);
    $chaine = preg_replace('/_+/', '_', $chaine);
    return trim($chaine, '_');
}

// Generate random 4 digit name, taking existing folder content into account
function generateFileName($folder)
{
    // Make a list of existing files
    $dir = opendir($folder);
    $file_list = array();
    while (($liste = readdir($dir)) !== false) {
        if (strpos($liste, '.txt') !== false) {
            $file_list[] = basename($liste, '.txt'); 
        }
    }
    closedir($dir);
    
    // Generate New File Name
    do {
        $file = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    } while (in_array($file, $file_list));
    
    return $file;
}

// Get and Format Stream Info
function formatStreamInfo($StreamInfo)
{
    // Check if required keys exist
    $Title = isset($StreamInfo['title']) ? strip_tags($StreamInfo['title'], '<br><sup>') : '';
    $SubTitle = isset($StreamInfo['subtitle']) ? strip_tags($StreamInfo['subtitle'], '<br><sup>') : '';
    $Date = isset($StreamInfo['date']) ? $StreamInfo['date'] : '';
    $Style = isset($StreamInfo['style']) ? $StreamInfo['style'] : '';

    // Return Formatted Stream Info
    return $Title . "\r\n" . $SubTitle . "\r\n" . $Date . "\r\n" . $Style;
}

// Get and Format Lower Third
function formatLowerThird($LowerThird)
{
    $name = isset($LowerThird['name']) ? strip_tags($LowerThird['name']) : '';
    $function = isset($LowerThird['function']) ? strip_tags($LowerThird['function'], "<br>") : '';
    $translation = isset($LowerThird['translation']) ? strip_tags($LowerThird['translation'], "<br>") : '';
    
    return $name . "\r\n" . $function . "\r\n" . $translation;
}

// Shorten Functions for list
function shortenFunction($function)
{
    if (strlen($function) > 20) {
        $cleanend = strrpos(substr($function, 0, 20), " ");
        if ($cleanend === false) {
            $cleanend = 20;
        }
        $shorten_function = substr($function, 0, $cleanend) . "...";
        $shorten_function = "(" . $shorten_function . ")";
    } elseif (strlen($function) != 0) {
        $shorten_function = "(" . $function . ")";
    } else {
        $shorten_function = '';
    }
    return $shorten_function;
}

// Format Transitions
function formatTransition($html)
{
    return strip_tags($html, ['<i>', '<b>', '<br>', '<h1>', '<h2>', '<h3>', '<img>']);
}

// Shorten Transition for list
function shortenTransition($content)
{
    // Setup Preview (shorten to 40 characters, find last space and add '...')
    $shorten_content = str_replace("<br>", " ", trim($content));
    $shorten_content = trim(strip_tags($shorten_content));

    if (strlen($shorten_content) > 40) {
        $cleanend = strrpos(substr($shorten_content, 0, 40), " ");
        if ($cleanend === false) {
            $cleanend = 40;
        }
        $shorten_content = substr($shorten_content, 0, $cleanend) . "...";
    }

    // Make sure something is displayed
    if ($shorten_content == "") {
        $shorten_content = "[HTML]";
    }

    return $shorten_content;
}

// Format Scrolling Banner
function formatScrollingBanner($ScrollingBanner)
{
    return strip_tags($ScrollingBanner['message'], '<i><b>') . "\r\n" . $ScrollingBanner['class'];
}

// Shorten Scrolling Banner for list
function shortenScrollingBanner($content)
{
    // Setup Preview (shorten to 60 characters, find last space and add '...')
    $shorten_content = strip_tags($content);

    if (strlen($shorten_content) > 60) {
        $cleanend = strrpos(substr($shorten_content, 0, 60), " ");
        if ($cleanend === false) {
            $cleanend = 60;
        }
        $shorten_content = substr($shorten_content, 0, $cleanend) . "...";
    }

    // Make sure something is displayed
    if ($shorten_content == "") {
        $shorten_content = "[EMPTY]";
    }

    return $shorten_content;
}

//Return Stream Key from "Pretty" name
function returnStreamKey($location)
{
    if ($location == "Amphi Condorcet Bas") {
        return 'condorcet-bas';
    } elseif ($location == "Amphi Condorcet Haut") {
        return 'condorcet-haut';
    } elseif ($location == "Amphi Michelet") {
        return 'michelet';
    } elseif ($location == "Amphi Costa") {
        return 'costa';
    } elseif ($location == "Lab LEBAS") {
        return 'mobile';
    } elseif ($location == "Lab Médiathèque") {
        return 'mobile';
    } elseif ($location == "Studio") {
        return 'studio';
    } elseif (isset($location)) {
        return 'mobile';//Other location
    } else {
        return 'preview'; 
    }//No location
}

function getPageTitle($url) {
    // Initialize a cURL session
    $curl = curl_init();
    
    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Execute the cURL request
    $response = curl_exec($curl);
    
    // Check if the response is not false
    if ($response !== false) {
        // Ensure the response is in UTF-8 encoding
        $encoding = mb_detect_encoding($response, 'UTF-8, ISO-8859-1', true);
        if ($encoding !== 'UTF-8') {
            $response = mb_convert_encoding($response, 'UTF-8', $encoding);
        }
        
        // Create a new DOMDocument instance
        $dom = new DOMDocument();
        
        // Suppress errors due to invalid HTML structure
        libxml_use_internal_errors(true);
        
        // Load the HTML response into the DOMDocument instance
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $response);
        
        // Restore error handling
        libxml_clear_errors();
        
        // Extract the title tag content
        $titleNodes = $dom->getElementsByTagName('title');
        
        if ($titleNodes->length > 0) {
            $title = $titleNodes->item(0)->textContent;
            
            // Shorten the title if it is more than 50 characters
            if (strlen($title) > 50) {
                $shortenedTitle = substr($title, 0, 50);
                $lastSpacePos = strrpos($shortenedTitle, ' ');
                
                if ($lastSpacePos !== false) {
                    $title = substr($shortenedTitle, 0, $lastSpacePos);
                } else {
                    $title = $shortenedTitle;
                }
                
                $title .= '...';
            }
        } else {
            $title = "Title not found";
        }
    } else {
        $title = "cURL Error: " . curl_error($curl);
    }
    
    // Close the cURL session
    curl_close($curl);
    
    // Return the title
    return $title;
}

function ensureString($value) {
    if (is_array($value)) {
        return empty($value) ? '' : json_encode($value);
    }
    return $value;
}

function addBusinessDays(DateTime $date, int $businessDays) {
    $delivery = clone $date;
    while ($businessDays > 0) {
        $delivery->modify('+1 day');
        // Check if the day is a weekend
        if ($delivery->format('N') < 6) { // 'N' format returns 1 for Monday and 7 for Sunday
            $businessDays--;
        }
    }
    return $delivery;
}

?>
