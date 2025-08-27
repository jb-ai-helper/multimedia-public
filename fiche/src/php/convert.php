<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Convert Request</title>
</head>

<body>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Common Functions
require '../../../src/php/commontools.php';

// Ensure all required functions are defined
if (!function_exists('formatStreamInfo') || !function_exists('formatLowerThird') || !function_exists('formatTransition') || !function_exists('formatScrollingBanner') || !function_exists('shortenFunction') || !function_exists('shortenTransition') || !function_exists('shortenScrollingBanner') || !function_exists('generateFileName')) {
    die('Required functions are missing.');
}

function Convert2Presets($Collection, $StreamInfo, $LowerThirds, $Transitions, $ScrollingBanners)
{
    $results = '';

    // Create directories if they don't exist
    $directories = ["streams", "names", "chapters", "banners"];
    foreach ($directories as $dir) {
        $path = "../../../streaming/data/".$Collection."/".$dir."/";
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            $results .= '- Directory ' . $path . ' could not be created.' . "\n";
        }
    }
    
    // Convert Stream Info
    if (isset($StreamInfo) && !empty($StreamInfo)) {
        $folder = "../../../streaming/data/" . $Collection . "/streams/";

        // Get already saved content
        $AllReadySaved = getAllReadySaved($folder);

        // Prepare file content
        $file_content = formatStreamInfo($StreamInfo);

        // Check if file content is not already saved
        if (!in_array($file_content, $AllReadySaved)) {
            // Prepare File URL
            $file = generateFileName($folder);
            $file_url = $folder . $file . ".txt";

            // Create file
            $open = fopen($file_url, "w");

            // Check if file opened successfully
            if ($open === false) {
                $results .= '- Stream Info ('.$file_url.') could not be opened for writing.'."\n";
            } else {
                // Write to file while testing if writing successful
                if (fwrite($open, $file_content) === false) {
                    // Write details to results if failed
                    $results.= '- Stream Info ('.$file_url.') could not be written.'."\n";
                } else {
                    $results.= $file;
                }
                // Close File
                fclose($open);
            }
        } else {
            $results .= array_search($file_content, $AllReadySaved);
        }
    }
    
    // Convert Lower Third
    if (isset($LowerThirds) && count($LowerThirds) > 0) {
        $folder = "../../../streaming/data/".$Collection."/names/";
        
        // Get already saved content
        $AllReadySaved = getAllReadySaved($folder);
        
        foreach ($LowerThirds as $lowerThird) {
            // Prepare file content
            $file_content = formatLowerThird($lowerThird);
            echo $file_content;
            
            // Check if file content is not already saved
            if (!in_array($file_content, $AllReadySaved)) {
                // Prepare File URL
                $file = generateFileName($folder);
                $file_url = $folder.$file.".txt";
                // Create file
                $open = fopen($file_url, "w");
                // Check if file opened successfully
                if ($open === false) {
                    $results .= '- Lower Third ('.$file_url.') could not be opened for writing.'."\n";
                } else {
                    // Write to file while testing if writing successful
                    if (fwrite($open, $file_content) === false) {
                        // Write details to results if failed
                        $shorten_lowerthird = $lowerThird['name'].' '.shortenFunction($lowerThird['function']);
                        $results .= '- Lower Third for '.$shorten_lowerthird.' ('.$file_url.')'."\n";
                    }
                    // Close File
                    fclose($open);
                }
            }
        }
    }
    
    // Convert Transitions
    if (isset($Transitions) && count($Transitions) > 0) {
        $folder = "../../../streaming/data/".$Collection."/chapters/";
        
        // Get already saved content
        $AllReadySaved = getAllReadySaved($folder);

        foreach ($Transitions as $html) {
            // Prepare file content
            $file_content = formatTransition($html);
            
            // Check if file content is not already saved
            if (!in_array($file_content, $AllReadySaved)) {
                // Prepare File URL
                $file = generateFileName($folder);
                $file_url = $folder.$file.".txt";
                // Create file
                $open = fopen($file_url, "w");
                // Check if file opened successfully
                if ($open === false) {
                    $results .= '- Transition ('.$file_url.') could not be opened for writing.'."\n";
                } else {
                    // Write to file while testing if writing successful
                    if (fwrite($open, $file_content) === false) {
                        // Write details to results if failed
                        $results .= '- Transition for "'.shortenTransition($html).'" ('.$file_url.')'."\n";
                    }
                    // Close File
                    fclose($open);
                }
            }
        }
    }
    
    // Convert Scrolling Banners
    if (isset($ScrollingBanners) && count($ScrollingBanners) > 0) {
        $folder = "../../../streaming/data/".$Collection."/banners/";
        
        // Get already saved content
        $AllReadySaved = getAllReadySaved($folder);
        
        foreach ($ScrollingBanners as $scrollingBanner) {
            // Prepare file content
            $file_content = formatScrollingBanner($scrollingBanner);
            
            // Check if file content is not already saved
            if (!in_array($file_content, $AllReadySaved)) {
                // Prepare File URL
                $file = generateFileName($folder);
                $file_url = $folder.$file.".txt";
                // Create file
                $open = fopen($file_url, "w");
                // Check if file opened successfully
                if ($open === false) {
                    $results .= '- Scrolling Banner ('.$file_url.') could not be opened for writing.'."\n";
                } else {
                    // Write to file while testing if writing successful
                    if (fwrite($open, $file_content) === false) {
                        // Write details to results if failed
                        $results .= '- Scrolling Banner for "'.shortenScrollingBanner($scrollingBanner['message']).'" ('.$file_url.')'."\n";
                    }
                    // Close File
                    fclose($open);
                }
            }
        }
    }
    
    return $results;
}

function getAllReadySaved($folder)
{
    $AllReadySaved = array();
    
    if ($target = opendir($folder)) {
        while (($file = readdir($target)) !== false) {
            if (is_file($folder . $file) && strpos($file, '.txt') !== false) {
                $FileContent = file_get_contents($folder . $file);
                $AllReadySaved[basename($file, ".txt")] = $FileContent;
            }
        }
        closedir($target);
    } else {
        // Gestion des erreurs si le dossier ne peut pas être ouvert
        echo "Erreur : Impossible d'ouvrir le dossier $folder.";
    }

    return $AllReadySaved;
}

$ref = $_POST["ref"] ?? null;
$Collection = $_POST["collection"] ?? null;

if (is_null($ref) || is_null($Collection)) {
    die("Ref or Collection is missing");
}

$XML = "../../events/".$ref.".xml";

if (!file_exists($XML)) {
    die("XML file does not exist");
}

// Load XML into array
$fiche = simplexml_load_file($XML, "SimpleXMLElement", LIBXML_NOCDATA);

if ($fiche === false) {
    die("Failed to load XML file");
}

$fiche_json = json_encode($fiche);
$fiche_array = json_decode($fiche_json, true);

$duration = 0;

$StreamInfo = array(
    "title" => $fiche_array['event']['title'],
    "subtitle" => $fiche_array['event']['subtitle'],
    "style" => $fiche_array['event']['style'],
    "date" => $fiche_array['video']['date']
);

$LowerThirds = array();
foreach ($fiche_array['speaker'] as $speaker) {
    $LowerThird = array(
        "name" => isset($speaker['name']) ? ensureString($speaker['name']) : '',
        "function" => isset($speaker['function']) ? ensureString($speaker['function']) : '',
        "translation" => isset($speaker['translation']) ? ensureString($speaker['translation']) : '',
    );
    array_push($LowerThirds, $LowerThird);
}

$Transitions = array();
$ScrollingBanners = array();

foreach ($fiche_array['sequence'] as $sequence) {
    if ($sequence['type'] == "transition") {
        array_push($Transitions, $sequence['html']); 
    } else {
        $duration = $duration + (int)$sequence['duration'];
        if ($sequence['type'] == "speech" && isset($sequence['extra']['scroll']['checked']) && $sequence['extra']['scroll']['checked'] == "true") {
            $ScrollingBanner = array(
                "message" => $sequence['extra']['scroll']['message'],
                "class" => 'none' // Keep class système alive (ancien feature)
            );
            array_push($ScrollingBanners, $ScrollingBanner);
        }
    }
}

// Setup error system
$results = Convert2Presets($Collection, $StreamInfo, $LowerThirds, $Transitions, $ScrollingBanners);
$additional_objects = "";

// Success = no ERROR 
if (is_numeric($results)) {
    $message = 'Votre demande de prestation multimédia a bien été convertie dans la collection \"'.$Collection.'\". Voulez-vous passer à l\'étape suivante ?';        

    //Set Time Zone
    $timezone = new DateTimeZone('Europe/Paris');

    // Format Start Date
    $start_date = explode("-", $fiche_array['event']['date']);
    $start_time = date("H:i:s", strtotime($fiche_array['event']['start']));
    $start_time = explode(":", $start_time);
    $startDateTime = new DateTime();
    $startDateTime->setTimezone($timezone);
    $startDateTime->setDate($start_date[0], $start_date[1], $start_date[2]);
    $startDateTime->setTime($start_time[0], $start_time[1], $start_time[2]);
    $start = $startDateTime->format('c');// 'c' is the same as ISO8601, but with the correct time zone offset

    // Format End Date
    $englishStart = $fiche_array['event']['date']." ".$fiche_array['event']['start'];
    $end_date = date('Y-m-d', strtotime($englishStart. ' + '.$duration.' minutes'));
    $end_date = explode("-", $end_date);
    $end_time = date('H:i:s', strtotime($englishStart. ' + '.$duration.' minutes'));
    $end_time = explode(":", $end_time);
    $endDateTime = new DateTime();
    $endDateTime->setTimezone($timezone);
    $endDateTime->setDate($end_date[0], $end_date[1], $end_date[2]);
    $endDateTime->setTime($end_time[0], $end_time[1], $end_time[2]);
    $end = $endDateTime->format('c');// 'c' is the same as ISO8601, but with the correct time zone offset

    // Format description for YouTube API
    $description = $fiche_array['video']['description'];
    $description = addslashes($description);
    $description = preg_replace('/<br\s?\/?>/i', "\\n", $description);

    // Create VideoObject
    $additional_objects .= "const VideoObject = new Object();";
    $additional_objects .= "VideoObject.title = \"".addslashes($fiche_array['video']['title'])."\";";
    $additional_objects .= "VideoObject.description = \"".$description."\";";
    $additional_objects .= "VideoObject.visibility = \"".$fiche_array['video']['visibility']."\";";
    $additional_objects .= "VideoObject.start = \"".$start."\";";
    $additional_objects .= "VideoObject.end = \"".$end."\";";

    // Create EventObject
    $additional_objects .= "const EventObject = new Object();";
    $additional_objects .= "EventObject.text = \"".$Collection."/".$results."\";";
    $additional_objects .= "EventObject.details = \"https://multimedia.enpjj.fr/fiche/?ref=".$ref."\";";
    $additional_objects .= "EventObject.location = \"".addslashes($fiche_array['event']['location'])."\";";
    $additional_objects .= "EventObject.release = \"".$fiche_array['event']['release']."\";";
    $additional_objects .= "EventObject.start = \"".$start."\";";
    $additional_objects .= "EventObject.end = \"".$end."\";";
} else {
    // Failure ERROR is not empty / display error message in dialogue box
    $message = 'Erreur lors de la conversion de votre demande de prestation multimédia :\n\n'.$results.'\n\n'.'Supprimez la collection puis recommencez (F5).'; 
}

// Alert error or Confirm success and next step via dialogue box in parent window
echo '<script type="text/javascript">';
echo $additional_objects;
echo 'parent.Convert2Schedule("'.$message.'", VideoObject, EventObject);';
echo '</script>';

?>
</body>
</html>
