<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../src/php/commontools.php';

//Default NOT LOCKED
$lock = '';
$boss_key = file_get_contents('src/keys/boss.txt');
$team_key = file_get_contents('src/keys/team.txt');
$PageTitle = "Demande d'intervention technique";

//Get Location from URL & set default
$default_location = isset($_GET["loc"]) ? $_GET["loc"] : 'SC';
if($default_location == 'residence' || $default_location == 'res') $default_location = 'RES';
else if($default_location == 'ptf') $default_location = 'PTF';

//Default Variables
$ref = time();
$today = new DateTime();
$date = $today->format('Y-m-d');
$name = '';
$email = '';
$description = '';
$location = $default_location;
$res = "RES";
$residence = "Résidence (précisez ↴)";
$ptf = "PTF";
$poledeformation = "PTF (précisez ↴)";
$service = $location == "RES" ? "CS" : "";
$PlusFiveBusinessDays = addBusinessDays($today, 5);
$delivery = $PlusFiveBusinessDays->format('Y-m-d');
$status = 'new';
$class = '';
$action = '<div class="action" onClick="Send()">Envoyer</div>';
$edit = '';

//Load Intervention
if (!empty($_GET["ref"]) && is_file("interventions/" . $_GET["ref"] . ".xml")) {
    //Ouvrir une demande existante
    $ref = $_GET["ref"];
    $xml = "interventions/" . $ref . ".xml";

    $demande = simplexml_load_file($xml) or die('Erreure lors du chargement, le fichier "'.$ref.'.xml" n\'existe pas.');
    $creation = new DateTime();
    $creation->setTimestamp($ref);
    $date = isset($demande->applicant->date) ? $demande->applicant->date : $creation->format('Y-m-d');
    $service = isset($demande->applicant->service) ? $demande->applicant->service : '';
	
	//Deal with service reorganisation
	if($service == "COM") $service = "DG";
	else if($service == "DAT" || $service == "DAF" || $service == "RH") $service = "SG";
	else if($service == "PTF" || $service == "UAIP") $service = "SF";
	else if($service == "UM") $service = "SRD";
	
    $name = isset($demande->applicant->name) ? $demande->applicant->name : '';
    $email = isset($demande->applicant->email) ? $demande->applicant->email : '';
    $description = isset($demande->object->description) ? $demande->object->description : '';
	$location = isset($demande->object->location) ? $demande->object->location : $default_location;
	
	//Setup Precise Location
	if (str_starts_with($location, "RES-")) {
		$res = $location;
		$parts = explode("-", $location, 2);
		$residence = "Résidence (" . $parts[1] . ")";
	}else if (str_starts_with($location, "PTF-")) {
		$ptf = $location;
		$parts = explode("-", $location, 2);
		$PTF = [
			"GN" => "PTF Grand-Nord",
			"IDFOM-PSD" => "PTF Île-de-France",
			"IDFOM-AG" => "MU Antilles-Guyane",
			"IDFOM-RM" => "MU Réunion-Mayotte",
			"IDFOM-OP" => "MU Océan Pacifique",
			"GC" => "PTF Grand-Centre",
			"CE" => "PTF Centre-Est",
			"GO" => "PTF Grand-Ouest",
			"GE" => "PTF Grand-Est",
			"SO" => "PTF Sud-Ouest",
			"SE" => "PTF Sud-Est",
			"SUD" => "PTF Sud",
		];

		$code = $parts[1];
		$poledeformation = isset($PTF[$code]) ? "PTF (" . $PTF[$cle] . ")" : "PTF (inconnu)";
	}

	$PlusFiveBusinessDays = addBusinessDays($creation, 5);
    $delivery = isset($demande->object->delivery) ? $demande->object->delivery : $PlusFiveBusinessDays->format('Y-m-d');
    $status = isset($demande->object->status) ? $demande->object->status : 'pending';
    
	//Make sure 'new' status is avoided.
    if($status == 'new') $status = 'pending';
    $action = '<div class="action" onClick="Edit()">Modifier</div><div class="action" onClick="Cancel()">Annuler</div>';
    $class = $status;
    $edit = '';
    
    //Get Identification
    if (!empty($_GET["key"])) {
        //If KEY given compute TOKEN
        $key = $_GET["key"];
        $token = hash('sha256', $key . $ref);
    } elseif (!empty($_GET["token"])) {
        //Else get TOKEN directly
        $token = $_GET["token"];
    } else {
        //No Identification
        $token = '';
    }
    
    /*LIFE CYCLLE
		no expense required : pending > accepted (team) > finished (team) > closed (boss) ou canceled
		requiring some expenses : pending > quoted (team) > approved (boss) > finished (team) > closed (boss) ou canceled
	*/
    
    //Manage Life Cycle
    if($token == ''){ // Ouverture sans token
        if($status == 'canceled'){
            $action = '<span class="info">Cette demande a été annulée.</span>';
            $edit = 'disabled';            
        } elseif ($status == 'closed'){
            $action = '<span class="info">Cette demande a été clôturée.</span>';
            $edit = 'disabled';
        } elseif ($status == 'finished'){
            $action = '<span class="info">Cette demande a été réalisée.</span>';
            $edit = 'disabled';
        } elseif ($status == 'accepted' || $status == 'approved'){
            $action = '<span class="info">Cette demande est en cours de réalisation...</span>';
            $edit = 'disabled';
        } elseif ($status == 'quoted'){
            $action = '<span class="info">Cette demande nécessite un engagement financier supplémentaire...</span>';
            $edit = 'disabled';
        }
    } else { //Ouverture avec token
        if ($token == hash('sha256', $boss_key . $ref)) { //Boss is logged in
            if ($status == 'canceled') {
                $action = '<span class="info">Cette demande a été annulée.</span>';
                $edit = 'disabled';
            } elseif ($status == 'closed') {
                $action = '<span class="info">Cette demande a déjà été clôturée.<br />Aucune action n\'est requise de votre part.</span>';
                $edit = 'disabled';
            } elseif ($status == 'finished') {
                $action = '<div class="action close" onClick="Close()">Clôturer</div>';
                $edit = 'disabled';
                $status = 'closed';
            } elseif ($status == 'accepted') {
                $action = '<span class="info">Cette demande est en cours de réalisation.<br />Aucune action n\'est requise de votre part.</span>';
                $edit = 'disabled';
            } elseif ($status == 'approved') {
                $action = '<span class="info">La dépense liée à cette demande a déjà été approuvée.<br />Aucune action n\'est requise de votre part.</span>';
                $edit = 'disabled';
            } elseif ($status == 'quoted') {
                $action = '<div class="action approve" onClick="Approve()">Approuver</div><div class="action" onClick="Cancel()">Annuler</div>';
                $edit = 'disabled';
				$status = 'approved';
            }
        } elseif ($token == hash('sha256', $team_key . $ref)) { //Team is logged in
            if ($status == 'canceled') {
                $action = '<span class="info">Cette demande a été annulée.</span>';
                $edit = 'disabled';
            } elseif ($status == 'pending') {
                $action = '<div class="action accept" onClick="Accept()">Accepter</div><div class="action quote" onClick="Quote()">Deviser</div>';
                $edit = 'disabled';
                $status = 'accepted';
            } elseif ($status == 'accepted') {
                $action = '<div class="action finished" onClick="Finish()">Terminer</div>';
                $edit = 'disabled';
                $status = 'finished';
            } elseif ($status == 'quoted') {
                $action = '<span class="info">La dépense liée à cette demande est en attente de validation.<br />Aucune action n\'est requise de votre part.</span>';
                $edit = 'disabled';
            } elseif ($status == 'approved') {
                $action = '<div class="action finished" onClick="Finish()">Terminer</div>';
                $edit = 'disabled';
                $status = 'finished';
            } elseif ($status == 'finished') {
                $action = '<span class="info">Cette demande a déjà été réalisée.<br />Aucune action n\'est requise de votre part.</span>';
                $edit = 'disabled';
            }
        }
    }
}

$PageTitle = date('Ymd', $ref) . '_' . $PageTitle . ' n°' . $ref;
    
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="/src/css/fonts.css">
	<link rel="stylesheet" href="src/css/technique.css">
	<script src="/src/js/commontools.js"></script>
    <script src="src/js/technique.js"></script>
	<link rel="icon" href="favicon.ico" />
<title><?php echo $PageTitle; ?></title>
</head>
<body onLoad="Select('service','<?php echo $service; ?>'); Select('location','<?php echo $location; ?>')">
    <div class="cycle <?php echo $class; ?>" onClick="Login()"></div>
    <div class="reset" onClick="New()"></div>
    <h1 id="ref"><?php echo $ref; ?></h1>
    <h2>Demandeur</h2>
    <label for="service">Service&nbsp;:&nbsp;
    <select id="service" <?php echo $edit; ?>>
        <option value="" disabled>Sélectionner</option>
        <option value="DG">Direction générale</option>
        <option value="SG">Sécrétariat Général</option>
        <option value="SF">Formation</option>
        <option value="SRD">Recherche</option>
        <option value="CS">Conciergerie Solidaire</option>
    </select></label>
    <label for="agent">Agent&nbsp;:&nbsp;<input id="agent" type="text" placeholder="Prénom NOM" value="<?php echo $name; ?>" <?php echo $edit; ?> /></label>
    <label for="agent">Email&nbsp;:&nbsp;<input id="email" type="text" placeholder="prenom.nom@justice.fr" value="<?php echo $email; ?>" <?php echo $edit; ?> /></label>
    <label for="date">Fait&nbsp;le&nbsp;<input id="date" type="date" value="<?php echo $date; ?>" disabled /></label>
    <h2>Objet de la demande</h2>
    <label for="location">Lieu de l'intervention&nbsp;:&nbsp;
    <select id="location" onChange="PreciseLocation()" <?php echo $edit; ?>>
        <option value="" disabled>Sélectionner</option>
        <option value="SC">Site Central</option>
        <option value="<?php echo $res ?>"><?php echo $residence ?></option>
        <option value="CEH">CEH</option>
        <option value="<?php echo $ptf ?>"><?php echo $poledeformation ?></option>
        <option value="ELSE">Autre</option>
    </select></label>
    <label for="delivery">Date&nbsp;souhaitée&nbsp;:&nbsp;<input id="delivery" type="date" value="<?php echo $delivery; ?>" <?php echo $edit; ?> /></label>
    <span class="NoPrint">Notez que le délai d'usage est de 5 jours ouvrés.</span><br />
    <label for="description"><textarea id="description" placeholder="Description de la demande..." <?php echo $edit; ?>><?php echo $description; ?></textarea></label>
    <input class="hidden" type="text" id="status" value="<?php echo $status; ?>" />
    <span class="NoPrint">Tous les champs sont obligatoires.</span>
    <?php echo $action; ?>
</body>

</html>