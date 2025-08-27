<?php

//Set global parameters
mb_internal_encoding("UTF-8");
setlocale(LC_ALL, 'en_GB');

//Set global variables
$day_options = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi");
$time_options = array("7h50","8h50","10h05","11h05","12h20","13h20","14h20","15h40","16h40");
//$time_options = array("M1","M2","M3","M4","MIDI","S1","S2","S3","S4");

function SendEmails($subject,$message)
	{
	//Create EmailList
	$EmailList = array(

	//CPE Emails
	"mehdi.rokia@savoirsnumeriques5962.fr",
	//"malika.el_kostiti@savoirsnumeriques5962.fr",

	//Coordos Emails
	"natalia.tartare@savoirsnumeriques5962.fr",
	"heloise.d-haene@savoirsnumeriques5962.fr",
	"najate.boutharouite@savoirsnumeriques5962.fr",			
	"sebastien.leterme@savoirsnumeriques5962.fr",

	//WebMaster
	//"jean-baptiste.wattiaux@savoirsnumeriques5962.fr",
	);

	$subject = mb_encode_mimeheader($subject,"UTF-8");
	
	//Set Header
	$headers = 'MIME-Version: 1.0'."\r\n";
	$headers.= 'Content-type: text/plain; charset=utf-8'."\r\n";
	$headers.= 'X-Mailer: PHP/'.phpversion()."\r\n";
	
	//Preparing error system
	$error_nb = 0; $error_msg = "Une erreure s'est produite lors de l'envoie à : ";

	foreach($EmailList as $email)
		{ if(!mail($email,$subject,$message,$headers)) ++$error_nb; $error_msg.= $email."\n"; }
	
	if($error_nb>0) return($error_msg);
	else return("");
	}


function GenerateRef($name)
	{
	$name = strtolower(iconv('UTF-8','ASCII//TRANSLIT',$name));
	$name = preg_replace('/[[:space:]]+/', '-', $name);
	return $name;
	}

function SortLastname($a, $b)
	{ return strnatcmp($a['lastname'], $b['lastname']); }

function SortFirstname($a, $b)
	{ return strnatcmp($a['firstname'], $b['firstname']); }

//Génerate page depending on action
if(isset($_GET['action']) && $_GET['action'] != "")
	{
	if($_GET['action'] == "save" && isset($_POST['ref']) && !is_null($_POST['ref']))
		{
		//Prepare save file
		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		//Get REF and build file path
		$ref_raw = $_POST['ref'];
		$ref = GenerateRef($ref_raw);
		$GroupFile = "../groups/".$ref.".xml";
		$MasterPath = "../".$ref; //For HTACCESS redirect compatibility
				
		//Get sent metadata
		$name_data = $_POST["name"];
		$teacher_data = $_POST["teacher"];
		$day_data = $_POST["day"];
		$time_data = $_POST["time"];

		//If new REF already exists number it and change file path
		if(file_exists($GroupFile) && $ref_raw != $ref && !file_exists($MasterPath) && !is_dir($MasterPath))
			{
			//Make a list of all the groups
			$GroupFolder = opendir ("../groups/");
			while ($file = readdir ($GroupFolder))
				{
				$EntreeExt = pathinfo(basename($file),PATHINFO_EXTENSION);
				if ($EntreeExt == "xml") { $GroupeList[]=$file; }
				}
			closedir ($GroupFolder);    
			unset($file);
			
			//Count number of file with the same REF
			$SameRefNb = 0;

			foreach($GroupeList as $PossibleMatch)
				{
				//$TestMatch = strcmp($PossibleMatch, $ref);
				$TestMatch = strncmp($PossibleMatch, $ref, strlen($ref));
				if($TestMatch == 0) { ++$SameRefNb; }
				}
			
			if($SameRefNb > 0) { $ref = $ref.'_'.$SameRefNb; }
			$GroupFile = "../groups/".$ref.".xml";
			}

		if(file_exists($GroupFile))
			{
			//Load old XML
			$xml = simplexml_load_file($GroupFile) or die('Erreure lors du chargement,\nle fichier "'.$ref.'.xml" n\'existe pas.');
			$dom->loadXML($xml->asXML());
			
			//Get root
			$group = $dom->getElementsByTagName('group')[0];

			//Clear old members
			$members = $group->getElementsByTagName('members')[0];
			if($members) $members->parentNode->removeChild($members);
		
			//Get and clear old metadata
			$metadata = $group->getElementsByTagName('metadata')[0];
			if($metadata) $metadata->parentNode->removeChild($metadata);
			}
		else
			{
			//Create new root
			$group = $dom->createElement('group');
			$dom->appendChild($group);
			}
		
		//Create new metadata
		$metadata = $dom->createElement('metadata');
		
		//Set main variables
		if($name_data == "") $name = $ref_raw;
		else $name = $name_data;
		$teacher = $teacher_data;
		$day = $day_data;
		$time = $time_data;
						
		//Add metadata to XML
		$name_node = $dom->createElement('name');
		$name_node->nodeValue = $name;
		$teacher_node = $dom->createElement('teacher');
		$teacher_node->nodeValue = $teacher;
		$day_node = $dom->createElement('day');
		$day_node->nodeValue = $day;
		$time_node = $dom->createElement('time');
		$time_node->nodeValue = $time;
		$metadata->appendChild($name_node);
		$metadata->appendChild($teacher_node);
		$metadata->appendChild($day_node);
		$metadata->appendChild($time_node);

		//Create new roaster
		$members = $dom->createElement('members');
		
		//Get student list
		$list = $_POST["list"];

		//Start making changes
		echo '<script>';
		
		//If students list empty delete group
		if(empty($list))
			{
			if (unlink($GroupFile))
				{
				echo 'alert("Le groupe \"'.$name.'\" a bien été supprimé.");';
				
				$subject = "Le groupe \"".$name."\" a été supprimé.";
				$message = "Le groupe \"".$name."\" a été supprimé car il ne contenait plus aucun membres.\n";
				$message.= "Les appels seront désormais accessibles dans la rubrique \"Archives\".";
				
				$result = SendEmails($subject,$message);
				if ($result != "") { echo 'alert("'.$error_msg.'");'; }
				else echo 'alert("Les CPE et les coordonnateurs ont été informés.");';
				}
			else echo 'alert("Une erreur s\'est produite lors de la suppréssion,\nveuillez réessayer.");';
			}
		else
			{
			//Add students to XLM
			for($i=0;$i<count($list);$i++)
				{
				$NameComponent = explode(";",$list[$i]);
				if(!$NameComponent[1]){ $NameComponent[1] = ''; }
				if(!$NameComponent[2]){ $NameComponent[2] = ''; }

				$student = $dom->createElement('student');
				$firstname = $dom->createElement('firstname');
				$firstname->nodeValue = $NameComponent[0];
				$lastname = $dom->createElement('lastname');
				$lastname->nodeValue = $NameComponent[1];
				$classname = $dom->createElement('classname');
				$classname->nodeValue = $NameComponent[2];

				$members->appendChild($student);
				$student->appendChild($firstname);
				$student->appendChild($lastname);
				$student->appendChild($classname);
				}

			$group->appendChild($metadata);
			$group->appendChild($members);

			if($dom->save($GroupFile))
				{
				echo 'if(confirm("Vos changements ont été sauvegardés."))';
				echo '{ window.parent.location = "../'.$ref.'"; }';
				echo 'else { window.parent.document.getElementById("SaveButton").classList.add("hidden");';
				echo 'window.parent.document.getElementById("SendButton").classList.remove("hidden"); }';
				}
			else echo 'alert("601 : Erreur lors de l\'enregistrement,\veuillez réessayer.");';
			}

		//Get deleted list
		$deleted = stripslashes($_POST["deleted"]);

		//If deleted students send emails
		if($deleted!="")
			{
			$DeletedList = explode(";",$deleted);
			
			//Set Subject
			$subject = "[EXIT] ".$name;

			//Set Message
			$message = "";
			foreach($DeletedList as $value) { $message.= $value."\n"; }

			//Preparing error system
			$error_nb = 0; $error_msg = "Une erreure s'est produite lors de l'envoie à : ";
			
			//Send emails
			$result = SendEmails($subject,$message);
			if ($result != "") { echo 'alert("'.$error_msg.'");'; }
			else
				{
				echo 'alert("La liste des élèves supprimés a été envoyée aux CPE et aux coordonnateurs !");';
				echo 'window.parent.location.reload();';
				}
			}
		echo '</script>';
		}
	elseif($_GET['action'] == "load")
		{
		if(isset($_GET['ref']) && file_exists("groups/".$_GET['ref'].".xml"))
			{
			$ref = $_GET["ref"];
			$xml = simplexml_load_file("groups/".$ref.".xml");
			$metadata = $xml->metadata[0];

			//Set page Title
			$name = $metadata->name;
			$nametitle = '[ABS] '.$name;
			
			//Get additional metadata
			$teacher = $metadata->teacher;
			$day = $metadata->day;
			$fullday = $day_options[intval($day)];
			$time = $metadata->time;
			$fulltime = $time_options[intval($time)];

			for($d=0;$d<count($day_options);$d++)
				{
				if($day != "" && $day == $d) { $selected = "selected"; } //Select the chosen day if set
				elseif ($day == "" && $d == 0) { $selected = "selected"; } //If not set select default
				else  { $selected = ""; }
				$day_select.= "<option ".$selected." value='".$d."'>".$day_options[$d]."</option>";
				}
			
			for($t=0;$t<count($time_options);$t++)
				{
				if($time != "" && $time == $t) { $selected = "selected"; } //Select the chosen day if set
				elseif ($time == "" && $t == 0) { $selected = "selected"; } //If not set select default
				else  { $selected = ""; }
				$time_select.= "<option ".$selected." value='".$t."'>".$time_options[$t]."</option>";
				}
			
			//Force ConfigBox if data is missing
			if($teacher == "" || $day == "" || $time == "") $ConfigBox = "";
			else $ConfigBox = "hidden";
			
			//Set tablelist
			$tablelist = "";
			
			$i = 0; $j = 1;
			$nb = count($xml->members[0]->children());
			foreach ($xml->members[0]->student as $student)
				{
				if($student->firstname != "") { $firstname = stripslashes($student->firstname); }
				else { $firstname = ""; }
				if($student->lastname != "") { $lastname = stripslashes($student->lastname); }
				else { $lastname = ""; }
				if($student->classname != "") { $classname = stripslashes($student->classname); }
				else { $classname = ""; }

				$table[$i] = array("firstname" => $firstname, "lastname" => $lastname, "classname" => $classname);
				$i++;
				}
			
			usort($table, 'SortLastname');

			$nbt = count($table);
			foreach ($table as $table)
				{
				if($j<10) { $j = '0'.$j; }
				$inputvalue = $table['firstname'];
				$inputvalue.= ";".$table['lastname'];
				$inputvalue.= ";".$table['classname'];
				
				$labelvalue = ucfirst($table['firstname']);
				$labelvalue.= "&nbsp;".strtoupper($table['lastname']);
				$labelvalue.= "&nbsp;(".strtoupper($table['classname']).")";

				$tablelist.= '<tr><td oncontextmenu="return ContextMenu(this,event)">';
				$tablelist.= '<input checked onChange="CountPresences()" type="checkbox" name="list" value="'.$inputvalue.'" id="student'.$j.'" />';
				$tablelist.= '<label id="label'.$j.'" for="student'.$j.'">'.$labelvalue.'</label></td></tr>';
				$j++;
				}
			if($nbt<10) { $nbt = '0'.$nbt; }
			$save = 'hidden';
			$send = '';
			}
		else echo '<script>window.location = "../";</script>';
		}
	elseif($_GET['action'] == "send")
		{
		$ref = $_POST['ref'];
		$xml_group = simplexml_load_file("../groups/".$ref.".xml") or die('Erreure lors de l\'envoie, le fichier "'.$ref.'.xml" n\'existe pas.');
		
		//Get group metadata
		$metadata_group = $xml_group->metadata[0];
		$name_group = $metadata_group->name;
		$teacher_group = $metadata_group->teacher;
		$day_group = $metadata_group->day;
		$time_group = $metadata_group->time;
		
		//Get School Year
		$time = time();
		$year = date('Y', $time);
		if(date('n', $time) < 8) $SchoolYear = ($year - 1).'-'.$year;
		else $SchoolYear = ($year).'-'.($year + 1);
		
		$AttendancePath = '../'.$SchoolYear;
		$AttendanceFile = $AttendancePath.'/'.$ref.'.xml';
		
		if(is_dir($AttendancePath) || mkdir($AttendancePath))
			{
			$dom = new DOMDocument('1.0');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			
			//If file exists load its content
			if(is_file($AttendanceFile))
				{
				$xml_old = simplexml_load_file($AttendanceFile) or die('Erreure lors de la récupération des information du fichier "'.$ref.'.xml"');
				$dom->loadXML($xml_old->asXML());
				$attendance = $dom->getElementsByTagName('attendance')[0];
				$metadata_old = $dom->getElementsByTagName('metadata')[0];
				
				//Refresh metadata
				$metadata_old->getElementsByTagName('name')[0]->nodeValue = $name_group;
				$metadata_old->getElementsByTagName('teacher')[0]->nodeValue = $teacher_group;
				$metadata_old->getElementsByTagName('day')[0]->nodeValue = $day_group;
				$metadata_old->getElementsByTagName('time')[0]->nodeValue = $time_group;
				}
			else
				{
				$attendance = $dom->createElement('attendance');
				$metadata_new = $dom->createElement('metadata');
				
				//Copy metadata
				$name_new = $dom->createElement('name');
				$name_new->nodeValue = $name_group;
				$teacher_new = $dom->createElement('teacher');
				$teacher_new->nodeValue = $teacher_group;
				$day_new = $dom->createElement('day');
				$day_new->nodeValue = $day_group;
				$time_new = $dom->createElement('time');
				$time_new->nodeValue = $time_group;
				
				//Generate root and metadata
				$dom->appendChild($attendance);
				$attendance->appendChild($metadata_new);
				$metadata_new->appendChild($name_new);
				$metadata_new->appendChild($teacher_new);
				$metadata_new->appendChild($day_new);
				$metadata_new->appendChild($time_new);
				}

			//Create a new attendance sheet with time parameter
			$AttendanceCall = $dom->createElement('call');
			$AttendanceTime = $dom->createAttribute('time');
			$AttendanceTime->value = $time;
			$AttendanceCall->appendChild($AttendanceTime);
		
			//Get array with absentees
			$absentee = stripslashes($_POST['absentee']);
			$AbsenteeList = explode(";",$absentee);
			
			//Build the attendance sheet
			foreach ($xml_group->members[0]->student as $student)
				{
				if($student->firstname != "") { $firstname_data = stripslashes($student->firstname); }
				else { $firstname_data = ""; }
				if($student->lastname != "") { $lastname_data = stripslashes($student->lastname); }
				else { $lastname_data = ""; }
				if($student->classname != "") { $classname_data = stripslashes($student->classname); }
				else { $classname_data = ""; }
							
				$StudentNode = $dom->createElement('student');
				$firstname = $dom->createElement('firstname');
				$firstname->nodeValue = $firstname_data;
				$lastname = $dom->createElement('lastname');
				$lastname->nodeValue = $lastname_data;
				$classname = $dom->createElement('classname');
				$classname->nodeValue = $classname_data;
				
				//NameStyle for matchmaking
				$StudentEntree =  ucwords($firstname_data).' '.strtoupper($lastname_data).' ('.strtoupper($classname_data).')';
				
				if(in_array($StudentEntree,$AbsenteeList)) { $StudentStatus = 0; }
				else { $StudentStatus = 1; }
				
				$status = $dom->createElement('status');				
				$status->nodeValue = $StudentStatus;

				$AttendanceCall->appendChild($StudentNode);
				$StudentNode->appendChild($firstname);
				$StudentNode->appendChild($lastname);
				$StudentNode->appendChild($classname);
				$StudentNode->appendChild($status);
				}

			//Add the attendance call to the list
			$attendance->appendChild($AttendanceCall);
			
			// Save the file
			if(!$dom->save($AttendanceFile)) echo '<script>alert("601 : Erreur lors de l\'enregistrement,n\veuillez réessayer.");</script>';
			else echo '<script>alert("Votre appel a bien été enregistré.");</script>';
			}

		//If Absentee(s) send emails
		if($absentee!="")
			{
			//Set Subject
			$subject = "[ABS] ".$name_group;

			//Set Message
			foreach($AbsenteeList as $value) { $message.= $value."\n"; }
			
			//Send emails
			$result = SendEmails($subject,$message);
			if ($result != "") { echo '<script>alert("'.$result.'")</script>'; }
			else
				{
				echo '<script>alert("La liste des absents a bien été envoyée aux CPE et aux coordonnateurs !");';
				echo 'window.parent.location.reload();</script>';
				}
			}
		else
			{
			echo "<script>alert('Aucun élève absent à signaler.');";
			echo 'window.parent.location.reload();</script>';
			}
		}
	else echo '<script>window.location = "../";</script>';
	}
else
	{
	$tablelist = '<tr><td oncontextmenu="return ContextMenu(this,event)">';
	$tablelist.= '<input checked onChange="CountPresences()" type="checkbox" name="list" value="prenom;nom;classe" id="student01" />';
	$tablelist.= '<label for="student01">Prenom&nbsp;NOM&nbsp;(CLASSE)</label></td></tr>';
	$nbt = '00'; $nametitle = ''; $ref = ''; $teacher = ''; $name = ''; $save = ''; $nametitle = 'Attendance';
	$send = 'hidden'; $ConfigBox = "hidden";
	for($d=0;$d<count($day_options);$d++)
		{
		if ($d == 0) { $selected = "selected"; } else  { $selected = ""; }
		$day_select.= "<option ".$selected." value='".$d."'>".$day_options[$d]."</option>";
		}

	for($t=0;$t<count($time_options);$t++)
		{
		if ($t == 0) { $selected = "selected"; } else  { $selected = ""; }
		$time_select.= "<option ".$selected." value='".$t."'>".$time_options[$t]."</option>";
		}
	}
?>
