<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

function FormatDate($DATE,$TYPE)
{
    //Import French Month array
    global $french_month;

    $Date = explode('-', $DATE);
    $year = $Date[0];
    $month = $Date[1];
    $day = $Date[2];

    if($TYPE == 'video') { return $day." ".$french_month[(int)$month]." ".$year;
    } elseif($TYPE == 'shooting') { return $day.'/'.$month.'/'.$year;
    }
}

function BuildTable($demande)
{    
    $CreationDate = new DateTime();
    $CreationDate->setTimestamp($demande['ddate']);
    $CreationDate = date_format($CreationDate, "d/m/Y");

    $ShootingDate = new DateTime();
    $ShootingDate->setTimestamp($demande['sdate']);
    $ShootingDate = date_format($ShootingDate, "d/m/Y");

    if (preg_match('/<br>([^<]*)$/', rtrim($demande['locaion'], '<br>'), $matches)) {
        $ShortenLocation = trim($matches[1]);
    } else {
        $ShortenLocation = trim($demande['locaion']);
    }
    
    $DeleteOption = '<div class="delete" onclick="DeleteFiche(\''.$demande['ddate'].'\')">&#10006;</div>';
    if(isset($_GET['archives'])) { $DeleteOption = ""; 
    }

    $Row = '<tr>';
    $Row.= '<td>'.$demande['ddate'].'</td>';
    $Row.= '<td>'.$CreationDate.'</td>';
    $Row.= '<td>'.$ShootingDate.'</td>';
    $Row.= '<td class="link">'.$DeleteOption.'<a href="../fiche/?ref='.$demande['ddate'].'" target="_blank">'.$demande['title'].'</a>'.'</td>';
    $Row.= '<td>'.$ShortenLocation.'</td>';
    $Row.= '<td>'.$demande['realease'].'</td>';
    $Row.= '</tr>';
    return($Row);
}

if(isset($_GET['archives'])) {
    if(!empty($_GET['archives']) && $_GET['archives'] != date("Y")) { $Now = strtotime("last day of december ".$_GET['archives']); 
    }
    else{ $Now = strtotime("yesterday midnight"); 
    }

    $title = "archivées";
    $link= "";
    $page = "En cours...";
}
else
{
    $Now = strtotime("today midnight");
    $title = "en cours";
    $link= "?archives";
    $page = "Consulter les archives";
}

$DateTime = new DateTime();
$DateTime->setTimestamp($Now);
$Today = date_format($DateTime, "d/m/Y");
$CurrentYear = date_format($DateTime, "Y");

$dir = "events/";
$folder = opendir($dir);
$XMLfiles = array();

while ($file = readdir($folder))
{
    if (strpos($file, '.xml') == true) {
        array_push($XMLfiles, $file);
    }
}

closedir($folder);    
unset($file);

$Demandes = array();

for($d = 0; $d <= count($XMLfiles)-1; $d++)
{
    $fiche = simplexml_load_file("events/".$XMLfiles[$d]) or die('Erreur lors du chargement, le fichier "'.$XMLfiles[$d].'" n\'existe pas.');
    $ref = basename($XMLfiles[$d], "."."xml");

    $Demandes[$d] = array(
        'ddate' => $ref,
        'sdate' => strtotime($fiche->event->date." ".$fiche->event->start),
        'title' => strip_tags($fiche->video->title),
        'locaion' => $fiche->event->location,
        'realease' => $fiche->event->release,
    );
}

//Sort by Shooting date
usort(
    $Demandes, function ($a, $b) {
        if ($a['sdate'] > $b['sdate']) {return 1;
        }
        elseif ($a['sdate'] < $b['sdate']) {return -1;
        }
        return 0;
    }
);

$Years = array();
$Total = 0;

//Filter and create rows
foreach($Demandes as $demande)
{
    //Archive Page
    if(isset($_GET['archives'])) {        
        $UpperLimit = strtotime("tomorrow midnight", $Now) - 1;
        $LowerLimit = strtotime("midnight first day of january ".$CurrentYear);
        
        if($demande['sdate'] <= $UpperLimit && $demande['sdate'] >= $LowerLimit) { $Table.= BuildTable($demande); $Total++; 
        } else {
            //Get Other Year
            $FileYear = new DateTime();
            $FileYear->setTimestamp($demande['sdate']);
            $FileYear = date_format($FileYear, "Y");
            //Place it in the list
            if(!in_array($FileYear, $Years)) { array_push($Years, $FileYear); 
            }
        }
    } else {
        //Regular Page
        if($demande['sdate'] >= $Now) {
            $Table.= BuildTable($demande); $Total++;
        }
    }
}

//Add Years to Archive Page
if(isset($_GET['archives'])) {
    //Make sure current year is in the list ans sort list
    if(!in_array($CurrentYear, $Years)) { array_push($Years, $CurrentYear); 
    }
    sort($Years);

    //Add other archived years
    if(count($Years) > 0) {
        foreach($Years as $otheryear)
        {
            if($otheryear != $CurrentYear) { $ArchivedYears.= '<a href="?archives='.$otheryear.'">'.$otheryear.'</a>&nbsp;|&nbsp;'; 
            }
            else{ $ArchivedYears.= '<b>'.$otheryear.'</b>&nbsp;|&nbsp;'; 
            }
        }
    }
}

if($Total < 10) { $Total = "0".$Total; 
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="src/css/manager.css">
    <script src="src/js/fiche.js"></script>
    <link rel="icon" href="favicon.ico" />
    <title>Request Manager</title>
</head>
<body>
    <h1>Request Manager</h1>
    <h3>Demandes <?php echo $title ?> au <?php echo $Today;?><div id="Total"><?php echo $Total;?></div></h3>
    <table>
        <tr>
            <th>Référence</th>
            <th>Création</th>
            <th>Tournage</th>
            <th>Titre</th>
            <th>Localisation</th>
            <th>Diffusion</th>
        </tr>
        <?php echo $Table ?>
    </table>
    <div id="archives"><?php echo $ArchivedYears ?><a href="manager.php<?php echo $link ?>"><?php echo $page ?></a></div>
</body>
</html>
