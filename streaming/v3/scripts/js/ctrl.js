// JavaScript Document
//Get Stream Key
var URL = new URL(window.location.href);
var STR = URL.searchParams.get("str");
var APP = window.location.pathname;
var TOP_APP = window.top.location.pathname;

//Open Broadcast Channels
var lsc = new BroadcastChannel(STR+'-local-style-channel');
var lmc = new BroadcastChannel(STR+'-local-messages-channel');

//Send Set Counter Command
function SetCounter(){
    var MESSAGE = "Démarrer le compteur à (secondes) :";
    var START = prompt(MESSAGE,0);

    const DATA = {type:"cnt", start:START};
    Update(DATA);
}

//Resize screen if stand alone
function ResizeBody()
{
	var ZM = window.innerWidth/500;//480px = max-width + 2*10px (magin)
	if(ZM <= 1){ ZM = 1; }
	document.body.style.maxWidth = "480px";
	document.body.style.transform = "scale("+ZM+")";
}

//Ask for Stream Key if opened seperately
function CheckStream()
{    
    //Check APP
	if(TOP_APP.endsWith("messagerie.php") || TOP_APP.endsWith("pilotage.php") || TOP_APP.endsWith("dgc.php") || TOP_APP.endsWith("dmc.php"))
	{
		//Check for PREVIEW stream key
		if(STR == 'preview')
		{			
			var new_stream = prompt("Préciser le lieu du stream :", STR);
			if(new_stream != 'preview'){ window.top.location.href = TOP_APP+"?str="+new_stream; }
		}

		//Resize if CTRL solo
		if(APP == TOP_APP)
		{
			window.addEventListener("resize", ResizeBody);
			ResizeBody();
		}
		
		//Re-arrange Layout for Messages
		if(APP.endsWith("dmc.php") && TOP_APP.endsWith("pilotage.php") || APP.endsWith("dmc.php") && TOP_APP.endsWith("messagerie.php"))
		{
			document.body.style.columnCount = "3";
			document.body.style.maxWidth = "calc((((100vh + 10px) * 4) - 5px) * 16 / 9)";
			document.body.style.overflow = "hidden";
			document.body.style.boxSizing = "border-box";
		}
	}	
	else
	{
		var DSK_params = "";
		var DSK_name = "DSK";
		var APP_name = "";
		
		if(APP.endsWith("dmc.php")){ var DSK_params = "mode=light&"; var APP_name = "Messagerie"; APP="../messagerie.php"; DSK_name+= ' Light'; }
		else if(APP.endsWith("dgc.php")){ var APP_name = "DGC"; }

		var Comment = document.createComment(" Open Apps Seperately ")
		
		var ButtonDSK = document.createElement("button");
			ButtonDSK.setAttribute("class", 'half');
			ButtonDSK.setAttribute("onClick", 'OpenLink(this)');
			ButtonDSK.setAttribute("value", 'dsk.php?'+DSK_params+'str='+STR);
			ButtonDSK.innerHTML = 'Ouvrir "'+DSK_name+'" séparément';
		document.body.appendChild(ButtonDSK);

		var ButtonAPP = document.createElement("button");
			ButtonAPP.setAttribute("class", 'half');
			ButtonAPP.setAttribute("onClick", 'OpenLink(this)');
			ButtonAPP.setAttribute("value", APP+'?str='+STR);
			ButtonAPP.innerHTML = 'Ouvrir "'+APP_name+'" séparément';
		document.body.appendChild(ButtonAPP);

		var Spacer = document.createElement("div");
			Spacer.setAttribute("class", 'spacer');
		document.body.appendChild(Spacer);

		var BODY = document.body.childNodes[0];
		document.body.insertBefore(Comment,BODY);
		document.body.insertBefore(ButtonDSK,BODY);
		document.body.insertBefore(ButtonAPP,BODY);
		document.body.insertBefore(Spacer,BODY);
	}
}

//Update RAM File (communication between pages)
async function Update(DATA)
{
	var params = new URLSearchParams();
	for (let key in DATA) { params.append(key, DATA[key]); }
	
	await fetch('../scripts/php/updates.php?str='+STR, { method: 'POST', body: params });
	
	console.log('Command sent: '+JSON.stringify(DATA));
	
	document.body.classList.add('wait');
	setTimeout(function () { document.body.classList.remove('wait'); }, 3000);
}

//Common Functions ---------->

//Open/Close Drop Down Menu
function DropDown(what){
	if(what.parentNode.classList.contains("active")) { what.parentNode.classList.remove("active"); }
	else { what.parentNode.classList.add("active"); }
}

//Select Streaming Data from Drop Down Menu
function SetData(ID, what){
	var VALUE = what.getAttribute('data-value');
	document.getElementById(ID).setAttribute('data-value', VALUE);
}

//Show Various Graphics
function Show(what){
	var TYPE = what.parentNode.id;
	
	if(TYPE == "LowerThird"){
		name_to_send = $("#lower-thirds-name:text").val();
		function_to_send = $("#lower-thirds-function:text").val();
		translation_to_send = $("#lower-thirds-translation:text").val();

		const DATA = {type:"lt", state:"ON", name:name_to_send, function:function_to_send, translation:translation_to_send};
		Update(DATA);
		
		//Check for Auto OFF
		if(document.getElementById('AutoLT').checked == true){
			what.disabled = true;
			setTimeout(function () {
				what.disabled = false;
				const DATA = {type:"lt", state:"OFF", name:name_to_send, function:function_to_send, translation:translation_to_send};
				Update(DATA);
			}, 10000);//10s
		}
	}
	else if(TYPE == "ScrollingBanner"){
		message_to_send = document.getElementById('scrolling-banner-message').value;
		//class_to_send = document.getElementById('scrolling-banner-class').getAttribute("data-value");
		class_to_send = "none";
		
		const DATA = {type:"sb", state:"ON", message:message_to_send, class:class_to_send};
		Update(DATA);
		
		//Check for Auto OFF
		if(document.getElementById('AutoLT').checked == true){
			what.disabled = true;
			setTimeout(function () {
				what.disabled = false;
				const DATA = {type:"sb", state:"OFF", message:message_to_send, class:class_to_send};
				Update(DATA);
			}, 60000);//60s
		}
	}
	else if(TYPE == "ChapterTransition"){
		var ChapterTitle = document.getElementById('chapter-html').value;
		const DATA = {type:"ct", state:"ON", title:ChapterTitle};
		Update(DATA);
		
		//Check for Auto OFF
		if(document.getElementById('AutoCT').checked == true){
			what.disabled = true;
			setTimeout(function () {
				what.disabled = false;
				const DATA = {type:"ct", state:"OFF", title:ChapterTitle};
				Update(DATA);
			}, 10000);//10s
		}
	}
}

//Hide Various Graphics
function Hide(what){
	var TYPE = what.parentNode.id;	
	if(TYPE == "LowerThird"){
		const DATA = {type:"lt", state:"OFF"};
		Update(DATA);
	}
	else if(TYPE == "ChapterTransition"){
		const DATA = {type:"ct", state:"OFF"};
		Update(DATA);
	}
	else if(TYPE == "ScrollingBanner"){
		const DATA = {type:"sb", state:"OFF"};
		Update(DATA);
	}
}

//Save Streaming Data
function Save(what){
	var REF = document.getElementById('collection-ref').value;
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
		save_form.setAttribute('action','../scripts/php/manager.php?str='+STR+'&action=save');
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

//Delete Saved Streaming Data
function Delete(what){
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
	var delete_form = document.createElement('form');
		delete_form.setAttribute('class','hidden');
		delete_form.setAttribute('action','../scripts/php/manager.php?str='+STR+'&action=delete');
		delete_form.setAttribute('method','post');
		delete_form.setAttribute('target','manager');
		document.body.appendChild(delete_form);

	if(typeof what === 'object')//Deleting a file
	{
		var REF = document.getElementById('collection-ref').value;
		var TYPE = what.parentNode.parentNode.parentNode.parentNode.parentNode.id;
		var FILE = what.parentNode.getElementsByClassName('file')[0].innerHTML;
		var MSG = 'Êtes-vous sûr de vouloir supprimer la sauvegarde N°'+FILE+' ?';
		
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
		
		var Condition = FILE;
	}
	else //Deleting a Collection
	{
		var REF = what;
		var Condition = REF;
		var MSG = 'Êtes-vous sûr de vouloir supprimer la collection "'+REF+'" et tous les titres qu\'elle contient ?';
	}
	
	//Make sure REF is not empty
	if(REF==""){ REF = "ENPJJ"; }

	//Create REF input
	var ref_input = document.createElement('input');
		ref_input.setAttribute('value',REF);
		ref_input.setAttribute('name','ref');
		delete_form.appendChild(ref_input);

	if(Condition != "" && confirm(MSG)){ delete_form.submit(); }
}

//Global Parameters ---------->

//Switch ON/OFF Parameters
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

	//Get TYPE
	//var TYPE = what.parent.parent.parent.tagName;
	var TYPE = what.id;
	
	for(var i = 0; i < ParameterList.length; i++){
		var parameter = ParameterList[i].getElementsByClassName("switch")[0].getElementsByTagName("input")[0];
		if(parameter != what){ parameter.checked = parameter.checked ? false : true; }
	
		if(TYPE == "AutoLT" || TYPE == "AutoCT" || TYPE == "AutoSB")
		{
			if(TYPE == "AutoLT"){
			var Button_1 = document.getElementById('LT1');
			var Button_2 = document.getElementById('LT2');
			}
			else if(TYPE == "AutoCT"){
			var Button_1 = document.getElementById('CT1');
			var Button_2 = document.getElementById('CT2');
			}
			else if(TYPE == "AutoSB"){
			var Button_1 = document.getElementById('SB1');
			var Button_2 = document.getElementById('SB2');
			}
			
			//Disable/Enable Auto OFF
			if(parameter.checked == false)
			{
				Button_1.className = "half";
				Button_1.innerHTML = "ON";
				Button_1.disabled = false;//Force UNABLE if Auto Running
				Button_2.className = "half";
			}
			else if(parameter.checked == true)
			{
				Button_1.className = "full";
				Button_1.innerHTML = "Envoyer";
				Button_2.className = "hidden";
			}
		}
		else if(TYPE == "Marianne" || TYPE == "ENPJJ" || TYPE == "Copyright" || TYPE == "Partenaire")
		{
			//Write Log
			var ID = parameter.id;
			var STATE = parameter.checked ? "ON" : "OFF";

			const DATA = {type:"gp", id:ID, status:STATE};
			Update(DATA);
		}
	}
}

//Lock/Unlock linked parameters
function UnLink(what){
	var current_state = what.innerHTML;
	if(current_state == "8") what.innerHTML = "X";
	else what.innerHTML = "8";
}

//Show Runner ---------->
function Run(STATE){
	const DATA = {type:"sr", cmd:STATE};
	Update(DATA);		
}

//Load Collection ---------->
function Load(what){
	var REF = what.getElementsByClassName('ref')[0].innerHTML;
	window.location.assign("dgc.php?collection="+REF+"&str="+STR);
}

//Stream Info ---------->
function Send(what){
	title_to_send = $("#stream-info-title:text").val();
	subtitle_to_send = $("#stream-info-subtitle:text").val();
	date_to_send = $("#stream-info-date").val();
	style_to_send = $("#stream-info-style").data("css");
		
	date_to_send = FormateDate(date_to_send,1);

	const DATA = {type:"si", title:title_to_send, subtitle:subtitle_to_send, date:date_to_send, style:style_to_send};
	Update(DATA);
}

//Count Down ---------->
function SetCountDown(what)
{
	delai_to_send = document.getElementById("count-down-delai").value;
	if(delai_to_send ==""){ alert("Durée incorrecte : doit être de type \"00:00:00\"."); }
	else{
		const DATA = {type:"cd", delai:delai_to_send};
		Update(DATA);
	}
}

//Messages ---------->

//Lood Messages in PREVIOUS MESSAGE
lmc.onmessage = function (ev){ SetPreviousMessage(ev.data); }

function SetPreviousMessage(DATA)
{
    var ID = DATA['id'];
    var STAMP = DATA['stamp'];
    var CONTENT = DATA['content'];
	
    if(APP.endsWith("dmc.php"))
	{
		document.getElementById("current-id").value = ID;
		document.getElementById("current-stamp").value = STAMP;
		document.getElementById("current-content").value = CONTENT;
	}
}

//Preview Messages
function Preview(what){
	var TYPE = what.parentNode.id;
	if(TYPE == "PublicMessages"){
        CONTENT = document.getElementById("public-html").value;
        STAMP = "Public";
    }
    else if(TYPE == "InternalMessages"){
        CONTENT = document.getElementById("internal-html").value;
        STAMP = "Interne";
    }
	const DATA = {id:"preview", stamp:STAMP, content:CONTENT};
	lmc.postMessage(DATA);
}

function ClearAllMessages(what){ Transfer(what); }
function RecallMessage(what){ Transfer(what); }
function DeleteMessage(what){ Transfer(what); }

function Transfer(what){
	var TYPE = what.parentNode.id;
	event.stopPropagation();

    //Setup Variables
    var ID = Number(Date.now());
	var STAMP;
	var CONTENT = what.parentNode.getElementsByTagName('textarea')[0].value;
    
	if(TYPE == "InternalMessages")
	{
		STAMP = 'Interne';
	}
	else if(TYPE == "PublicMessages")
	{
		//STAMP = document.getElementById('note-life').value;
		STAMP = "Public";
	}
	else if(TYPE == "PreviousMessage")
	{
		ID = document.getElementById('current-id').value;
		STAMP = document.getElementById('current-stamp').value

		var COMMAND = what.innerHTML;
		if(COMMAND == "Vider"){ ID = STAMP = CONTENT = ""; }
		else if(COMMAND == "Supprimer"){ STAMP = "Delete"; }
		else if(COMMAND == "Renvoyer"){ ID = Number(Date.now()); }
	}

	if(CONTENT == "" && ID != ""){ alert("Il n'y a rien à transférer !"); }
	else
        {
        const DATA = {type:"ms",stamp:STAMP,id:ID,content:CONTENT};
        Update(DATA);
        SetPreviousMessage(DATA);

        //Clear previous content
        if(COMMAND != "Renvoyer"){ what.parentNode.getElementsByTagName('textarea')[0].value = ""; }
		}
}

//Insert Images or QR Code
function Insert(TYPE){
    if(TYPE == "QR"){ CLASS = "qrcode"; TXT = "du lien"; var SRC="https://chart.apis.google.com/chart?chs=540x540&cht=qr&chld=L%7C1&chl="; }
    else{ CLASS = ""; TXT = "de l'image"; var SRC=""; }
    
	var MSG = "Copier/Coller ci-dessous l'adresse Internet "+TXT+" que vous souhaitez insérer."
    var LINK = prompt(MSG);
	
	if(TYPE != "QR" && !isValidURL(LINK) && LINK != "")
	{
		alert("Cette URL ("+LINK+") n'est pas valide.\nMerci de réessayer...");
	}
	else
	{
        if(TYPE == "QR" && LINK.length < 500){ SRC = SRC + encodeURIComponent(LINK); }
        else if(TYPE == "QR" && LINK.length < 500){ alert("Cette URL ("+LINK+") est trop longue.\nMerci de réessayer..."); }
        else{ SRC = LINK; }
		
		//Generate Image/QR Code HTML
        var IMG = '<img class="'+CLASS+'" src="../../scripts/js/'+SRC+'">';
		if(TYPE == "QR"){ IMG+= '<span class="link">'+LINK+'</span>' }
		
		//Add Image/QR Code to the rest of the MESSAGE
		var MESSAGE = document.getElementById('public-html');
			MESSAGE.value+= IMG;
        
        Preview(MESSAGE);
	}
}

//Chapter -------->
function Transition(what){
	var ChapterTitle = document.getElementById('chapter-html').value;
	const DATA = {type:"ct", title:ChapterTitle};
	Update(DATA);

	what.disabled = true;
	setTimeout(function () { what.disabled = false; }, 10000);
}


function isValidURL(URL)
{
	if(URL!=="")
	{  
		test = document.createElement('input');
		test.setAttribute('type', 'url');
		test.value = URL;
		return test.validity.valid;
	}
	else{ return false }
}

function ValidURL(string)
{
	let url;
	try { url = new URL(string);}
	catch (_) { return false; }
	return url.protocol === "http:" || url.protocol === "https:";
}