//Raccourcis Clavier
var keys = {};
onkeydown = onkeyup = function(e){
	e = e || event;
	e.which = e.which || e.keyCode;
	keys[e.which] = e.type === 'keydown';
		
	if(e.key == "c" && e.altKey == true) { window.location.assign(window.location.href+"&action=convert"); }//Alt + C
	if(e.key == "m" && e.altKey == true) { window.location.assign("manager.php"); }//Alt + l
}

//Get REF
var URL = new URL(window.location.href);
var REF = URL.searchParams.get("ref");

var STR = REF;
if(REF == null){ STR = "temp"; }
console.log("Stream Key:", STR);

var psi = new BroadcastChannel(STR+'-stream-info-channel');
var plt = new BroadcastChannel(STR+'-lower-third-channel');
var psb = new BroadcastChannel(STR+'-scrolling-banner-channel');
var pct = new BroadcastChannel(STR+'-chapter-transition-channel');

// Objet pour stocker les éléments modifiés au moment de l'impression
const elementsModifiedForPrint = {};

// Événement `beforeprint` pour cacher les éléments avec data-original = innerHTML
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.editable').forEach(function(div) {
        // Vérifie si l'attribut "data-original" est égal à l'innerHTML
        if (div.getAttribute('data-original') === div.innerHTML) {
            // Stocke l'état initial de l'élément (s'il est déjà caché ou visible)
            elementsModifiedForPrint[div] = div.style.display || '';
            // Cache l'élément pour l'impression
            div.classList.add('hide-for-print');
        }
    });
});

// Événement `afterprint` pour restaurer l'affichage initial
window.addEventListener('afterprint', function() {
    document.querySelectorAll('.editable').forEach(function(div) {
        // Restaure uniquement les éléments modifiés par `beforeprint`
        if (elementsModifiedForPrint.hasOwnProperty(div)) {
            div.classList.remove('hide-for-print');
        }
    });
    // Vide l'objet pour libérer la mémoire
    Object.keys(elementsModifiedForPrint).forEach(key => delete elementsModifiedForPrint[key]);
});

//Send DATA To Broadcast Channels
function SendTo(channel,DATA){
    channel.postMessage(JSON.stringify(DATA));
    console.log('Command sent: '+JSON.stringify(DATA));
}

//DEfault Streaming Folder
var StreamingFolder = "../streaming/v2/";

function ToggleActions(what){
	var STATE = what.dataset.state;
	var ID = what.id;
	
	if(STATE == "off"){
		document.getElementById(ID).dataset.state = "on";
		document.getElementById(ID+"_actions").dataset.state = "on";
	}
	else{
		document.getElementById(ID).dataset.state = "off";
		document.getElementById(ID+"_actions").dataset.state = "off";
	}
}

function ResetFiche(){
	var MSG = "Cette fiche n'existe pas !";
	if(confirm(MSG)){ location.href='../fiche/'; }
}

function NewFiche(){
	var MSG = "Attention, toutes les informations non sauvegadées seront perdues.";
	if(confirm(MSG)){ location.href='../fiche/'; }
}

function OpenFiche(){
	var MSG = "Veuillez entrer le code de la fiche à ouvrir :";
    let REF = prompt(MSG);
	if(REF!=null){ location.href='../fiche/?ref='+REF; }
}

function DeleteFiche(REF){
	var MSG = "Êtes-vous certain de vouloir supprimer cette demande ?";
    let CONF = confirm(MSG);
	if(CONF==true){
        //Create hidden iframe for delete.php
        var iframe = document.createElement('iframe');
            iframe.setAttribute('class', 'hidden');
            iframe.setAttribute('id','action');
            iframe.setAttribute('name','action');
            document.body.appendChild(iframe);
        //Create hidden form to send XML
        var delete_form = document.createElement('form');
            delete_form.setAttribute('id','saveform');
            delete_form.setAttribute('class','hidden');
            delete_form.setAttribute('action','src/php/delete.php');
            delete_form.setAttribute('method','post');
            delete_form.setAttribute('target','action');
            document.body.appendChild(delete_form);
        //Create REF input
        var ref_input = document.createElement('input');
            ref_input.setAttribute('value',REF);
            ref_input.setAttribute('name','ref');
            delete_form.appendChild(ref_input);
        //Send request to Multimédia
        delete_form.submit();
    }
}

function EditInput(what, MAX, TYPE){
	
	var ID = what.id;
	var OriginalValue = what.innerHTML.replace(/<br\s*[\/]?>/gi, "\n");
	
	what.removeAttribute('onclick');
	if(!what.dataset.original) what.setAttribute('data-original', OriginalValue);
	what.setAttribute('data-replicated', OriginalValue);
	
	if(TYPE == 'number') CHECK = "ValidateNumber(event);";
	else{ CHECK = ""; }
    
	var TXTAREA = document.createElement('textarea');
		TXTAREA.setAttribute('onblur', "SetInput(this, "+MAX+",'"+TYPE+"')");
		TXTAREA.setAttribute('onkeydown', 'ListenKey(this,event)');
		TXTAREA.setAttribute('ondblclick', 'toggleCase()');
		TXTAREA.setAttribute('maxlength', MAX);
		TXTAREA.setAttribute('resize', 'none');
		TXTAREA.setAttribute('onInput', 'Replicate(this);'+CHECK);
		TXTAREA.innerHTML = OriginalValue;
		
		what.innerHTML = "";
		what.appendChild(TXTAREA);
		TXTAREA.focus();
		TXTAREA.select();
}

function toggleCase() {
	event.stopPropagation();

	const textarea = event.target;
	const { selectionStart: start, selectionEnd: end, value } = textarea;
	const selected = value.slice(start, end);

	// Compte le nombre de lettres upper / lower
	const lowers = (selected.match(/[a-z]/g) || []).length;
	const uppers = (selected.match(/[A-Z]/g) || []).length;

	const converted =
		lowers >= uppers ? selected.toUpperCase() : selected.toLowerCase();

	textarea.value = value.slice(0, start) + converted + value.slice(end);
	textarea.setSelectionRange(start, start + converted.length);
}

function SetInput(what, MAX, TYPE){
	
	var DIV = what.parentElement;
	var OriginalValue = what.parentNode.dataset.original;
	var NewValue = what.value;
	
	//Clean up NewValue
	if(NewValue.length != 0){
        NewValue = NewValue.replace( /(<([^>]+)>)/ig, '');//Strip all tags
        NewValue = NewValue.replace(/\r\n?|\n/g, "<br />");//Add BR for carriage return
        NewValue = NewValue.replace(/\t/g, ' ');//Convert tabulations to spaces
        NewValue = NewValue.trim();//Remove whitespace from both sides
	}

    //Check if empty or only blank spaces
    if(NewValue.length == 0 || NewValue.replace(/\s+/g,'').length == 0){
        NewValue = OriginalValue;
        
        //Uncheck Scroll if field is reset (back to Original Value)
        if(what.parentNode.id.includes('_scrollmessage')){
        var Scroll_ID = what.parentNode.id.replace('_scrollmessage', "");
        document.getElementById(Scroll_ID+'_scroll').checked = false;
        }
        //Uncheck DDR if field is reset (back to Original Value)
        if(what.parentNode.id.includes('_ddrsources')){
        var DDR_ID = what.parentNode.id.replace('_ddrsources', "");
        document.getElementById(DDR_ID+'_ddr').checked = false;
        }
    }

    DIV.innerHTML = NewValue;
    DIV.setAttribute('onclick', "EditInput(this,"+MAX+",'"+TYPE+"')");
    DIV.removeAttribute('data-replicated');
    UpdatePreview(DIV);
}

function Replicate(what){
	var ValueToReplicate = what.value;
	if(ValueToReplicate.length == 0) ValueToReplicate = " ";
	 what.parentNode.dataset.replicated = ValueToReplicate; 
}

function ValidateNumber(event){
	const elem = event.target;
	const value = elem.value;
	const numVal = value.replace(/\D/,"");
	elem.value = numVal;
}

function EditDate(what,FORMAT){
	
	var OriginalValue = what.dataset.date;

	var DATE = document.createElement('input');
		DATE.setAttribute('type',"date")
		DATE.setAttribute('onkeydown', 'ListenKey(this,event)');
		DATE.setAttribute('onblur', 'SetDate(this,'+FORMAT+')');
		
		what.removeAttribute('onclick');
		what.innerHTML = "";
		what.appendChild(DATE);
		DATE.value = OriginalValue;
		DATE.focus();
		DATE.select();	
}

function SetDate(what,FORMAT){
		
	var DIV = what.parentElement;
	var OriginalValue = DIV.dataset.date;

	if(what.value == "") NewValue = OriginalValue;
	else NewValue = what.value;

	DIV.removeChild(what);
	DIV.setAttribute('onclick', "EditDate(this,"+FORMAT+")");

	var FormatedDate = FormateDate(NewValue,FORMAT);
		DIV.innerHTML = FormatedDate;
		DIV.dataset.date = NewValue;
	
	var BroadcastMethode = document.getElementById("BroadcastMethode").innerHTML;
	var ShootingDate = document.getElementById('ShootingDate');
	var VideoDate = document.getElementById('VideoDate');
	UpdatePreview(VideoDate);
	
	if(BroadcastMethode == "Asynchrone" && CompareDates(VideoDate.dataset.date,ShootingDate.dataset.date) == -1){
		var ERROR = "Le tournage ne peut pas avoir lieu après la date de publication de la vidéos. La date du tournage à donc été reculée.";
		alert(ERROR); ResetShootingDate();
	}
	else if(BroadcastMethode == "Synchrone" && CompareDates(VideoDate.dataset.date,ShootingDate.dataset.date) != 0){ ResetShootingDate(); }
}

function ResetShootingDate(){
	var ShootingDate = document.getElementById('ShootingDate');
	var VideoDate = document.getElementById('VideoDate');
    ShootingDate.dataset.date = VideoDate.dataset.date;
    ShootingDate.innerHTML = FormateDate(VideoDate.dataset.date,0);
}

function ListenKey(what,event)
{
	var intKeyCode = event.keyCode;

    //Allow Shift + Enter BUT not for "smalltext"
	if(intKeyCode == 13 && !event.shiftKey || intKeyCode == 13 && event.shiftKey && what.getAttribute('onblur').includes('smalltext'))
	{ what.blur(); }
    
    
}

function AutoScroll(what)
{
	var NewWidth = Number(what.scrollWidth);
	var NewHeight = Number(what.scrollHeight);
	what.style.width = NewWidth+"px";
	what.style.height = NewHeight+"px";
}

function ShowOptions(what){
	var options = document.getElementById(what.id + "_options");
		options.classList.remove("hidden");
}

function HideOptions(what){
	what.classList.add("hidden");
}

function DeleteOptions(what){ what.parentElement.removeChild(what); }

function SelectOption(what){
	var option_html = what.innerHTML;
	var target = what.parentElement.parentElement.id.replace("_options", "");
	document.getElementById(target).innerHTML = option_html;
	
	//Get Additional Dataset if exists
	if(what.dataset){ Object.assign(document.getElementById(target).dataset, what.dataset); }
	
	//Open SubOptions if extists and trigger matches
	var suboptions = Array.prototype.slice.call(document.getElementsByClassName(target+"_suboptions"));
		suboptions.forEach(function(suboption){
			var TRIGGER = option_html.search(suboption.dataset.trigger);
			if(TRIGGER == -1 && !suboption.classList.contains("hidden")){ suboption.classList.add("hidden"); }
			else if(TRIGGER >= 0 && suboption.classList.contains("hidden")) {suboption.classList.remove("hidden"); }
	});
}

function UpdatePreview(what){
	
	//Target determines TYPE of preview
	var target = what.parentElement;
	if(target == "") target = what.parentElement.parentElement;
		
	//Set TYPE
	var TYPE = what.id;
	if(target.classList.contains('options')){ TYPE = target.parentElement.id.replace("_options", ""); }
	else if(target.classList.contains('chapter') || what.id.match("ScrollingMessage") != null){ TYPE = "ScrollingMessage"; }
	else if(target.classList.contains('transition')){ TYPE = "Transition"; }
	else if(target.classList.contains('duration')){ TYPE = "Duration"; }
	else if(target.classList.contains('speaker')){ TYPE = "Speaker"; }
    
    //Preview depending on TYPE
    UpdateElement(TYPE);
}

function UpdateElement(TYPE){
    //Preview Stream Info
	if(TYPE == "EventTitle" || TYPE == "EventSubTitle" || TYPE == "EventStyle" || TYPE == "VideoDate"){
		var title_to_send = document.getElementById('EventTitle').innerHTML;
		var subtitle_to_send = document.getElementById('EventSubTitle').innerHTML;
		var date_to_send = document.getElementById('VideoDate').innerHTML;
		var style_to_send = document.getElementById('EventStyle').dataset.css;
		
        const DATA = {type:"si", title:title_to_send, subtitle:subtitle_to_send, date:date_to_send, style:style_to_send};
        SendTo(psi,DATA);
	}
	//Preview Stream Location Photo
	else if(TYPE == "EventLocation"){
		document.getElementById("EventLocation_preview").src = document.getElementById('EventLocation').dataset.preview;
	}
	//Preview Transition
	else if(TYPE == "Transition"){
        var HTML_Texareas = getElementByIdEndingWith("_Chapter");
            HTML_Texareas.forEach(function(el){
                var ChapterTitle = el.value;
                var PreviewRef = el.parentElement.id;
                const DATA = {id:PreviewRef, type:"ct", state:"ON", title:ChapterTitle};
                SendTo(pct,DATA);
            });
	}
	//Update Time id Duration
	else if(TYPE == "Duration") UpdateTime();
	//Update all speaker occurences
	else if(TYPE == "Speaker"){
        var Speakers = document.getElementsByClassName("speaker");//Get Object Collection
            for(speaker of Speakers){
                var ID = speaker.id;
                var UpdatedName = speaker.getElementsByClassName('name')[0].innerHTML;
                var OCCURENCES = document.getElementsByClassName(ID)
                for(occurence of OCCURENCES){
                        var OldName = occurence.innerHTML.replace( /(<([^>]+)>)/ig, '');//Clean up old name
                        occurence.innerHTML = occurence.innerHTML.replace(OldName, UpdatedName);
                    }
            }
	}
}

function UpdateAll(){
    UpdateElement("EventTitle");
    UpdateElement("EventLocation");
    UpdateElement("Transition");
    //UpdateElement("ScrollingMessage");
    UpdateElement("Duration");
    UpdateElement("Speaker");
}

function ToogleAttendance(what){
	what.dataset.attendance = what.dataset.attendance=="onsite" ? "remote" : "onsite";
	var OCCURENCES = Array.prototype.slice.call(document.getElementsByClassName(what.id));
		OCCURENCES.forEach(function(OCCURENCE){ OCCURENCE.dataset.attendance = what.dataset.attendance; });
}

function AddSpeaker(what){

	//Add Global Speaker
	if(what.parentElement.id == "DSK_Section"){ AddGlobalSpeaker(); }
	//Add speaker to chapter
	else
	{
		var PUBLIC_OPTION = document.createElement("div");
			PUBLIC_OPTION.setAttribute('class', "option");
			PUBLIC_OPTION.setAttribute('onclick',"SelectSpeaker(this)");
			PUBLIC_OPTION.setAttribute('data-id',"public");
			PUBLIC_OPTION.setAttribute('data-attendance',"hybrid");
			PUBLIC_OPTION.innerHTML = "Questions du public";
		
		var CHAPTERSPEAKERS_OPTIONS = document.createElement("div");
			CHAPTERSPEAKERS_OPTIONS.setAttribute('class', "options");
			CHAPTERSPEAKERS_OPTIONS.appendChild(PUBLIC_OPTION);
		
		var AvailableSpeakers = Array.prototype.slice.call(document.getElementsByClassName("speaker"));
			AvailableSpeakers.forEach(function(SPEAKER){
				var OPTION = document.createElement("div");
					OPTION.setAttribute('class', "option");
					OPTION.setAttribute('onclick',"SelectSpeaker(this)");
					OPTION.setAttribute('data-id',SPEAKER.id);
					OPTION.setAttribute('data-attendance',SPEAKER.dataset.attendance);
					OPTION.innerHTML = SPEAKER.getElementsByClassName("name")[0].innerHTML;
				
				CHAPTERSPEAKERS_OPTIONS.appendChild(OPTION);
			});
		
		
		var CHAPTERSPEAKERS = document.createElement("div");
			CHAPTERSPEAKERS.setAttribute('id',"ChapterSpeakers_options");
			CHAPTERSPEAKERS.setAttribute('onclick',"DeleteOptions(this)");
			CHAPTERSPEAKERS.appendChild(CHAPTERSPEAKERS_OPTIONS);
				
		what.parentElement.appendChild(CHAPTERSPEAKERS);
	}
}

function AddGlobalSpeaker(SPEAKER){
    //Set default values
    var SUBREF = GenerateSubRef();
    var NAME_DEFAULT = "Prénom NOM";
    var NAME = NAME_DEFAULT;
    var FUNCTION_DEFAULT = "Fonction Complète";
    var FUNCTION = FUNCTION_DEFAULT;
    var TRANSLATION_DEFAULT = "Fonction Traduite";
    var TRANSLATION = TRANSLATION_DEFAULT;
    var ATTENDANCE = "onsite";
    var LINK = "Copier/coller le lien ici...";
    
    //Change default if SPEAKER given & accoount for empty
    if(Object.prototype.toString.call(SPEAKER) === '[object Object]'){
        SUBREF = SPEAKER['id'];
        NAME = SPEAKER['name'].length == 0 ? NAME : SPEAKER['name'];
        FUNCTION = SPEAKER['function'].length == 0 ? FUNCTION : SPEAKER['function'];
        TRANSLATION = SPEAKER['translation'].length == 0 ? TRANSLATION : SPEAKER['translation'];
        ATTENDANCE = SPEAKER['attendance'];
        LINK = SPEAKER['link'];
    }
    
    var Speaker = document.createElement('div');
        Speaker.setAttribute('id', SUBREF);
        Speaker.setAttribute('class', "speaker");
        Speaker.setAttribute('data-attendance', ATTENDANCE);
        Speaker.setAttribute('ondblclick', "ToogleAttendance(this)");

    var DeleteButton = document.createElement('div');
        DeleteButton.setAttribute('class', "delete");
        DeleteButton.setAttribute('onclick', "Remove(this)");

    var PreviewButton = document.createElement('div');
        PreviewButton.setAttribute('class', "preview");
        PreviewButton.setAttribute('onclick', "PreviewSpeaker(this)");

    var Name = document.createElement('div');
        Name.setAttribute('class', "name editable");
        Name.setAttribute('onclick', "EditInput(this,100,'smalltext')");
        Name.setAttribute('data-original', NAME_DEFAULT);
        Name.innerHTML = NAME;

    var Function = document.createElement('div');
        Function.setAttribute('class', "function editable");
        Function.setAttribute('onclick', "EditInput(this,140,'longtext')");
        Function.setAttribute('data-original', FUNCTION_DEFAULT);
        Function.innerHTML = FUNCTION;

    var Translation = document.createElement('div');
        Translation.setAttribute('class', "translation editable");
        Translation.setAttribute('onclick', "EditInput(this,140,'longtext')");
        Translation.setAttribute('data-original', TRANSLATION_DEFAULT);
        Translation.innerHTML = TRANSLATION;

    var span = document.createElement('span');
        span.innerHTML = "Lien de connexion&nbsp;:&nbsp;";

    var RemoteLink = document.createElement('div');
        RemoteLink.setAttribute('class', "link editable");
        RemoteLink.setAttribute('onclick', "EditInput(this,524288,'smalltext')");
        RemoteLink.innerHTML = LINK;

    var Connection = document.createElement('div');
        Connection.setAttribute('class', "connection");
        Connection.appendChild(span);
        Connection.innerHTML+= "<br />";
        Connection.appendChild(RemoteLink);

    //Build Speaker
    var SpeakersList = document.getElementById("Speakers");
        SpeakersList.appendChild(Speaker);

        //Build inner elements and HTML
        Speaker.appendChild(DeleteButton);
        Speaker.appendChild(PreviewButton);
        Speaker.appendChild(Name);
        Speaker.innerHTML+= "<br />";
        Speaker.appendChild(Function);
        Speaker.innerHTML+= "<br />";
        Speaker.appendChild(Translation);
        Speaker.appendChild(Connection);
}


function Remove(what){
	var ELEMENT = what.parentElement;//Element to be deleted
	var PARENT = ELEMENT.parentElement;//Parent of the element to be deleted
    //NB: what = Delete Button

    //If element = Speaker then remove all its occurences
    if(ELEMENT.classList.contains("speaker")){
        var OCCURENCES = Array.prototype.slice.call(document.getElementsByClassName(ELEMENT.id));
		OCCURENCES.forEach(function(OCCURENCE){ OCCURENCE.parentElement.removeChild(OCCURENCE); });
    }
	//Remove element
    PARENT.removeChild(ELEMENT);
	UpdateTime();
}

function DeleteScript(ID){
    var script = document.getElementById(ID);
        script.parentNode.removeChild(script);
}

function PreviewSpeaker(what){
    //Get orginal values
    var original_name = what.parentElement.getElementsByClassName("name")[0].dataset.original;
    var original_function = what.parentElement.getElementsByClassName("function")[0].dataset.original;
    var original_translation = what.parentElement.getElementsByClassName("translation")[0].dataset.original;
    
    //Get data to send
    name_to_send = what.parentElement.getElementsByClassName("name")[0].innerHTML;
    function_to_send = what.parentElement.getElementsByClassName("function")[0].innerHTML;
    translation_to_send = what.parentElement.getElementsByClassName("translation")[0].innerHTML;

    //Clear function or translation if default.

    if(name_to_send == original_name){ name_to_send = ""; }
    if(function_to_send == original_function){ function_to_send = ""; }
    if(translation_to_send == original_translation){ translation_to_send = ""; }

    var DATA = {type:"lt", state:"OFF", name:original_name, function:original_function, translation:original_translation};
    SendTo(plt,DATA); document.body.classList.add('wait');
    setTimeout(function () { DATA = {type:"lt", state:"ON", name:name_to_send, function:function_to_send, translation:translation_to_send}; SendTo(plt,DATA); }, 2000);
    setTimeout(function () { DATA = {type:"lt", state:"OFF", name:name_to_send, function:function_to_send, translation:translation_to_send}; SendTo(plt,DATA); }, 8000);
    setTimeout(function () { DATA = {type:"lt", state:"ON", name:original_name, function:original_function, translation:original_translation}; SendTo(plt,DATA); document.body.classList.remove('wait') }, 10000);
}

function UpdateTime(){
	
	var StartTime = document.getElementById("StartTime").value;
	var Time = StartTime.split(":")
	var Minutes = (Number(Time[0])*60)+Number(Time[1]);
	
	var Durations = Array.prototype.slice.call(document.querySelectorAll('[id$="_Duration"]'));
		Durations.forEach(function(ELEMENT){
            ELEMENT.parentElement.parentElement.setAttribute('data-start', Minutes2Time(Minutes));
            Minutes = Minutes+Number(ELEMENT.innerHTML);
        });
			
	var EndTime = Minutes2Time(Minutes);
	document.getElementById("EndTime").value = EndTime;
	console.log ("End Time: "+EndTime);
}

function Minutes2Time(MIN){
    var Hours = parseInt(MIN/60);
    var Minutes = MIN-(Hours*60);
	if(String(Hours).length<2) Hours = "0"+String(Hours);
	if(String(Minutes).length<2) Minutes = "0"+String(Minutes);
    return Hours+":"+Minutes;
}

function AddSequence(SEQUENCE){
    if(Object.prototype.toString.call(SEQUENCE) === '[object Object]'){
        var TYPE = SEQUENCE['type'];
        //Generate sequence depending on TYPE
        if(TYPE == "speech"){ AddSpeech(SEQUENCE); }
        else if(TYPE == "transition"){ AddTransition(SEQUENCE); }
        if(TYPE == "pause"){ AddPause(SEQUENCE); }
    }
}

function AddTransition(SEQUENCE){
    if(Object.prototype.toString.call(SEQUENCE) === '[object Object]'){
        var ID = SEQUENCE['id'];
        var HTML = SEQUENCE['html'];
    }
    else{
        var ID = GenerateSubRef();
        var HTML = "<h1>Titre du Chapitre</h1>";
    }
	
	//Create Common Elements
	
	var DELETE = document.createElement("div");
		DELETE.setAttribute('class', "delete");
		DELETE.setAttribute('onclick', "Remove(this)");
	
	var MOVEUP = document.createElement("div");
		MOVEUP.setAttribute('class', "moveup");
		MOVEUP.setAttribute('onclick', "MoveUp(this)");
	
	var MOVEDOWN = document.createElement("div");
		MOVEDOWN.setAttribute('class', "movedown");
		MOVEDOWN.setAttribute('onclick', "MoveDown(this)");
	
	//Create Preview
	
	var GRAPHICS = document.createElement("iframe");
		GRAPHICS.setAttribute('class',"screen");
		GRAPHICS.setAttribute('src', StreamingFolder+"apps/dsk.php?mode=transition&str="+STR+"&eid="+ID);
		GRAPHICS.setAttribute('onload', "UpdatePreview(document.getElementById('"+ID+"_Chapter'))");
		
	var TRANSITION_PREVIEW = document.createElement("div");
		TRANSITION_PREVIEW.setAttribute('class', "small");
		TRANSITION_PREVIEW.setAttribute('id', ID+"_Preview");
		TRANSITION_PREVIEW.appendChild(GRAPHICS);
	
	//Create Editable HTML + SPAN
	
	var TRANSITION_CHAPTER = document.createElement("textarea");
		TRANSITION_CHAPTER.setAttribute('id', ID+"_Chapter");
		TRANSITION_CHAPTER.setAttribute('oninput', "UpdatePreview(this)");
		TRANSITION_CHAPTER.setAttribute('ondblclick', 'toggleCase()');
		TRANSITION_CHAPTER.innerHTML = HTML;
	
	var TRANSITION_SPAN = document.createElement("span");
		TRANSITION_SPAN.innerHTML = 'Les balises "&#60;h1&#62;", "&#60;h2&#62;" ou "&#60;h3&#62;" vous permettent de modifier la taille des titres. La balise "&#60;br&#62;" vous permez de forcer le retour à la ligne.';

	//Build the whole sequence
	
	var TRANSITION = document.createElement("div");
		TRANSITION.setAttribute('class', "sequence transition")
		TRANSITION.setAttribute('id', ID);
		TRANSITION.appendChild(DELETE);
		TRANSITION.appendChild(MOVEUP);
		TRANSITION.appendChild(MOVEDOWN);
		TRANSITION.appendChild(TRANSITION_PREVIEW);
		TRANSITION.appendChild(TRANSITION_CHAPTER);
		TRANSITION.appendChild(TRANSITION_SPAN);

	//Create Transition Wrapper
	
	var SEQUENCES = document.getElementById("Sequences");
		SEQUENCES.appendChild(TRANSITION);
}

function AddSpeech(SEQUENCE){
    //Set default values
    var ID = GenerateSubRef();
    var DURATION = '0';
    var DDR_CHECKED = false;
    var DDR_SOURCES_DEFAULT = "Indiquer ici le détail des médias à diffuser.";
    var DDR_SOURCES = DDR_SOURCES_DEFAULT;
    var SCROLL_CHECKED = false;
    var SCROLL_MESSAGE_DEFAULT = "Indiquer ici le message à faire défiler (numéro de téléphone, consigne de sécurité, appel à l’action, etc.).";
    var SCROLL_MESSAGE = SCROLL_MESSAGE_DEFAULT;
    var REQUESTS_SPECIFICATIONS_DEFAULT = "Préciser ici vos besoins techniques spécifiques.";
    var REQUESTS_SPECIFICATIONS = REQUESTS_SPECIFICATIONS_DEFAULT;
    var PUPITRE_CHECKED = false;
    var BUREAU_CHECKED = false;
    var FAUTEUIL_CHECKED = false;
    var HF_CHECKED = false;
    var PC_CHECKED = false;
    var SPEAKING = '';
    
    //Change default if SEQUENCE given & account for empty
    if(Object.prototype.toString.call(SEQUENCE) === '[object Object]'){
        var ID = SEQUENCE['id'];
        var DURATION = SEQUENCE['duration'];
        var DDR_CHECKED = SEQUENCE['extra']['ddr']['checked'];
        var DDR_SOURCES = SEQUENCE['extra']['ddr']['sources'].length == 0 ? DDR_SOURCES_DEFAULT : SEQUENCE['extra']['ddr']['sources'];
        var SCROLL_CHECKED = SEQUENCE['extra']['scroll']['checked'];
        var SCROLL_MESSAGE = SEQUENCE['extra']['scroll']['message'].length == 0 ? SCROLL_MESSAGE_DEFAULT : SEQUENCE['extra']['scroll']['message'];
        var REQUESTS_CHECKED = SEQUENCE['extra']['requests']['checked'];
        var REQUESTS_SPECIFICATIONS = SEQUENCE['extra']['requests']['specifications'].length == 0 ? SCROLL_MESSAGE_DEFAULT : SEQUENCE['extra']['requests']['specifications'];
        //Extract NEEDS
        var NEEDS = SEQUENCE['needs'];
            //Pupitre
            if(NEEDS.match("pupitre")){ var PUPITRE_CHECKED = 'true'; }
            else{ var PUPITRE_CHECKED = 'false'; }
            //Bureau
            if(NEEDS.match("bureau")){ var BUREAU_CHECKED = 'true'; }
            else{ var BUREAU_CHECKED = 'false'; }
            //Fauteuil
            if(NEEDS.match("fauteuil")){ var FAUTEUIL_CHECKED = 'true'; }
            else{ var FAUTEUIL_CHECKED = 'false'; }
            //HF
            if(NEEDS.match("hf")){ var HF_CHECKED = 'true'; }
            else{ var HF_CHECKED = 'false'; }
            //PC
            if(NEEDS.match("pc")){ var PC_CHECKED = 'true'; }
            else{ var PC_CHECKED = 'false'; }
        //Extract SPEAKING
        var SPEAKING = SEQUENCE['speaking'];
    }
	
	//Create Common Elements
	
	var DELETE = document.createElement("div");
		DELETE.setAttribute('class', "delete");
		DELETE.setAttribute('onclick', "Remove(this)");
	
	var MOVEUP = document.createElement("div");
		MOVEUP.setAttribute('class', "moveup");
		MOVEUP.setAttribute('onclick', "MoveUp(this)");
	
	var MOVEDOWN = document.createElement("div");
		MOVEDOWN.setAttribute('class', "movedown");
		MOVEDOWN.setAttribute('onclick', "MoveDown(this)");
	
	var DURATION_SPAN = document.createElement("span");
		DURATION_SPAN.innerHTML = 'Durée&nbsp;:&nbsp;';
	
	var DURATION_EDITABLE = document.createElement("div");
		DURATION_EDITABLE.setAttribute('id', ID+"_Duration");
		DURATION_EDITABLE.setAttribute('class', "editable");
		DURATION_EDITABLE.setAttribute('onclick', "EditInput(this,3,'number')");
		DURATION_EDITABLE.innerHTML = DURATION;
	
	var DURATION = document.createElement("div");
		DURATION.setAttribute('class', "duration");
		DURATION.appendChild(DURATION_SPAN);
		DURATION.appendChild(DURATION_EDITABLE);
		DURATION.innerHTML+= '&nbsp;min';
	
	//Create Chapter Speakers Section
	
	var SPEAKERS = document.createElement("h3");
		SPEAKERS.innerHTML = 'Intervenants&nbsp;:';

	var SPEAKERS_WRAPER = document.createElement("div");
		SPEAKERS_WRAPER.setAttribute('class', "speakers");

    //Add Speaking Speakers
    if(SPEAKING != ""){
        var speaker = SPEAKING.split(',');

        for(id of speaker){
            var SPEAKER = new Object();
            if(id == 'public'){
                SPEAKER['id'] = id;
                SPEAKER['name'] = "Questions du public";
                SPEAKER['attendance'] = "hybrid";
            }
            else{
                var GlobalSpeaker = document.getElementById(id);
                SPEAKER['id'] = id;
                SPEAKER['name'] = GlobalSpeaker.getElementsByClassName('name')[0].innerHTML;
                SPEAKER['attendance'] = GlobalSpeaker.dataset.attendance;
            }
            var SPEAKINGPEAKER = AddSpeakingSpeaker(SPEAKER);
            SPEAKERS_WRAPER.appendChild(SPEAKINGPEAKER);
        }
    }
	
	var SPEAKERS_BUTTON = document.createElement("div");
		SPEAKERS_BUTTON.setAttribute('class', "button");
		SPEAKERS_BUTTON.setAttribute('onclick', "AddSpeaker(this)")
		SPEAKERS_BUTTON.innerHTML = '+ Intervenant';
		SPEAKERS_WRAPER.appendChild(SPEAKERS_BUTTON);
	
	//Create Chapter Technical Needs Suboption of EventLocation
	
	var TECHNICALNEEDS = document.createElement("h3");
		TECHNICALNEEDS.innerHTML = "Besoins Techniques (Amphis)&nbsp;:";
	
	var PUPITRE = document.createElement("input");
		PUPITRE.setAttribute('id', ID+"_pupitre");
		PUPITRE.setAttribute('class', "option");
		PUPITRE.setAttribute('type', "checkbox");
	
	var PUPITRE_LABEL = document.createElement("label");
		PUPITRE_LABEL.setAttribute('for', ID+"_pupitre");
		PUPITRE_LABEL.innerHTML = "Intervention(s) depuis un pupitre.";
	
	var BUREAU = document.createElement("input");
		BUREAU.setAttribute('id', ID+"_bureau");
		BUREAU.setAttribute('class', "option");
		BUREAU.setAttribute('type', "checkbox");
	
	var BUREAU_LABEL = document.createElement("label");
		BUREAU_LABEL.setAttribute('for', ID+"_bureau");
		BUREAU_LABEL.innerHTML = "Intervention(s) depuis le(s) bureau(x) (micros filaires).";	

	var FAUTEUIL = document.createElement("input");
		FAUTEUIL.setAttribute('id', ID+"_fauteuil");
		FAUTEUIL.setAttribute('class', "option");
		FAUTEUIL.setAttribute('type', "checkbox");
	
	var FAUTEUIL_LABEL = document.createElement("label");
		FAUTEUIL_LABEL.setAttribute('for', ID+"_fauteuil")
		FAUTEUIL_LABEL.innerHTML = "Intervention(s) depuis des fauteuils (micros sans fils).";

	var HF = document.createElement("input");
		HF.setAttribute('id', ID+"_hf");
		HF.setAttribute('class', "option");
		HF.setAttribute('type', "checkbox");
	
	var HF_LABEL = document.createElement("label");
		HF_LABEL.setAttribute('for', ID+"_hf")
		HF_LABEL.innerHTML = "Intervention(s) depuis le public (micros sans fils).";
	
	var PC = document.createElement("input");
		PC.setAttribute('id', ID+"_pc");
		PC.setAttribute('class', "option");
		PC.setAttribute('type', "checkbox");
	
	var PC_LABEL = document.createElement("label");
		PC_LABEL.setAttribute('for', ID+"_pc");
		PC_LABEL.innerHTML = "Diffusion de médias par les intervenants (PowerPoints, photos, vidéos, etc.).";
	
	var LOCATION_SUBOPTIONS = document.createElement("div");
		LOCATION_SUBOPTIONS.setAttribute('data-trigger', "Amphi");
		LOCATION_SUBOPTIONS.setAttribute('style', "margin-bottom:1rem;");
		LOCATION_SUBOPTIONS.setAttribute('class', "EventLocation_suboptions");
		LOCATION_SUBOPTIONS.appendChild(TECHNICALNEEDS);
		LOCATION_SUBOPTIONS.innerHTML+= "<br />";
		LOCATION_SUBOPTIONS.appendChild(PUPITRE);
		LOCATION_SUBOPTIONS.appendChild(PUPITRE_LABEL);
        LOCATION_SUBOPTIONS.innerHTML+= "<br />";
		LOCATION_SUBOPTIONS.appendChild(BUREAU);
		LOCATION_SUBOPTIONS.appendChild(BUREAU_LABEL);
		LOCATION_SUBOPTIONS.innerHTML+= "<br />";
		LOCATION_SUBOPTIONS.appendChild(FAUTEUIL);
		LOCATION_SUBOPTIONS.appendChild(FAUTEUIL_LABEL);
		LOCATION_SUBOPTIONS.innerHTML+= "<br />";
		LOCATION_SUBOPTIONS.appendChild(HF);
		LOCATION_SUBOPTIONS.appendChild(HF_LABEL);
		LOCATION_SUBOPTIONS.innerHTML+= "<br />";
		LOCATION_SUBOPTIONS.appendChild(PC);
		LOCATION_SUBOPTIONS.appendChild(PC_LABEL);
	
	if(document.getElementById('EventLocation').innerHTML.search("Amphi") == -1) LOCATION_SUBOPTIONS.classList.add("hidden");

	//Create Chapter Special Needs Section
	
	var SECIALNEEDS = document.createElement("h3");
		SECIALNEEDS.innerHTML = 'Dispositifs Spéciaux&nbsp;:';
	
	var DDR = document.createElement("input");
		DDR.setAttribute('id', ID+"_ddr")
		DDR.setAttribute('class', "option")
		DDR.setAttribute('type', "checkbox")
	
	var DDR_LABEL = document.createElement("label");
		DDR_LABEL.setAttribute('for', ID+"_ddr")
		DDR_LABEL.innerHTML = "Diffusion de média depuis la régie&nbsp;:&nbsp;";
	
	var DDR_DETAILS = document.createElement("div");
		DDR_DETAILS.setAttribute('id',ID+"_ddrsources");
		DDR_DETAILS.setAttribute('onclick',"EditInput(this,500,'text')");
		DDR_DETAILS.setAttribute('class',"editable");
		DDR_DETAILS.setAttribute('data-original',DDR_SOURCES_DEFAULT);
		DDR_DETAILS.innerHTML = DDR_SOURCES;
	
	var SCROLL = document.createElement("input");
		SCROLL.setAttribute('id', ID+"_scroll")
		SCROLL.setAttribute('class', "option")
		SCROLL.setAttribute('type', "checkbox")
	
	var SCROLL_LABEL = document.createElement("label");
		SCROLL_LABEL.setAttribute('for', ID+"_scroll")
		SCROLL_LABEL.innerHTML = "Message à faire défiler en haut de l'écran&nbsp;:&nbsp;";
	
	var SCROLL_DETAILS = document.createElement("div");
		SCROLL_DETAILS.setAttribute('id',ID+"_scrollmessage");
		SCROLL_DETAILS.setAttribute('onclick',"EditInput(this,250,'smalltext')");
		SCROLL_DETAILS.setAttribute('class',"editable");
		SCROLL_DETAILS.setAttribute('data-original',SCROLL_MESSAGE_DEFAULT);
		SCROLL_DETAILS.innerHTML = SCROLL_MESSAGE;
	
	var REQUESTS = document.createElement("input");
		REQUESTS.setAttribute('id', ID+"_requests")
		REQUESTS.setAttribute('class', "option")
		REQUESTS.setAttribute('type', "checkbox")
	
	var REQUESTS_LABEL = document.createElement("label");
		REQUESTS_LABEL.setAttribute('for', ID+"_ddr")
		REQUESTS_LABEL.innerHTML = "Prestation(s) technique(s) supplémentaires(s)&nbsp;:&nbsp;";
	
	var REQUESTS_DETAILS = document.createElement("div");
		REQUESTS_DETAILS.setAttribute('id',ID+"_requestsspecs");
		REQUESTS_DETAILS.setAttribute('onclick',"EditInput(this,500,'longtext')");
		REQUESTS_DETAILS.setAttribute('class',"editable");
		REQUESTS_DETAILS.setAttribute('data-original',REQUESTS_SPECIFICATIONS_DEFAULT);
		REQUESTS_DETAILS.innerHTML = REQUESTS_SPECIFICATIONS;

	//Build the whole sequence
	
	var SPEECH = document.createElement("div");
		SPEECH.setAttribute('class', "sequence speech")
		SPEECH.setAttribute('data-start', "--:--");
		SPEECH.setAttribute('id', ID);
		SPEECH.appendChild(DELETE);
		SPEECH.appendChild(MOVEUP);
		SPEECH.appendChild(MOVEDOWN);
		SPEECH.appendChild(DURATION);
		SPEECH.appendChild(SPEAKERS);
		SPEECH.innerHTML+= "<br />";
		SPEECH.appendChild(SPEAKERS_WRAPER);
		SPEECH.appendChild(LOCATION_SUBOPTIONS);
		SPEECH.appendChild(SECIALNEEDS);
		SPEECH.innerHTML+= "<br />";
		SPEECH.appendChild(DDR);
		SPEECH.appendChild(DDR_LABEL);
		SPEECH.appendChild(DDR_DETAILS);
		SPEECH.innerHTML+= "<br />";
		SPEECH.appendChild(SCROLL);
		SPEECH.appendChild(SCROLL_LABEL);
		SPEECH.appendChild(SCROLL_DETAILS);
		SPEECH.innerHTML+= "<br />";
		SPEECH.appendChild(REQUESTS);
		SPEECH.appendChild(REQUESTS_LABEL);
		SPEECH.appendChild(REQUESTS_DETAILS);

	//Create Chapter Wrapper
	
	var SEQUENCES = document.getElementById("Sequences");
		SEQUENCES.appendChild(SPEECH);

    //Checks what needs to be
    
    if(PUPITRE_CHECKED == 'true') document.getElementById(ID+'_pupitre').checked = true;
    if(BUREAU_CHECKED == 'true') document.getElementById(ID+'_bureau').checked = true;
    if(FAUTEUIL_CHECKED == 'true') document.getElementById(ID+'_fauteuil').checked = true;
    if(HF_CHECKED == 'true') document.getElementById(ID+'_hf').checked = true;
    if(PC_CHECKED == 'true') document.getElementById(ID+'_pc').checked = true;
    if(DDR_CHECKED == 'true') document.getElementById(ID+'_ddr').checked = true;
    if(SCROLL_CHECKED == 'true') document.getElementById(ID+'_scroll').checked = true;
    if(REQUESTS_CHECKED == 'true') document.getElementById(ID+'_requests').checked = true;
}

function AddPause(SEQUENCE){
    if(Object.prototype.toString.call(SEQUENCE) === '[object Object]'){
        var ID = SEQUENCE['id'];
        var DURATION = SEQUENCE['duration'];
    }
    else{
        var ID = GenerateSubRef();
        var DURATION = "0";
    }
		
	//Create Common Elements
	
	var DELETE = document.createElement("div");
		DELETE.setAttribute('class', "delete");
		DELETE.setAttribute('onclick', "Remove(this)");
	
	var MOVEUP = document.createElement("div");
		MOVEUP.setAttribute('class', "moveup");
		MOVEUP.setAttribute('onclick', "MoveUp(this)");
	
	var MOVEDOWN = document.createElement("div");
		MOVEDOWN.setAttribute('class', "movedown");
		MOVEDOWN.setAttribute('onclick', "MoveDown(this)");
	
	var DURATION_SPAN = document.createElement("span");
		DURATION_SPAN.innerHTML = 'Durée&nbsp;:&nbsp;';
	
	var DURATION_EDITABLE = document.createElement("div");
		DURATION_EDITABLE.setAttribute('id', ID+"_Duration");
		DURATION_EDITABLE.setAttribute('class', "editable");
		DURATION_EDITABLE.setAttribute('onclick', "EditInput(this,3,'number')");
		DURATION_EDITABLE.innerHTML = DURATION;
	
	var DURATION = document.createElement("div");
		DURATION.setAttribute('class', "duration");
		DURATION.appendChild(DURATION_SPAN);
		DURATION.appendChild(DURATION_EDITABLE);
		DURATION.innerHTML+= '&nbsp;min';

	//Build the whole sequence
	
	var PAUSE = document.createElement("div");
		PAUSE.setAttribute('class', "sequence pause")
		PAUSE.setAttribute('data-start', "--:--");
		PAUSE.setAttribute('id', ID);
		PAUSE.appendChild(DELETE);
		PAUSE.appendChild(MOVEUP);
		PAUSE.appendChild(MOVEDOWN);
		PAUSE.appendChild(DURATION);

	//Create Pause Wrapper
	
	var SEQUENCES = document.getElementById("Sequences");
		SEQUENCES.appendChild(PAUSE);
}

function SelectSpeaker(what){
	var ID = what.dataset.id;
    var ATTENDANCE = what.dataset.attendance;
	var OPTIONS = document.getElementById("ChapterSpeakers_options");
	var SPEAKERS = OPTIONS.parentElement;
	
    var SPEAKER = new Object();
        SPEAKER['id'] = ID;
        SPEAKER['name'] = what.innerHTML;
        SPEAKER['attendance'] = what.dataset.attendance;
    var SPEAKINGPEAKER = AddSpeakingSpeaker(SPEAKER);
    
	SPEAKERS.removeChild(OPTIONS);
	
	if(SPEAKERS.getElementsByClassName(ID).length == 0){
        SPEAKERS.insertBefore(SPEAKINGPEAKER,SPEAKERS.firstChild);
        //add HF need is public
        if(what.dataset.id == "public") document.getElementById(SPEAKERS.parentElement.id+'_hf').checked = true;
    }
	else { alert("Cet·te intervenant·e est déjà dans la liste de cette séquence.") }
	
	event.stopPropagation();
}

function AddSpeakingSpeaker(SPEAKER){
    if(Object.prototype.toString.call(SPEAKER) === '[object Object]'){

        var DELETE = document.createElement("div");
            DELETE.setAttribute('class', "delete");
            DELETE.setAttribute('onclick', "Remove(this)");

        var SPEAKINGPEAKER = document.createElement("div");
            SPEAKINGPEAKER.setAttribute('class',"button "+SPEAKER['id']);
            SPEAKINGPEAKER.setAttribute('data-attendance',SPEAKER['attendance']);
            SPEAKINGPEAKER.innerHTML = SPEAKER['name'];
            SPEAKINGPEAKER.appendChild(DELETE)
        
        return SPEAKINGPEAKER;
    }
    else{ alert('Error creating speaking speaker!'); return false; }
}

//Escape the XML entities
function MakeSafe(HTML){
    if(HTML.match(/["'<>&]/g)){ return '<![CDATA['+HTML+']]>'; }
    else{ return HTML; }
}

function SaveFiche(REF){
    //Collect Video Info
    var Video = new Object();
        Video['title'] = MakeSafe(document.getElementById('VideoTitle').innerHTML);
        Video['visibility'] = document.getElementById('VideoVisibility').dataset.visibility;
        Video['date'] = document.getElementById('VideoDate').dataset.date;
        Video['description'] = MakeSafe(document.getElementById('VideoDescription').innerHTML);

    //Collect Event Info
    var Event = new Object();
        Event['title'] = MakeSafe(document.getElementById('EventTitle').innerHTML);
        Event['subtitle'] = MakeSafe(document.getElementById('EventSubTitle').innerHTML);
        Event['style'] = document.getElementById('EventStyle').dataset.css;
        Event['location'] = document.getElementById('EventLocation').innerHTML;
        Event['onsite'] = document.getElementById('OnSiteAttendance').innerHTML;
        Event['remote'] = document.getElementById('RemoteAttendance').innerHTML;
        Event['release'] = document.getElementById('BroadcastMethode').innerHTML;
        Event['date'] = document.getElementById('ShootingDate').dataset.date;
        Event['start'] = document.getElementById('StartTime').value;
    
    //Account for other location
    if(Event['location'] == "Autre...") Event['location'] = MakeSafe(document.getElementById('OtherLocation').innerHTML);
    
    //Collect Speakers Info
    var Speakers = document.getElementsByClassName('speaker');
    var Speaker = new Object();
    var sp = 1;
    
    for(speaker of Speakers){
        //Collect values & account for default
        var NAME = speaker.getElementsByClassName('name')[0].innerHTML != speaker.getElementsByClassName('name')[0].dataset.original ? speaker.getElementsByClassName('name')[0].innerHTML : "";
        var FUNCTION = speaker.getElementsByClassName('function')[0].innerHTML != speaker.getElementsByClassName('function')[0].dataset.original ? speaker.getElementsByClassName('function')[0].innerHTML : "";
        var TRANSLATION = speaker.getElementsByClassName('translation')[0].innerHTML != speaker.getElementsByClassName('translation')[0].dataset.original ? speaker.getElementsByClassName('translation')[0].innerHTML : "";
        //Make sure not to save empty speakers
        if(NAME.length+FUNCTION.length+TRANSLATION.length > 0){
            //Populate array
            Speaker['SP'+sp] = new Object();
            Speaker['SP'+sp]['id'] = speaker.id;
            Speaker['SP'+sp]['name'] = MakeSafe(NAME);
            Speaker['SP'+sp]['function'] = MakeSafe(FUNCTION);
            Speaker['SP'+sp]['translation'] = MakeSafe(TRANSLATION);
            Speaker['SP'+sp]['attendance'] = speaker.dataset.attendance;
            Speaker['SP'+sp]['link'] = MakeSafe(speaker.getElementsByClassName('link')[0].innerHTML);
            sp++;
        }
    }

    //Collect Sequences
    var Sequences = document.getElementsByClassName('sequence');
    var Sequence = new Object();
    var sq = 1; //Make sure sequences are in order

    for(sequence of Sequences){
        Sequence['SQ'+sq] = new Object();
        //Get sequence ID
        Sequence['SQ'+sq]['id'] = sequence.id;
        //Get sequence type
        Sequence['SQ'+sq]['type'] = sequence.classList.item(1);
        //Get sequence duration except for transitions type
        if(Sequence['SQ'+sq]['type'] != 'transition'){ Sequence['SQ'+sq]['duration'] = document.getElementById(sequence.id+'_Duration').innerHTML; }
        //Get transition HTML content
        if(Sequence['SQ'+sq]['type'] == 'transition'){ Sequence['SQ'+sq]['html'] = MakeSafe(document.getElementById(sequence.id+'_Chapter').value); }
        //Get Speech info
        if(Sequence['SQ'+sq]['type'] == 'speech'){
            //Get Technical Needs
            var needs = new Array();
            if(document.getElementById(sequence.id+'_pupitre').checked){ needs.push('pupitre') }
            if(document.getElementById(sequence.id+'_bureau').checked){ needs.push('bureau') }
            if(document.getElementById(sequence.id+'_fauteuil').checked){ needs.push('fauteuil') }
            if(document.getElementById(sequence.id+'_hf').checked){ needs.push('hf') }
            if(document.getElementById(sequence.id+'_pc').checked){ needs.push('pc') }
            Sequence['SQ'+sq]['needs'] = needs.toString();
            
            //Get speaking speakers ID
            var SpeakingSpeakers = document.getElementById(sequence.id).getElementsByClassName('speakers')[0].getElementsByClassName('button');
            var speaking = new Array();
            for(speakingspeaker of SpeakingSpeakers){
                SpeakingID = speakingspeaker.classList.item(1);
                if(SpeakingID != null){ speaking.push(SpeakingID); }
            }
            Sequence['SQ'+sq]['speaking'] = speaking.toString();
            
            //Get Technical Needs
            Sequence['SQ'+sq]['extra'] = new Object();
                //DDR section
                Sequence['SQ'+sq]['extra']['ddr'] = new Object();
                if(document.getElementById(sequence.id+'_ddr').checked){ Sequence['SQ'+sq]['extra']['ddr']['checked'] = true; }
                else{ Sequence['SQ'+sq]['extra']['ddr']['checked'] = false; }
                Sequence['SQ'+sq]['extra']['ddr']['sources'] = MakeSafe(document.getElementById(sequence.id+"_ddrsources").innerHTML);
                //Scroll section
                Sequence['SQ'+sq]['extra']['scroll'] = new Object();
                if(document.getElementById(sequence.id+'_scroll').checked){ Sequence['SQ'+sq]['extra']['scroll']['checked'] = true; }
                else{ Sequence['SQ'+sq]['extra']['scroll']['checked'] = false; }
                Sequence['SQ'+sq]['extra']['scroll']['message'] = document.getElementById(sequence.id+"_scrollmessage").innerHTML;
                //Requests section
                Sequence['SQ'+sq]['extra']['requests'] = new Object();
                if(document.getElementById(sequence.id+'_requests').checked){ Sequence['SQ'+sq]['extra']['requests']['checked'] = true; }
                else{ Sequence['SQ'+sq]['extra']['requests']['checked'] = false; }
                Sequence['SQ'+sq]['extra']['requests']['specifications'] = MakeSafe(document.getElementById(sequence.id+"_requestsspecs").innerHTML);
        }
        sq++;
    }
    
    //Build XML Object
    var Fiche = new Object();
        Fiche['video'] = Video;
        Fiche['event'] = Event;
        Fiche['speaker'] = Speaker;
        Fiche['sequence'] = Sequence;
    
    //console.log(JSON.stringify(XML));
    var FICHE = OBJtoXML(Fiche);
    //console.log(XML);
    var XML = '<?xml version="1.0" encoding="UTF-8"?>';
        XML+= '<fiche>'+FICHE+'</fiche>';
    
	//Create hidden action iframe
    var iframe = document.createElement('iframe');
        iframe.setAttribute('class', 'hidden');
        iframe.setAttribute('id','action');
        iframe.setAttribute('name','action');
        document.body.appendChild(iframe);
	//Create hidden form to send XML
	var save_form = document.createElement('form');
		save_form.setAttribute('id','saveform');
		save_form.setAttribute('class','hidden');
		save_form.setAttribute('action','src/php/save.php');
		save_form.setAttribute('method','post');
		save_form.setAttribute('target','action');
		document.body.appendChild(save_form);
	//Create XML input
	var xml_input = document.createElement('input');
		xml_input.setAttribute('value',XML);
		xml_input.setAttribute('name','xml');
		save_form.appendChild(xml_input);
    //Create REF input
    var ref_input = document.createElement('input');
        ref_input.setAttribute('value',REF);
        ref_input.setAttribute('name','ref');
        save_form.appendChild(ref_input);
    //Send XML to be saved
    console.log(XML);
    save_form.submit();
}

function EnvoyerFiche(){    
    if(REF != null){
        var MSG = "Veuillez indiquer votre email afin que l'équipe Multimédia puisse revenir vers vous :";
        let SENDER = prompt(MSG);
        if(SENDER != null && ValidEmail(SENDER)){
            //Create hidden action iframe
            var iframe = document.createElement('iframe');
                iframe.setAttribute('class', 'hidden');
                iframe.setAttribute('id','action');
                iframe.setAttribute('name','action');
                document.body.appendChild(iframe);
            //Create hidden form to send XML
            var save_form = document.createElement('form');
                save_form.setAttribute('id','saveform');
                save_form.setAttribute('class','hidden');
                save_form.setAttribute('action','src/php/send.php');
                save_form.setAttribute('method','post');
                save_form.setAttribute('target','action');
                document.body.appendChild(save_form);
            //Create REF input
            var ref_input = document.createElement('input');
                ref_input.setAttribute('value',REF);
                ref_input.setAttribute('name','ref');
                save_form.appendChild(ref_input);
            //Create sender input
            var sender_input = document.createElement('input');
                sender_input.setAttribute('value',SENDER);
                sender_input.setAttribute('name','sender');
                save_form.appendChild(sender_input);
            //Send request to Multimédia
            save_form.submit();
        }
        else{ alert('L\'adresse email indiquée n\'est pas valide.\r\nMerci de recommencer...'); }
    }
    else{ alert('Avant d\'envoyer votre demande au service Multimédia, merci de l\'enregistrer en cliquant sur "Sauvegarder".'); }
}

function CopyFiche(){
    //Generate new ref
    var NewREF = Math.floor(new Date().getTime() / 1000);
    
    if(REF != null){
        //Create hidden action iframe
        var iframe = document.createElement('iframe');
            iframe.setAttribute('class', 'hidden');
            iframe.setAttribute('id','action');
            iframe.setAttribute('name','action');
            document.body.appendChild(iframe);
        //Create hidden form to send XML
        var save_form = document.createElement('form');
            save_form.setAttribute('id','saveform');
            save_form.setAttribute('class','hidden');
            save_form.setAttribute('action','src/php/copy.php');
            save_form.setAttribute('method','post');
            save_form.setAttribute('target','action');
            document.body.appendChild(save_form);
        //Create old REF input
        var oldref_input = document.createElement('input');
            oldref_input.setAttribute('value',REF);
            oldref_input.setAttribute('name','oldref');
            save_form.appendChild(oldref_input);
        //Create new REF input
        var newref_input = document.createElement('input');
            newref_input.setAttribute('value',NewREF);
            newref_input.setAttribute('name','newref');
            save_form.appendChild(newref_input);
        //Send request to Multimédia
        save_form.submit();
    }
    else{ alert('Pour dupliquer une demande, il vous faut d\'abord la sauvegarder ou en charger une déjà enregistrée en cliquant sur "Ouvrir".'); }
}

function Convert2Schedule(MESSAGE,VIDEO,EVENT){
    //Handle error messages
    if(MESSAGE.startsWith('Erreur')){ alert(MESSAGE); }
    //Invite Event and Live scheduling
    else if(confirm(MESSAGE)){
        //Set Global Variables
        window.VideoObject = VIDEO;
        window.EventObject = EVENT;
        console.log(VideoObject);
        console.log(EventObject);
        //Show Stp Two
        document.getElementById('Convert_StepOne').classList.add('hidden');
        document.getElementById('Convert_StepTwo').classList.remove('hidden');
        if(EventObject['release'] == "Synchrone"){ document.getElementById('CreateLive').disabled = false; GenerateThumbnail(); }
    }
}

function ClearConvertPopUp(){
    var action = document.getElementById('action');
    var saveform = document.getElementById('saveform');
    var convert_popup = document.getElementById('Convert_options');
    if(action){ action.parentElement.removeChild(action); }
    if(saveform){ saveform.parentElement.removeChild(saveform); }
    if(convert_popup){ convert_popup.parentElement.removeChild(convert_popup); }
}


function GetCalendarID(CALENDAR){
    if(CALENDAR == "Amphi Condorcet Bas") return "575r6ica7qtduonrjfg7q2m1go@group.calendar.google.com";
    else if(CALENDAR == "Amphi Condorcet Haut") return "rsoshe708q04sb0uc89la8lrvk@group.calendar.google.com";
    else if(CALENDAR == "Amphi Costa") return "121e37bb28eda13e3f8717be0a0286167f1d1530b68cd5a086dea7715563dc2b@group.calendar.google.com";
    else if(CALENDAR == "Amphi Michelet") return "97159241ece74cdd6f2a134262ccec10671774077d3f7f0ef011cd738cf1bbdd@group.calendar.google.com";
    else if(CALENDAR == "Studio") return "0504bee628c25fe46ce6b6afbe50a69c5e7f1aa7c778762cfe3fb12aced76609@group.calendar.google.com";
    else{ return "e7c8f50fc1313e08591f957bf36e06a64e590f8d6b7e171b85843adbb342b094@group.calendar.google.com"; }//Mobile for LABs & Else
}

function GetDate4Google(){
    var StartTime = document.getElementById('StartTime').value;
    var ShootingDate = document.getElementById('ShootingDate').dataset.date;
    var EndTime = document.getElementById('EndTime').value;
    
    var GoogleDate = new Object();
        GoogleDate['start'] = ShootingDate+"T"+StartTime+":00";
        GoogleDate['end'] = ShootingDate+"T"+EndTime+":00";
    
    return GoogleDate;
}

function getAccessToken(TYPE){
    var scope;
    var callback;
    if(TYPE == "YouTube"){ scope = "https://www.googleapis.com/auth/youtube.force-ssl"; callback = CreateLive; }
    if(TYPE == "Calendar"){ scope = "https://www.googleapis.com/auth/calendar.events"; callback = ScheduleLive; }
    
    const client = window.google.accounts.oauth2.initTokenClient({
    client_id: '902579757667-sa4dhe6192i5hoqmqfead1m0ugddp99o.apps.googleusercontent.com',
    scope: scope,
    callback: callback,
    });
    client.requestAccessToken()
}

function CreateLive(response){
    console.log(response);
    console.log("here");
    var accessToken = response.access_token;
    //Send Request to API
    var xhr_video = new XMLHttpRequest();
        //Create Video Request
        xhr_video.addEventListener("load", (event) => {
            var video_response = JSON.parse(xhr_video.responseText);
            console.log(video_response);
        /**/
            var VideoID = video_response.id;
            //Create Thumbnail Request
            var xhr_thumbnail = new XMLHttpRequest();
                xhr_thumbnail.addEventListener("load", (event) => { console.log(JSON.parse(xhr_thumbnail.responseText)); confirmVideoLink(VideoID); });
                xhr_thumbnail.open('POST', 'https://www.googleapis.com/upload/youtube/v3/thumbnails/set?videoId=' + VideoID + '&key=AIzaSyArXTLfXmaf6R8t5c9Oyw2I2owXcqNYnec');
                xhr_thumbnail.setRequestHeader('Authorization', 'Bearer ' + accessToken);

            // Create FormData object to send thumbnail
            var formData = new FormData();
            var ImageData = thumbnail.split(',');
            formData.append('media', base64ToBlob(ImageData[1]), 'thumbnail.png');
            //Send Thumbnail Request
            xhr_thumbnail.send(formData);
        /**/
        });
        xhr_video.open('POST', 'https://youtube.googleapis.com/youtube/v3/liveBroadcasts?part=snippet&part=status&key=AIzaSyArXTLfXmaf6R8t5c9Oyw2I2owXcqNYnec');
        xhr_video.setRequestHeader('Authorization', 'Bearer ' + accessToken);
        xhr_video.setRequestHeader('Accept', 'application/json');
        xhr_video.setRequestHeader('Content-Type', 'application/json');

        const live = {
            "snippet": {
                "scheduledStartTime": VideoObject['start'],
                "title": VideoObject['title'],
                "description": VideoObject['description'],
                "scheduledEndTime": VideoObject['end'],
            },
            "status": {
                "privacyStatus": VideoObject['visibility']
            }
        };
        //Send Video Request
        xhr_video.send(JSON.stringify(live));
        console.log(live);
}

function base64ToBlob(base64Data) {
    const binaryString = window.atob(base64Data);
    const byteNumbers = new Array(binaryString.length);

    for (let i = 0; i < binaryString.length; i++) {
        byteNumbers[i] = binaryString.charCodeAt(i);
    }

    const byteArray = new Uint8Array(byteNumbers);
    return new Blob([byteArray], { type: 'image/png' });
}

function GenerateThumbnail(){
    //Get Thumbnail
    const Thumbnail = document.getElementById('Thumbnail').contentWindow;
    //Add Thumbnail CSS
    const ThumbnailHead = Thumbnail.document.head;
    var ThumbnailCSS = Thumbnail.document.createElement('link');
        ThumbnailCSS.setAttribute('rel','stylesheet');
        ThumbnailCSS.setAttribute('href','/fiche/src/css/thumbnail.css');
        ThumbnailHead.appendChild(ThumbnailCSS);
    
    const screenshotTarget = Thumbnail.document.body;

     window.setTimeout(function(){
         html2canvas(screenshotTarget,{
            width: 1280,
            height: 720,
            allowTaint: true,
            useCORS: true,
            logging: true,
            }).then((canvas) => {
        window.thumbnail = canvas.toDataURL("image/png");
        ThumbnailHead.removeChild(ThumbnailCSS);
        /*
        var link = document.createElement('a');
            link.href = thumbnail;
            link.download = 'thumbnail.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        */
        });
     }, 100);
}

function confirmVideoLink(VideoID){
    var link = "https://youtube.com/live/"+VideoID;
    console.log("Video Created: "+link);
    EventObject['video'] = link;
    if(confirm("Votre diffusion en directe a bien été crée.\r\nVoulez-vous ouvrir la vidéo dans un nouvel onglet ?")){ window.open(link, "_blank"); }
}

function ScheduleLive(response){
    console.log(response);
    console.log(EventObject['start']);
    var accessToken = response.access_token;
    var xhr = new XMLHttpRequest();
        xhr.addEventListener("load", (event) => { confirmEventLink(xhr.responseText); });
        xhr.open('POST', 'https://www.googleapis.com/calendar/v3/calendars/'+GetCalendarID(EventObject['location'])+'/events');
        xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Content-Type', 'application/json');
    
    const live = {
        "end": {
            "dateTime": EventObject['end'],
            "timeZone": "Europe/Paris"
        },
        "start": {
            "dateTime": EventObject['start'],
            "timeZone": "Europe/Paris"
        },
        "description": EventObject['details'],
        "location": EventObject['video'],
        "summary": EventObject['text']
    };
    
    xhr.send(JSON.stringify(live));
}

function confirmEventLink(response){
    var responseObject = JSON.parse(response);
    console.log(responseObject);
    var link = responseObject.htmlLink;
    console.log("Event Scheduled: "+link);
    if(confirm("Votre diffusion en directe a bien ajoutée à votre calendrier.\r\nVoulez-vous ouvrir l'événement dans un nouvel onglet ?")){ window.open(link, "_blank"); }
}