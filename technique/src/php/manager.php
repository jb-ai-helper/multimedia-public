<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Get Location from URL
$location = isset($_GET["loc"]) ? $_GET["loc"] : null;
if($location == 'residence' || $location == 'res') $location = 'RES';
else if($location == 'ptf') $location = 'PTF';

function BuildTable($demande)
{    
    $DeliveryDate = new DateTime($demande['delivery']);
    $DeliveryDateFormatted = $DeliveryDate->format("d/m/Y");

	//Create Location Filter URL
	$baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
	$params = $_GET;
	$params['loc'] = $demande['location'];
	$LocationUrl = $baseUrl . '?' . http_build_query($params);
	
	$Row = '<tr>';
    $Row.= '<td>'.$DeliveryDateFormatted.'</td>';
    $Row.= '<td><a href="'.htmlspecialchars($LocationUrl).'" target="_self">'.$demande['location'].'</a></td>';
    $Row.= '<td class="intervention" onClick="Open(this)" data-ref="'.$demande['ref'].'" data-service="'.$demande['service'].'">'.$demande['name'];
    $Row.= '<div class="status '.$demande['status'].'"></div></td>';
    $Row.= '</tr>';
    return($Row);
}

$TargetDate = new DateTime();

if (isset($_GET['archives'])) {
    if (!empty($_GET['archives'])) {
        $ReferencePoint = strtotime("last day of december " . $_GET['archives']);
    } else {
        $ReferencePoint = strtotime("last day of december " . date("Y"));
    }
    $TargetDate->setTimestamp($ReferencePoint);
    $title = "archivées en " . $TargetDate->format("Y");
    $link = "";
    $page = "En cours...";
} else {
    $ReferencePoint = strtotime("today midnight");
    $TargetDate->setTimestamp($ReferencePoint);
    $title = "en cours au " . $TargetDate->format("d/m/Y");
    $link = "?archives";
    $page = "Consulter les archives";
}

$dir = "interventions/";
$folder = opendir($dir);
$XMLfiles = array();

while ($file = readdir($folder)) {
    if (strpos($file, '.xml') !== false) {
        array_push($XMLfiles, $file);
    }
}

closedir($folder);    
unset($file);

$Demandes = array();

for ($d = 0; $d <= count($XMLfiles)-1; $d++) {
    $demande = simplexml_load_file("interventions/".$XMLfiles[$d]) or die('Erreur lors du chargement, le fichier "'.$XMLfiles[$d].'" n\'existe pas.');
    $ref = basename($XMLfiles[$d], ".xml");

    $Demandes[$d] = array(
        'ref' => $ref,
        'service' => (string)$demande->applicant->service,
        'name' => (string)$demande->applicant->name,
        'delivery' => (string)$demande->object->delivery,
        'status' => (string)$demande->object->status,
		'location' => (string)$demande->object->location
    );
	
	//Ajust Location
	if (str_starts_with($Demandes[$d]['location'], "RES-") || str_starts_with($Demandes[$d]['location'], "PTF-")) {
		$parts = explode("-", $Demandes[$d]['location'], 2);
		$Demandes[$d]['location'] = $parts[0];
	}

}

// Sort by delivery date
usort($Demandes, function ($a, $b) {
    return strtotime($a['delivery']) <=> strtotime($b['delivery']);
});

$CurrentYear = $TargetDate->format("Y");
$Years = array();
$Table = '';
$Total = 0;

// Filter and create rows
foreach ($Demandes as $demande) {
	// Filtre "loc" si défini
	$matchLocation = true;
	if ($location !== null) {
		$matchLocation = ($demande['location'] == $location);
	}

    if (isset($_GET['archives'])) {        
        //Archive Page
        $UpperLimit = $ReferencePoint;
        $LowerLimit = strtotime("midnight first day of january ".$CurrentYear);
        $DemandeDate = strtotime($demande['delivery']);
        
        if (($demande['status'] == 'canceled' || $demande['status'] == 'closed') && $matchLocation){
            if($DemandeDate >= $LowerLimit && $DemandeDate <= $UpperLimit){
                $Table .= BuildTable($demande);
                $Total++;
            } else {
                // Get Other Year
                $FileYear = new DateTime($demande['delivery']);
                $FileYear = $FileYear->format("Y");
                // Place it in the list
                if (!in_array($FileYear, $Years)) {
                    array_push($Years, $FileYear);
                }
            }
        }
    } else {
        //Regular Page
        if ($demande['status'] != 'canceled' && $demande['status'] != 'closed' && $matchLocation) {
            $Table .= BuildTable($demande);
            $Total++; 
        }
    }
}

// Add Years to Archive Page
$ArchivedYears = '';
if (isset($_GET['archives'])) {
    // Make sure current year is in the list and sort list
    if (!in_array($CurrentYear, $Years)) {
        array_push($Years, $CurrentYear);
    }
    sort($Years);

    // Add other archived years
    if (count($Years) > 0) {
        foreach ($Years as $otheryear) {
            if ($otheryear != $CurrentYear) {
                $ArchivedYears .= '<a href="?archives='.$otheryear.'">'.$otheryear.'</a>&nbsp;|&nbsp;';
            } else {
                $ArchivedYears .= '<b>'.$otheryear.'</b>&nbsp;|&nbsp;';
            }
        }
    }
}

if ($Total < 10) {
    $Total = "0".$Total;
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="src/css/manager.css">
    <script src="src/js/technique.js"></script>
    <link rel="icon" href="favicon.ico" />
    <title>Interventions Technques</title>
</head>
<body>
    <h1>Request Manager</h1>
    <h3>
        Demandes <?php echo $title ?>
        <div id="Total"><?php echo $Total;?></div>
        <div id="New" data-ref="" onClick="Open(this)"></div>
    </h3>
    <table>
        <thead>
            <tr>
                <th>Livrable</th>
                <th>Lieu</th>
                <th>Demande</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $Table ?>
        </tbody>
    </table>
    <div id="archives"><?php echo $ArchivedYears ?><a href="gestion.php<?php echo $link ?>"><?php echo $page ?></a></div>
    <div id="CSV" onClick="DownloadCSV()">Exporter en CSV</div>
</body>
</html>
