// JavaScript Document

//Open Broadcast Channels
var sn = new BroadcastChannel('obs-side-notes-channel');

//Side Note
var LoadingNote = new Array();
var ClearNote = new Array();
var KillNote = new Array();
var LastMessage;
var lastModified;

//Check if SideMessages in empty & if so OFF
/*var sn_previous_HTML;
var EmptySideMessages = window.setInterval(function () {
	var sn_current_state = document.getElementById("SideMessages").classList;
	if(sn_current_state.contains("ON"))
		{
		var sn_current_HTML = document.getElementById("Messages").innerHTML;
		if(sn_current_HTML == "") { ShowNote('OFF'); }
		}
}, 1000);*/

//Listen to PREVIEW
sn.onmessage = function (ev){ var DATA=ev.data; ShowNote(DATA); }

//Listen to the distant note update (RAM File)
window.onload = function() {
	var StreamKey = document.getElementById("SideMessages").getAttribute("data-stream-key");
	var ram_file = '../ram/' + StreamKey + '.txt';
	if(FileExists(ram_file)){
		console.log ('Valid stream key found: awaiting distant content...');
		window.setInterval(LoadMessage, 4000, ram_file); //request speed
	}
	else console.log ('Invalid stream key found: unable to recieve distant data.');
}

function LoadMessage(PATH){
	
	var xmlhttp = new XMLHttpRequest();	
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var distant_content = xmlhttp.responseText;
			if(typeof window.last_message == 'undefined'){ window.last_message = distant_content; }
            else if(window.last_message != distant_content){
                window.last_message = distant_content;
				ShowNote(distant_content);
				console.log ('Distant content recieved: ' + distant_content);
			}
		}
	}
	xmlhttp.open("POST", PATH, true);
	//xmlhttp.setRequestHeader('User-Agent', 'test');
	xmlhttp.send();
}

function ShowNote(DATA){
    //Seperate DATA
    note_content = DATA.split("|");
    var ID = note_content[0];
    var STAMP = note_content[1];
    var NOTE = note_content[2];

    //IF PREVIEW
    if(ID == 'preview'){
        //Adjust ID function of STAMP
        ID = STAMP+'Preview';
        //Update PREVIEW if exists
        if(document.getElementById(ID)){
            if(NOTE === ""){
                if(!window.KillPreview) { CancelPreview(STAMP); }
                else{ console.log("Waiting for previous preview to end.") }
            }
            else{
                document.getElementById(ID).innerHTML = NOTE;
                console.log (STAMP + ' Preview updated: ' + NOTE);
            }
        }
        //Create PREVIEW if doesn't exist
        else if(NOTE != ""){
            //Determin CLASS function of STAMP
            if(STAMP == "Public"){ var CLASS = (window.NoteType == "Right") ? "Left" : "Right"; }
            else{ CLASS = STAMP; }
            //Preview Creation
            CreateTextBubble(ID, STAMP, CLASS, NOTE);
            LoadingNote[LoadingNote.length] = window.setTimeout(function () { document.getElementById(ID).classList.remove("Loading"); clearTimeout(LoadingNote[LoadingNote.length]);  }, 1000);
            console.log (STAMP +' Preview added: ' + NOTE);
        }
    }
    //IF Distant CLEAR ALL command (no ID)
    else if(ID == ''){ ClearMessages(); }
    //IF Regular Note
    else{
        //Determine LIFESPAM
        if(STAMP == 'Public') { LIFESPAN = 28800000; }//8h (toujours) 
        else if(STAMP == 'Interne') { LIFESPAN = 28800000; }//8h (toujours)
        //Allow for custom LIFESPAN
        else{
            var note_timer = STAMP.split(":");
            var HOURS = Number(note_timer[0]);
            var MINUTES = Number(note_timer[1]);

            if(note_timer.length == 2){ SECONDS = 0; }
            else{ var SECONDS = Number(note_timer[2]); }

            var LIFESPAN = ((((HOURS*60)+MINUTES)*60)+SECONDS)*1000;
        }
        //Delete Message if ID already exists
        if(document.getElementById(ID)){
            var MESSAGE = document.getElementById(ID);
                MESSAGE.classList.add('Clearing');
            window.setTimeout(function () { document.getElementById('Messages').removeChild(MESSAGE); }, 1000);
            console.log ('Message Deleted: ' + ID);
        }
        //Else Create New Message
        else{
            //Determin CLASS
            if(STAMP == "Public"){
                switch(window.NoteType){
                    case "Right": window.NoteType = "Left";break;
                    case "Left": window.NoteType = "Right";break;
                    case undefined: window.NoteType = "Right";
                }
                var CLASS = window.NoteType;
            }
            else{ var CLASS = STAMP; }
            //Kill existing PREVIEW(s)
            if(document.getElementById('PublicPreview')){ CancelPreview('Public'); }
            if(document.getElementById('InternePreview')){ CancelPreview('Interne'); }
            //Only Log INTERN messages if MODE = LIGHT
            var MODE = document.getElementById('mode').value;
            if(STAMP == 'Interne' && MODE != 'light'){ console.log ('Internal Message: ' + note_content[2]); }
            //Message Creation
            CreateTextBubble(ID, STAMP, CLASS, NOTE);
            if(MODE != 'light' && STAMP == "Public") { window.setTimeout(function () { document.getElementById('notification').play();  }, 1000); }
            LoadingNote[LoadingNote.length] = window.setTimeout(function () { document.getElementById(ID).classList.remove("Loading"); clearTimeout(LoadingNote[LoadingNote.length]);  }, 500);
            ClearNote[ClearNote.length] = window.setTimeout(function () { document.getElementById(ID).classList.add("reverse", "Clearing"); clearTimeout(ClearNote[ClearNote.length]);  }, LIFESPAN);
            KillNote[KillNote.length] = window.setTimeout(function () { document.getElementById(ID).remove(); clearTimeout(KillNote[KillNote.length]);  }, LIFESPAN+1000);
            console.log ('Note Added: ' + note_content[2]);
        }
    }
}

function CreateTextBubble(ID, STAMP, CLASS, NOTE){
	var TextBuble = document.createElement('div');
	TextBuble.setAttribute('class', "TextBuble "+CLASS+" Loading");
	TextBuble.setAttribute('id',ID);
	TextBuble.setAttribute('data-stamp',STAMP);
	TextBuble.setAttribute('onclick',"SelectMessage('"+ID+"')");
	TextBuble.innerHTML = NOTE;
	document.getElementById("Messages").appendChild(TextBuble);
}

function SelectMessage(ID){
    var STAMP = document.getElementById(ID).dataset.stamp;
    var NOTE = document.getElementById(ID).innerHTML;
    var DATA = ID + '|' + STAMP + '|' + NOTE;
    sn.postMessage(DATA);
}

function CancelPreview(STAMP){
    var ID = STAMP+'Preview'
	document.getElementById(ID).classList.add("Clearing");
	window.KillPreview = window.setTimeout(function () {
		document.getElementById(ID).remove();
		clearTimeout(window.KillPreview);
		delete window.KillPreview;
	}, 1000);
}

function ClearMessages(){
	//Clear all Timeouts
	for (var c=0; c<ClearNote.length; c++) { clearTimeout(ClearNote[c]); }
	for (var k=0; k<KillNote.length; k++) { clearTimeout(KillNote[k]); }

	//Fade then kill all TextBubbles
	var TextBubles = document.getElementsByClassName("TextBuble");
	for (var i = 0; i < TextBubles.length; i++) { TextBubles[i].classList.add('Clearing'); }
	window.setTimeout(function () { document.getElementById("Messages").innerHTML = '';  }, 1000);
	
	//Reset Bubbles Left/Right
	delete window.NoteType;
    delete window.last_message;
}