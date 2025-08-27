// JavaScript Document

//Open Broadcast Channels
var si = new BroadcastChannel('obs-stream-info-channel');
var gp = new BroadcastChannel('obs-global-parmeter-channel');

//Global Parameters
gp.onmessage = function (ev)
	{
	//Receive Data
	received_data=ev.data.split("|");
		
	//Sort Data
	var ID = received_data[0];
	var STATE = received_data[1];
	
	//Turn Element ON/OFF
	var ELEMENT = document.getElementById(ID);
	if(STATE == "OFF"){ELEMENT.style.opacity = 0;}
	else{ELEMENT.style.opacity = 1;}
    
    //Turn Partenaire ON/OF with TopBanner
    if(ID == "TopBanner"){
        ELEMENT = document.getElementById("Partenaire");
        if(STATE == "OFF"){ELEMENT.style.opacity = 0;}
        else{ELEMENT.style.opacity = 1;}
    }
    
    //Populate Console
	console.log (received_data[0] + ': ' + received_data[1]);
	}

//Stream Info
si.onmessage = function (ev)
	{
	//Receive Data
	received_data=ev.data.split("|");
		
	//Sort Data
	var TITLE = received_data[0];
		TITLE = TITLE.replace(/<br>/gi, " ");
	var SUBTITLE = received_data[1];
		SUBTITLE = SUBTITLE.replace(/<br>/gi, " ");
	
	//Insert New Text
	if(document.getElementById("Title")){
        document.getElementById("Title").innerHTML = TITLE
        document.getElementById("SubTitle").innerHTML = SUBTITLE
        console.log ('Banner: ' + received_data[0] + ' | ' + received_data[1]);
    }
    else console.log ('No available Banner ("welcome" page).');
    
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
        if(Old_CSS){ document.head.removeChild(Old_CSS);
        }
    }
}
