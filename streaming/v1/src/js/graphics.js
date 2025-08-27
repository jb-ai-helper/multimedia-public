// JavaScript Document

//Open Broadcast Channels
var si = new BroadcastChannel('obs-stream-info-channel');
var lt = new BroadcastChannel('obs-lower-third-channel');
var sb = new BroadcastChannel('obs-scrolling-banner-channel');
var ct = new BroadcastChannel('obs-chapter-transition-channel');


function FileExists(URL){
	if(URL){
		var req = new XMLHttpRequest();
		req.open('HEAD', URL, false);
		req.send();
		return req.status==200;
	}
	else { return false; }
}

//Stream Info
si.onmessage = function (ev)
	{
	//Receive Data
	received_data=ev.data.split("|");

    //Add Style CSS File
    var STYLE = received_data[3];
    var Old_CSS = document.getElementById("AddedStyleSheet");
    
    if(STYLE != "" && STYLE != "ENPJJ"){
        var CSS = document.createElement("link");
            CSS.setAttribute("id","AddedStyleSheet");
            CSS.setAttribute("rel","stylesheet");
            CSS.setAttribute("type", "text/css");
            CSS.setAttribute("href","../styles/"+STYLE+".css");
        //Remove previously added style sheet
        if(Old_CSS){ document.head.removeChild(Old_CSS); }
        //Add new style sheet
        document.head.appendChild(CSS);
        }
    else{
        //Remove previously added style sheet
        if(Old_CSS){ document.head.removeChild(Old_CSS); }
        }
    
    var VIDEO = document.getElementById('video');
    var SOURCE = VIDEO.getElementsByTagName('source')[0];
    var URL = "../styles/src/vid/bkg-"+STYLE+".webm";

    if(FileExists(URL)){
        SOURCE.setAttribute('src', URL);
        VIDEO.load();
        }
    else{
        SOURCE.setAttribute('src', "../src/vid/bkg-enpjj.webm");
        VIDEO.load();
        }

    }

//Lower Thrid
lt.onmessage = function (ev)
	{
	//Receive Data
	received_data=ev.data.split("|");
		
	//Sort Data
	var NAME = received_data[0];
	var FUNCTION = received_data[1];
	var TRANSLATION = received_data[2];
	var STATE = received_data[3];
	
	//Adjust Function Width
	var hyphenation = FUNCTION.indexOf(' ',FUNCTION.length/2);	
	if(FUNCTION.length>60 && FUNCTION.match(/<br\s*\/?>/ig) == null) { FUNCTION = FUNCTION.slice(0, hyphenation) + "<br \>" + FUNCTION.slice(hyphenation+1); }
	
	//Adjust Translation Width
	var hyphenation = TRANSLATION.indexOf(' ',TRANSLATION.length/2);	
	if(TRANSLATION.length>60 && TRANSLATION.match(/<br\s*\/?>/ig) == null) { TRANSLATION = TRANSLATION.slice(0, hyphenation) + "<br \>" + TRANSLATION.slice(hyphenation+1); }
	
	//Insert New Text
	document.getElementById("name").innerHTML = NAME;
	document.getElementById("function").innerHTML = FUNCTION;
	document.getElementById("translation").innerHTML = TRANSLATION;
	//Change State
	document.getElementById("LowerThird").className= STATE;
	console.log ('Name: ' + received_data[0] + ', Function: ' + received_data[1] + + ', Translation: ' + received_data[2] + ', Animation: ' + received_data[3]);
	}

//Chapter Transition
ct.onmessage = function (ev){
	//Receive Data
	var TITLE=ev.data;
	
	//Get mode
	var mode = document.getElementById("mode").value;
	
	if(!window.PartAnim){
		document.getElementById("Title").innerHTML = TITLE; //Insert New Title
		
		//Only if mode not "transition" (static) or "demo"
		if(mode != "transition" && mode != "demo"){
		document.getElementById("ChapterTransition").className = 'ON'; //Launch animation
		document.getElementById('transition').play();//Play audio
		console.log ('Part Animation: ON');
		window.PartAnim = window.setTimeout(function () {
				document.getElementById("ChapterTransition").className = 'OFF';
				clearTimeout(window.PartAnim); delete window.PartAnim;
				console.log ('Part Animation: OFF');
			}, 10000);// Ici la durée de la transition
		}
	}
	else{ console.log ('Error: Animation already in progress...'); }
}

//Scrolling Banner
sb.onmessage = function (ev)
	{
	received_data=ev.data.split("|");

	//Receieve Variables
	var MESSAGE = received_data[0];
	var CLASS = received_data[1];
	var STATE = received_data[2];

	//Insert New Text
	document.getElementById("Banner").innerHTML = MESSAGE;
	//Calculate and set animation duration (15s min)
	DURATION = Math.round(MESSAGE.length/10+5); if(DURATION<15) DURATION = 15;
	document.getElementById("Banner").style.animationDuration = DURATION + 's';
	//Change State
	document.getElementById("ScrollingBanner").className= STATE;
	//Add New Class
	document.getElementById("ScrollingBanner").classList.add(CLASS);
	console.log ('Message: ' + received_data[0] + ', Class: ' + received_data[1] + ', Animation: ' + received_data[2]);
	}