// JavaScript Document

//Get Stream Key
var URL = new URL(window.location.href);
var STR = URL.searchParams.get("str");

//Open Broadcast Channel
var lsc = new BroadcastChannel(STR+'-local-style-channel');

//Listen Broadcast Channel
lsc.onmessage = function (event)
{
	var DATA = event.data;
	console.log("Command received: " + JSON.stringify(DATA));

	if(STR == DATA['str'])
	{
		//Get Style
		STYLE = DATA['style'];

        //Add Style CSS File
        var Old_CSS = document.getElementById("AddedStyleSheet");

        //Store Style on page
        document.getElementById('style').value = STYLE;

        if(STYLE != "" && STYLE != "enpjj"){
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

        //Change Video Background
		var Background = document.getElementById('Background');
		var Background_source = Background.getElementsByTagName('source')[0];
		var bkg = "../styles/vid/bkg-"+STYLE+".webm";
		Background_source.setAttribute('src', bkg);
		Background.load();

		//Populate Console
		console.log ("Style Updated!");
	}
};