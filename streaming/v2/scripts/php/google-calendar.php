<?php
// List of preset calendars
if (!empty($_GET["cal"])) {
    $cal = $_GET["cal"];
    if ($cal == 'condorcet-bas') {
        $cal = urlencode('575r6ica7qtduonrjfg7q2m1go@group.calendar.google.com');
    } elseif ($cal == 'condorcet-haut') {
        $cal = urlencode('rsoshe708q04sb0uc89la8lrvk@group.calendar.google.com');
    } elseif ($cal == 'costa') {
        $cal = urlencode('121e37bb28eda13e3f8717be0a0286167f1d1530b68cd5a086dea7715563dc2b@group.calendar.google.com');
    } elseif ($cal == 'michelet') {
        $cal = urlencode('97159241ece74cdd6f2a134262ccec10671774077d3f7f0ef011cd738cf1bbdd@group.calendar.google.com');
    } elseif ($cal == 'studio') {
        $cal = urlencode('0504bee628c25fe46ce6b6afbe50a69c5e7f1aa7c778762cfe3fb12aced76609@group.calendar.google.com');
    } elseif ($cal == 'mobile') {
        $cal = urlencode('e7c8f50fc1313e08591f957bf36e06a64e590f8d6b7e171b85843adbb342b094@group.calendar.google.com');
    } else {
        $cal = urlencode('575r6ica7qtduonrjfg7q2m1go@group.calendar.google.com'); //Condorcet Bas set default
    }
} else {
    $cal = urlencode('575r6ica7qtduonrjfg7q2m1go@group.calendar.google.com'); //Condorcet Bas set default
}

$info = $_GET["info"];
$KeyAPI = 'AIzaSyA6MfU4jTiL-nQQbC2_emtk1wgPJED1u0Y';
$Today = urlencode(date("Y-m-d")."T".date("H:i:s")."+01:00");
$URL = 'https://www.googleapis.com/calendar/v3/calendars/'.$cal.'/events?orderBy=startTime&singleEvents=true&timeMin='.$Today.'&key='.$KeyAPI;
$GoogleCalendar = json_decode(file_get_contents($URL));

if (empty($GoogleCalendar->items)) {
    //In cas no event scheduled
    $collection = 'ENPJJ';
    $title_ref = '0000';
} else {
    //Get Next Event Summary
    if (isset($GoogleCalendar->items[0]->summary)) {
        $summary = $GoogleCalendar->items[0]->summary;
    } else {
        $summary = "ENPJJ/0000";//Revert to default
    }
    $REF = explode("/", $summary);
    $collection = trim($REF[0]);
    $title_ref = trim($REF[1]);

    //Get Next Event Start Time
    $date = $GoogleCalendar->items[0]->start->dateTime;
    //Get Next Event  Location
    if (isset($GoogleCalendar->items[0]->location)) {
        $link = $GoogleCalendar->items[0]->location;
    } else {
        $link = 'https://www.youtube.com/channel/UCWAKCXaiQeZhA1__yEb8LDA/live'; 
    }
    $id = substr($link, strrpos($link, "/")+1);
    //Get Next Event Attachement
    if (isset($GoogleCalendar->items[0]->description)) {
        $doc = $GoogleCalendar->items[0]->description;
    }
}

//Return Values for Include
if ($info == 'date') {
    return $date; 
} elseif ($info == 'title_ref') {
    return $title_ref; 
} elseif ($info == 'collection') {
    return $collection; 
} elseif ($info == 'doc' && $doc != "") {
    header('Location: '.$doc);
    //http://multimedia.enpjj.fr/streaming/v2/src/php/google-calendar.php?info=doc
} elseif ($info == 'link' && $link != "") { 
    header('Location: '.$link);
    //http://multimedia.enpjj.fr/streaming/v2/src/php/google-calendar.php?info=link
} elseif ($info == 'studio' && $id != "") { 
    header('Location: https://studio.youtube.com/video/'.$id.'/livestreaming');
    //http://multimedia.enpjj.fr/streaming/v2/src/php/google-calendar.php?info=studio
} else {
    header('Location: '.$URL);
}
?>
