// JavaScript Document

//Open Broadcast Channels
var si = new BroadcastChannel('obs-stream-info-channel');

//Stream Data
si.onmessage = function (ev)
	{
	//Receive Data
	received_data=ev.data.split("|");
		
	//Sort Data
	var TITLE = received_data[0];
	var MESSAGE = received_data[1];
	var DATE = received_data[2];
    var STYLE = received_data[3];
	
    var Old_CSS = document.getElementById("AddedStyleSheet");

    //Insert New Text
	document.getElementById("Title").innerHTML = TITLE;
	document.getElementById("SubTitle").innerHTML = MESSAGE;
	document.getElementById("Date").innerHTML = DATE;

    //Add Style CSS File
    if(STYLE != "" && STYLE != "ENPJJ"){
        var CSS = document.createElement("link");
            CSS.setAttribute("id","AddedStyleSheet");
            CSS.setAttribute("rel","stylesheet");
            CSS.setAttribute("type", "text/css");
            CSS.setAttribute("href","../styles/"+STYLE);
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
    
    //Populate Console
	console.log ('Title: ' + received_data[0] + ', Message: ' + received_data[1] + ', Date: ' + received_data[2] + ', Style Sheet: ' + received_data[3]);
}