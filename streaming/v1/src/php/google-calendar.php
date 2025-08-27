<?php
// List of preset calendars
if(!empty($_GET["cal"])) {
	$cal = $_GET["cal"];
	if($cal == 'amphi') $cal = urlencode('575r6ica7qtduonrjfg7q2m1go@group.calendar.google.com');
	elseif($cal == 'mobile') $cal = urlencode('rsoshe708q04sb0uc89la8lrvk@group.calendar.google.com');
}
else { $cal = urlencode('575r6ica7qtduonrjfg7q2m1go@group.calendar.google.com'); }// set default as Amphi

$info = $_GET["info"];
$KeyAPI = 'AIzaSyArXTLfXmaf6R8t5c9Oyw2I2owXcqNYnec';
$Today = urlencode(date("Y-m-d")."T".date("H:i:s")."+01:00");
$GoogleCalendar_url = 'https://www.googleapis.com/calendar/v3/calendars/'.$cal.'/events?orderBy=startTime&singleEvents=true&timeMin='.$Today.'&key='.$KeyAPI;
//https://www.googleapis.com/calendar/v3/calendars/bmg5rqmcpvr77j96ca55vll47o%40group.calendar.google.com/events?orderBy=startTime&singleEvents=true&timeMin='.$Today.'&key=AIzaSyBm3mcu_8D0sxDjV5nsXUyCLGQCofkbKoM
$GoogleCalendar = json_decode(file_get_contents($GoogleCalendar_url));

if(empty($GoogleCalendar->items))
{
	//In cas no event scheduled
	$collection = 'ENPJJ';
	$title = '0000';
}
else
{
	//Get Next Event Summary
	$Next_Summary = $GoogleCalendar->items[0]->summary;
	$REF = explode("/", $Next_Summary);
	$collection = $REF[0];
	$title = $REF[1];

	//Get Next Event Start Time
	$Next_Date = $GoogleCalendar->items[0]->start->dateTime;
	//Get Next Event  Location
	$Next_Location = $GoogleCalendar->items[0]->location;
	//Get Next Event Description
	$Next_Description = $GoogleCalendar->items[0]->description;
	$YouYube_Link = strip_tags($Next_Description);
	$YouYube_ID = substr($YouYube_Link, strrpos($YouYube_Link, "/")+1);
	//Get Next Event Attachement
	$Next_Attachments = $GoogleCalendar->items[0]->attachments[0]->fileUrl;
}

//Return Values for Include
if($info == 'date'){ return $Next_Date; }
elseif($info == 'title'){ return $title; }
elseif($info == 'collection'){ return $collection; }

//http://obs.jbwattiaux.fr/src/php/google-calendar.php?info=chat
elseif($info == 'chat')
{
	$Chat_URL = $Next_Location;
	header('Location: '.$Chat_URL);
}
//http://obs.jbwattiaux.fr/src/php/google-calendar.php?info=doc
elseif($info == 'doc')
{
	$Stream_Doc = $Next_Attachments;
	header('Location: '.$Stream_Doc);
}
//http://obs.jbwattiaux.fr/src/php/google-calendar.php?info=link
elseif($info == 'link')
{
	header('Location: '.$YouYube_Link);
}
//http://obs.jbwattiaux.fr/src/php/google-calendar.php?info=studio
elseif($info == 'studio')
{
	header('Location: https://studio.youtube.com/video/'.$YouYube_ID.'/livestreaming');
}
else
{
	header('Location: '.$GoogleCalendar_url);
}
?>