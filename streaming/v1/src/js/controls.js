// JavaScript Document
//Default Graphic Modules States
var LT_State = 'OFF';
var SB_State = 'OFF';


//Raccourcis Clavier
var keys = {};
onkeydown = onkeyup = function(e){
	e = e || event;
	e.which = e.which || e.keyCode;
	keys[e.which] = e.type === 'keydown';
		
	//Ctrl + Alt + t Show/Hide Lower Third
	if(keys[17] && keys[18] && keys[84]) { LT_State === "ON" ? Hide(document.getElementById('LT2')) : Show(document.getElementById('LT1')); }
	//Ctrl + Alt + b Show/Hide Scrolling Banner
	if(keys[17] && keys[18] && keys[66]) { SB_State === "ON" ? Hide(document.getElementById('SB2')) : Show(document.getElementById('SB1')); }
	//Ctrl + Alt + u Send Chapter Title
	if(keys[17] && keys[18] && keys[85]) { Transition(document.getElementById('CT1')); }
	//Ctrl + Alt + l Switch Logo ENPJJ ON/OFF (w/banner)
	if(keys[17] && keys[18] && keys[76]) {
		var ENPJJ = document.getElementById('ENPJJ');
		ENPJJ.checked = ENPJJ.checked ? false : true;
		ENPJJ.onchange();
	}
	//Ctrl + Alt + v Switch TopBanner ON/OFF
	if(keys[17] && keys[18] && keys[86]) {
		var TopBanner = document.getElementById('TopBanner');
		TopBanner.checked = TopBanner.checked ? false : true;
		TopBanner.onchange();
	}
	//Ctrl + Alt + m Switch Marianne ON/OFF
	if(keys[17] && keys[18] && keys[77]) {
		var Marianne = document.getElementById('Marianne');
		Marianne.checked = Marianne.checked ? false : true;
		Marianne.onchange();
	}
}

//Open Broadcast Channels
var lt = new BroadcastChannel('obs-lower-third-channel');
var sn = new BroadcastChannel('obs-side-notes-channel');
var si = new BroadcastChannel('obs-stream-info-channel');
var sb = new BroadcastChannel('obs-scrolling-banner-channel');
var cd = new BroadcastChannel('obs-count-down-channel');
var gp = new BroadcastChannel('obs-global-parmeter-channel');
var ct = new BroadcastChannel('obs-chapter-transition-channel');

function Seperate(what){
    if(window.open(what.value,'_blank')){
        window.parent.document.getElementById('MessageFrame').className = "full";
        var GraphicsFrame = window.parent.document.getElementById('GraphicsFrame');
        GraphicsFrame.parentNode.removeChild(GraphicsFrame);
    }
}

function OpenLink(what){ window.open(what.value,'_blank'); }

function Switch(what){
	var ParameterList = [];

	//Check if linked -> get LockStatus
	if(what.closest(".linked")){
		var LinkedGroupe = what.closest(".linked");
		var LockStatus = LinkedGroupe.getElementsByClassName("lock")[0].innerHTML;	
	}
	else { LockStatus = "X"; }
	
	//Get Parameter List
	if(LockStatus == "8"){
		ParameterList = LinkedGroupe.getElementsByClassName("parameter");
	}
	else {
		ParameterList.push(what.closest(".parameter"));
	}

	//Get Status to 
	var SwitchStatus = what.checked;
	
	for(var i = 0; i < ParameterList.length; i++){
		var parameter = ParameterList[i].getElementsByClassName("switch")[0].getElementsByTagName("input")[0];
		if(parameter != what){ parameter.checked = parameter.checked ? false : true; }
		
		//Write Log
		var parameter_id = parameter.id;
		var parameter_status = parameter.checked ? "ON" : "OFF";
		gp.postMessage(parameter_id + '|' + parameter_status);
	}
}

function UnLink(what){
	var current_state = what.innerHTML;
	if(current_state == "8") what.innerHTML = "X";
	else what.innerHTML = "8";
}

function CorrectKeys(e){
	var charValue= String.fromCharCode(e.keyCode);
	
	var allowed = [8, 46, 16, 36, 35, 37, 39, 56, 54, 109];
	//8 = BSP, 46 = SUPPR, 16 = MAJ, 36 = START, 35 = END, 37 = <-, 39= ->, 56 = "_" (8) ; 54 = "-" (6) ; 109 = "-" (keypad)
	
	if(e.which>=65 && e.which<=90){ return true; } //Letters
	else if(e.which>=96 && e.which<=105){ return true; } //Numbers
	else if(allowed.includes(e.which)){ return true; } //Allowed
	else { e.preventDefault(); }
}

function DropDown(what){
	if(what.parentNode.classList.contains("active")) { what.parentNode.classList.remove("active"); }
	else { what.parentNode.classList.add("active"); }
}

function Unset(REF){
	event.stopPropagation();
	
	var MSG = 'Êtes-vous sûr de vouloir supprimer la collection "'+REF+'" et tous les titres qu\'elle contient ?';
	
	if(confirm(MSG)){
		//Create hidden iframe
		if(!document.getElementById('manager'))
			{
			var iframe = document.createElement('iframe');
				iframe.setAttribute('class', 'hidden');
				iframe.setAttribute('id','manager');
				iframe.setAttribute('name','manager');
				document.body.appendChild(iframe);
			}
		//Create hidden form to send data
		var delete_form = document.createElement('form');
			delete_form.setAttribute('class','hidden');
			delete_form.setAttribute('action','../src/php/manager.php?action=delete');
			delete_form.setAttribute('method','post');
			delete_form.setAttribute('target','manager');
			document.body.appendChild(delete_form);
		//Create REF input
		var ref_input = document.createElement('input');
			ref_input.setAttribute('value',REF);
			ref_input.setAttribute('name','ref');
			delete_form.appendChild(ref_input);

		if(REF != ""){ delete_form.submit(); }
	}
}

function Load(what){
	var REF = what.getElementsByClassName('ref')[0].innerHTML;
	window.location.assign("controls.php?collection="+REF);
}

function SetData(ID, what){
	var VALUE = what.getAttribute('data-value');
	document.getElementById(ID).setAttribute('data-value', VALUE);
}

function SetCountDown(what)
{
	delai_to_send = document.getElementById("count-down-delai").value;
	if(delai_to_send ==""){ alert("Durée incorrecte : doit être de type \"00:00:00\"."); }
	else { cd.postMessage(delai_to_send); }
}

function Show(what){
	var TYPE = what.parentNode.id;
	if(TYPE == "LowerThird"){
		name_to_send = $("#lower-thirds-name:text").val();
		function_to_send = $("#lower-thirds-function:text").val();
		translation_to_send = $("#lower-thirds-translation:text").val();
		lt.postMessage(name_to_send + '|' + function_to_send + '|' + translation_to_send + '|' + 'ON');
		window.setTimeout(function () { LT_State = 'ON'; }, 1000);
	}
	else if(TYPE == "ScrollingBanner"){
		message_to_send = document.getElementById('scrolling-banner-message').value;
		//class_to_send = document.getElementById('scrolling-banner-class').getAttribute("data-value");
		class_to_send = "none";
		sb.postMessage(message_to_send + '|' + class_to_send + '|' + 'ON');
		window.setTimeout(function () { SB_State = 'ON'; }, 1000);
	}
}

function Hide(what){
	var TYPE = what.parentNode.id;	
	if(TYPE == "LowerThird"){
		lt.postMessage(name_to_send + '|' + function_to_send + '|' + translation_to_send + '|' + 'OFF');
		window.setTimeout(function () { LT_State = 'OFF'; }, 1000);
	}
	else if(TYPE == "ScrollingBanner"){
		sb.postMessage(message_to_send + '|' + class_to_send + '|' + 'OFF');
		window.setTimeout(function () { SB_State = 'OFF'; }, 1000);
	}
}

function Send(what){
	var TYPE = what.parentNode.id;
	
	if(TYPE == "StreamInfo")//For control.php
	{
	title_to_send = $("#stream-info-title:text").val();
	subtitle_to_send = $("#stream-info-subtitle:text").val();
	date_to_send = $("#stream-info-date").val();
	style_to_send = $("#stream-info-style").data("css");
		
	date_to_send = FormateDate(date_to_send,1);

	si.postMessage(title_to_send + '|' + subtitle_to_send + '|' + date_to_send + '|' + style_to_send);
	}
}

//Lood Messages in PREVIOUS MESSAGE
sn.onmessage = function (ev) {
    received_data=ev.data;
    note_content = received_data.split("|");

    var ID = note_content[0];
    var STAMP = note_content[1];
    var NOTE = note_content[2];
    
    document.getElementById("current-id").value = ID;
    document.getElementById("current-stamp").value = STAMP;
	document.getElementById("current-content").value = NOTE;
    }

function Preview(what){
	var TYPE = what.parentNode.id;
	if(TYPE == "PublicMessages"){
        NOTE = document.getElementById("public-html").value;
        STAMP = "Public";
    }
    else if(TYPE == "InternalMessages"){
        NOTE = document.getElementById("internal-html").value;
        STAMP = "Interne";
    }
    var transfered_content = "preview|"+STAMP+"|" + NOTE;
	sn.postMessage(transfered_content);
}

function Save(what){
	var REF = document.getElementById('collection-ref').value;
	var URL = window.location.href;
	var TYPE = what.parentNode.id;
	event.stopPropagation();
	
	//Create hidden iframe
	if(!document.getElementById('manager'))
		{
		var iframe = document.createElement('iframe');
			iframe.setAttribute('class', 'hidden');
			iframe.setAttribute('id','manager');
			iframe.setAttribute('name','manager');
			document.body.appendChild(iframe);
		}
	//Create hidden form to send data
	var save_form = document.createElement('form');
		save_form.setAttribute('class','hidden');
		save_form.setAttribute('action','../src/php/manager.php?action=save');
		save_form.setAttribute('method','post');
		save_form.setAttribute('target','manager');
		document.body.appendChild(save_form);
	//Create TYPE input
	var type_input = document.createElement('input');
		type_input.setAttribute('value',TYPE);
		type_input.setAttribute('name','type');
		save_form.appendChild(type_input);
	//Create REF input
	var ref_input = document.createElement('input');
		ref_input.setAttribute('value',REF);
		ref_input.setAttribute('name','ref');
		save_form.appendChild(ref_input);
	//Create Original URL input
	var url_input = document.createElement('input');
		url_input.setAttribute('value',URL);
		url_input.setAttribute('name','url');
		save_form.appendChild(url_input);

	//Save Stream Infos
	if(TYPE == "StreamInfo"){
		//Get Data to Save
		var TITLE = document.getElementById('stream-info-title').value;
		var SUBTITLE = document.getElementById('stream-info-subtitle').value;
		var DATE = document.getElementById('stream-info-date').value;
		var STYLE = $("#stream-info-style").data("css");

		if(TITLE != ""){
			//Create TITLE input
			var title_input = document.createElement('input');
				title_input.setAttribute('value',TITLE);
				title_input.setAttribute('name','title');
				save_form.appendChild(title_input);
			//Create SUBTITLE input
			var subtitle_input = document.createElement('input');
				subtitle_input.setAttribute('value',SUBTITLE);
				subtitle_input.setAttribute('name','subtitle');
				save_form.appendChild(subtitle_input);
			//Create DATE input
			var date_input = document.createElement('input');
				date_input.setAttribute('value',DATE);
				date_input.setAttribute('name','date');
				save_form.appendChild(date_input);
			//Create STYLE input
			var style_input = document.createElement('input');
				style_input.setAttribute('value',STYLE);
				style_input.setAttribute('name','style');
				save_form.appendChild(style_input);
			//Send Data to be Saved
			save_form.submit();
		}
		else{ alert("Vous devez indiquer le titre du stream au minimum !"); document.body.removeChild(iframe); document.body.removeChild(save_form); }
	}
	
	//Save Lower Third
	if(TYPE == "LowerThird"){
		//Get Data to Save
		var NAME = document.getElementById('lower-thirds-name').value;
		var FUNCTION = document.getElementById('lower-thirds-function').value;
		var TRANSLATION = document.getElementById('lower-thirds-translation').value;
		if(NAME != ""){
			//Create NAME input
			var name_input = document.createElement('input');
				name_input.setAttribute('value',NAME);
				name_input.setAttribute('name','name');
				save_form.appendChild(name_input);
			//Create FUNCTION input
			var function_input = document.createElement('input');
				function_input.setAttribute('value',FUNCTION);
				function_input.setAttribute('name','function');
				save_form.appendChild(function_input);
			//Create TRANSLATION input
			var translation_input = document.createElement('input');
				translation_input.setAttribute('value',TRANSLATION);
				translation_input.setAttribute('name','translation');
				save_form.appendChild(translation_input);
			//Send Data to be Saved
			save_form.submit();
		}
		else{ alert("Vous devez indiquer le nom de l'intervenant au minimum !"); document.body.removeChild(iframe); document.body.removeChild(save_form); }
	}
	
	//Save Chapter Title
	if(TYPE == "ChapterTransition"){
		//Get Data to Save
		var CHAPTER = document.getElementById('chapter-html').value;
		if(CHAPTER != ""){
			//Create CHAPTER input
			var chapter_input = document.createElement('input');
				chapter_input.setAttribute('value',CHAPTER);
				chapter_input.setAttribute('name','chapter');
				save_form.appendChild(chapter_input);
			//Send Data to be Saved
			save_form.submit();
		}
		else{ alert("Il n'y a rien à sauvegarder !"); document.body.removeChild(iframe); document.body.removeChild(save_form); }
	}
	
	//Save Scrolling Banner
	if(TYPE == "ScrollingBanner"){
		//Get Data to Save
		var MESSAGE = document.getElementById('scrolling-banner-message').value;
		//var CLASS = document.getElementById('scrolling-banner-class').getAttribute("data-value");
		var CLASS = "none";
		if(MESSAGE != ""){
			//Create MESSAGE input
			var message_input = document.createElement('input');
				message_input.setAttribute('value',MESSAGE);
				message_input.setAttribute('name','message');
				save_form.appendChild(message_input);
			//Create CLASS input
			var class_input = document.createElement('input');
				class_input.setAttribute('value',CLASS);
				class_input.setAttribute('name','class');
				save_form.appendChild(class_input);
			//Send Data to be Saved
			save_form.submit();
		}
		else{ alert("Vous devez indiquer le titre du stream au minimum !"); document.body.removeChild(iframe); document.body.removeChild(save_form); }
	}
}

function ClearAllMessages(what){ Transfer(what); }
function RecallMessage(what){ Transfer(what); }
function DeleteMessage(what){ Transfer(what); }

function Transfer(what){
	var TYPE = what.parentNode.id;
	event.stopPropagation();

	//Transfer Messages and Messages
	if(TYPE == 'PublicMessages' || TYPE == 'InternalMessages' || TYPE == "PreviousMessage"){
		//Get Variables
		var KEY = document.getElementById('stream-key').value;
		var ID = Number(Date.now());
		if(TYPE == "InternalMessages") {
			var STAMP = 'Interne';
			var CONTENT = document.getElementById('internal-html').value;
			//Correct TYPE for Graphics
			TYPE = "PublicMessages";
		}
		else if(TYPE == "PublicMessages"){
			//var STAMP = document.getElementById('note-life').value;
			var STAMP = "Public";
			var CONTENT = document.getElementById('public-html').value;
		}
		else if(TYPE == "PreviousMessage"){
			var COMMAND = what.innerHTML;
			if(COMMAND == "Vider"){ ID = ""; }
			else if(COMMAND == "Supprimer"){ ID = document.getElementById('current-id').value; }
            STAMP = document.getElementById('current-stamp').value
			var PATH = '../ram/' + KEY + '.txt';
			var CONTENT = document.getElementById("current-content").value;
			//Correct TYPE for Graphics
			TYPE = "PublicMessages";
		}

		//Create hidden iframe
		if(!document.getElementById('manager'))
			{
			var iframe = document.createElement('iframe');
				iframe.setAttribute('class', 'hidden');
				iframe.setAttribute('id','manager');
				iframe.setAttribute('name','manager');
				document.body.appendChild(iframe);
			}
		//Create hidden form to send data
		var save_form = document.createElement('form');
			save_form.setAttribute('class','hidden');
			save_form.setAttribute('action','../src/php/manager.php?action=transfer');
			save_form.setAttribute('method','post');
			save_form.setAttribute('target','manager');
			document.body.appendChild(save_form);

		//Create TYPE input
		var type_input = document.createElement('input');
			type_input.setAttribute('value',TYPE);
			type_input.setAttribute('name','type');
			save_form.appendChild(type_input);

		//Create KEY input
		var key_input = document.createElement('input');
			key_input.setAttribute('value',KEY);
			key_input.setAttribute('name','key');
			save_form.appendChild(key_input);

		//Create LIFE input
		var lifespan_input = document.createElement('input');
			lifespan_input.setAttribute('value',STAMP);
			lifespan_input.setAttribute('name','stamp');
			save_form.appendChild(lifespan_input);

		//Create STAMP input
		var stamp_input = document.createElement('input');
			stamp_input.setAttribute('value',ID);
			stamp_input.setAttribute('name','id');
			save_form.appendChild(stamp_input);

		if(CONTENT != ""){
			//Create CONTENT input
			var note_input = document.createElement('input');
				note_input.setAttribute('value',CONTENT);
				note_input.setAttribute('name','content');
				save_form.appendChild(note_input);
			//Send Data to be Saved
			save_form.submit()
			var transfered_content = ID+"|"+STAMP+"|"+CONTENT;
			console.log ("Transfered Content: "+transfered_content);
			}
		else {
			alert("Il n'y a rien à transférer !");
			document.body.removeChild(iframe);
			document.body.removeChild(save_form);
			}
	}
}

function Delete(what){
	var REF = document.getElementById('collection-ref').value;
	var TYPE = what.parentNode.parentNode.parentNode.parentNode.parentNode.id;
	var FILE = what.parentNode.getElementsByClassName('file')[0].innerHTML;
	event.stopPropagation();

	//Make sure REF is not empty
	if(REF==""){ REF = "ENPJJ"; }
	
	//Create hidden iframe
	if(!document.getElementById('manager'))
		{
		var iframe = document.createElement('iframe');
			iframe.setAttribute('class', 'hidden');
			iframe.setAttribute('id','manager');
			iframe.setAttribute('name','manager');
			document.body.appendChild(iframe);
		}
	//Create hidden form to send data
	var delete_form = document.createElement('form');
		delete_form.setAttribute('class','hidden');
		delete_form.setAttribute('action','../src/php/manager.php?action=delete');
		delete_form.setAttribute('method','post');
		delete_form.setAttribute('target','manager');
		document.body.appendChild(delete_form);
	//Create REF input
	var ref_input = document.createElement('input');
		ref_input.setAttribute('value',REF);
		ref_input.setAttribute('name','ref');
		delete_form.appendChild(ref_input);
	//Create TYPE input
	var type_input = document.createElement('input');
		type_input.setAttribute('value',TYPE);
		type_input.setAttribute('name','type');
		delete_form.appendChild(type_input);
	//Create FILE input
	var file_input = document.createElement('input');
		file_input.setAttribute('value',FILE);
		file_input.setAttribute('name','file');
		delete_form.appendChild(file_input);
		delete_form.submit();

	if(FILE != ""){ delete_form.submit(); }
}

function Transition(what){
	var ChapterTitle = document.getElementById('chapter-html').value;
	ct.postMessage(ChapterTitle);
	what.disabled = true; window.setTimeout(function () { what.disabled = false; }, 10000);
}

function Insert(TYPE){
    if(TYPE == "QR"){ CLASS = "qrcode"; TXT = "du lien"; var URL="https://chart.apis.google.com/chart?chs=540x540&cht=qr&chld=L%7C1&chl="; }
    else{ CLASS = ""; TXT = "de l'image"; var URL=""; }
    
	var MSG = "Copier/Coller ci-dessous l'adresse Internet "+TXT+" que vous souhaitez insérer."
    var LINK = prompt(MSG);
	
	if(TYPE != "QR" && !ValidURL(LINK) && LINK != "")
	{
		alert("Cette URL ("+LINK+") n'est pas valide.\nMerci de réessayer...");
	}
	else
	{
        if(TYPE == "QR" && LINK.length < 500){ URL = URL + encodeURIComponent(LINK); }
        else if(TYPE == "QR" && LINK.length < 500){ alert("Cette URL ("+LINK+") est trop longue.\nMerci de réessayer..."); }
        else{ URL = LINK; }
		
        var IMG = '<img class="'+CLASS+'" src="../../../src/js/'+URL+'"><span class="link">'+LINK+'</span>';
		var HTML = document.getElementById('public-html');
			HTML.value+= IMG;
        
        Preview(HTML);
	}
}

function ValidURL(string)
{
	let url;
	try { url = new URL(string);}
	catch (_) { return false; }
	return url.protocol === "http:" || url.protocol === "https:";
}