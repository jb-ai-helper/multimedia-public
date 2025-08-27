<?php
//Load XML
$fiche = simplexml_load_file($xml) or die('Erreure lors du chargement, le fichier "'.$ref.'.xml" n\'existe pas.');
//Populate default variables
$VideoTitle = $fiche->video->title;
$VideoVisibilityData = $fiche->video->visibility;
$VideoDateData = $fiche->video->date;
$VideoDate = FormatDate($VideoDateData, 'video');
$VideoDescription = $fiche->video->description;
$EventTitle = $fiche->event->title;
$EventSubTitle = $fiche->event->subtitle;
$EventStyleData = $fiche->event->style;
$EventStyle = GetStyleName($EventStyleData);
$EventLocation = $fiche->event->location;
$OnSiteAttendance = $fiche->event->onsite;
$RemoteAttendance = $fiche->event->remote;
$BroadcastMethode = $fiche->event->release;
$BroadcastMethode_suboptions = ShowHideSubOptions($BroadcastMethode);
$ShootingDateData = $fiche->event->date;
$StartTime = $fiche->event->start;
$ShootingDate = FormatDate($ShootingDateData, 'shooting');
$Speakers = LoadGlobalSpeakers($fiche);
$Sequences = LoadSequences($fiche);

function FormatDate(string $DATE, string $TYPE)
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

function GetStyleName($CSS)
{
    if($CSS == "undefined" || $CSS == "ENPJJ") { return 'ENPJJ';
    } else{
        $CSS = '../streaming/v2/styles/'.$CSS.'.css';
        $OpenedFile = fopen($CSS, "r");
        while(!feof($OpenedFile))
            {
            $Content = fgets($OpenedFile);
            $Line[] = $Content;

            //Format Name
            $StyleName = trim($Line[0]);
            $StyleName = str_replace("/* ", "", $StyleName);
            $StyleName = str_replace(" */", "", $StyleName);
        }
        fclose($OpenedFile);
        return $StyleName;
    }
}

function ShowHideSubOptions($OPTION)
{
    if($OPTION == "Asynchrone") { return ""; 
    }
    else{ return "hidden"; 
    }
}

function LoadGlobalSpeakers($FICHE)
{
    //Start writing Auto Generative Script
    $SCRIPT = '<script id="AGS_SP" type="text/javascript">';
    $SCRIPT.= 'var Speaker = new Object();';
    
    foreach($FICHE->speaker->children() as $speaker){
        $SCRIPT.= "Speaker['id'] = '".$speaker->id."';";
        $SCRIPT.= "Speaker['name'] = '".addslashes($speaker->name)."';";
        $SCRIPT.= "Speaker['function'] = '".addslashes($speaker->function)."';";
        $SCRIPT.= "Speaker['translation'] = '".addslashes($speaker->translation)."';";
        $SCRIPT.= "Speaker['attendance'] = '".$speaker->attendance."';";
        $SCRIPT.= "Speaker['link'] = '".$speaker->link."';";
        $SCRIPT.= 'AddGlobalSpeaker(Speaker);';
    }
    //Remove AGSS after runnin
    $SCRIPT.= 'DeleteScript("AGS_SP");';
    //End AGSS
    $SCRIPT.= '</script>';
    
    return $SCRIPT;
}

function LoadSequences($FICHE)
{
    //Start writing Auto Generativ Script
    $SCRIPT = '<script id="AGS_SQ" type="text/javascript">';
    $SCRIPT.= 'var Sequence = new Object();';
    
    foreach($FICHE->sequence->children() as $sequence){
        //Speech Sequences
        if($sequence->type == "speech") {
            $SCRIPT.= "Sequence['id'] = '".$sequence->id."';";
            $SCRIPT.= "Sequence['type'] = '".$sequence->type."';";
            $SCRIPT.= "Sequence['duration'] = '".$sequence->duration."';";
            $SCRIPT.= "Sequence['needs'] = '".$sequence->needs."';";
            $SCRIPT.= "Sequence['speaking'] = '".$sequence->speaking."';";
            //Extras
            $SCRIPT.= "Sequence['extra'] = new Object();";
                //REQUESTS
                $SCRIPT.= "Sequence['extra']['requests'] = new Object();";
                $SCRIPT.= "Sequence['extra']['requests']['checked'] = '".$sequence->extra->requests->checked."';";
                $SCRIPT.= "Sequence['extra']['requests']['specifications'] = '".addslashes($sequence->extra->requests->specifications)."';";
                //DDR
                $SCRIPT.= "Sequence['extra']['ddr'] = new Object();";
                $SCRIPT.= "Sequence['extra']['ddr']['checked'] = '".$sequence->extra->ddr->checked."';";
                $SCRIPT.= "Sequence['extra']['ddr']['sources'] = '".addslashes($sequence->extra->ddr->sources)."';";
                //SCROLL
                $SCRIPT.= "Sequence['extra']['scroll'] = new Object();";
                $SCRIPT.= "Sequence['extra']['scroll']['checked'] = '".$sequence->extra->scroll->checked."';";
                $SCRIPT.= "Sequence['extra']['scroll']['message'] = '".addslashes($sequence->extra->scroll->message)."';";
        }
        //Transition Sequences
        elseif($sequence->type == "transition") {
            $SCRIPT.= "Sequence['id'] = '".$sequence->id."';";
            $SCRIPT.= "Sequence['type'] = '".$sequence->type."';";
            $SCRIPT.= "Sequence['html'] = '".addslashes($sequence->html)."';";
        }
        //Pause Sequences
        elseif($sequence->type == "pause") {
            $SCRIPT.= "Sequence['id'] = '".$sequence->id."';";
            $SCRIPT.= "Sequence['type'] = '".$sequence->type."';";
            $SCRIPT.= "Sequence['duration'] = '".$sequence->duration."';";
        }
        $SCRIPT.= 'AddSequence(Sequence);';
    }
    //Remove AGSS after runnin
    //$SCRIPT.= 'DeleteScript("AGS_SQ");';
    //End AGSS
    $SCRIPT.= '</script>';
    
    return $SCRIPT;

}

?>
