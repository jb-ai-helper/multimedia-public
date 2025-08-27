// JavaScript Document

//Get Stream Key
var URL = new URL(window.location.href);
var STR = URL.searchParams.get("str");
var EID = URL.searchParams.get("eid");//Element ID
var APP = window.location.pathname;

//Get Mode
if(URL.searchParams.get("mode") != ""){ var APP_Mode = URL.searchParams.get("mode"); }
else{ var APP_Mode = ""; }

//Ask for Stream Key if opened seperately & keep mode
if(STR == 'preview' && window == window.top){
	var new_stream = prompt("Préciser le lieu du stream :", STR);
    	
	if(APP_Mode != null){ APP_Mode = "mode="+APP_Mode+"&"; }//Account for subsequent variables chaine
	if(new_stream != 'preview'){ window.location.href = "dsk.php?"+APP_Mode+"str="+new_stream; }
}

//Select Communication methode (Broadcast Channel or SSE)
if(APP_Mode == "demo" || APP_Mode == "miniature" || APP_Mode == "transition"){
    //Open Broadcast Channels (for preview)
    var psi = new BroadcastChannel(STR+'-stream-info-channel');
    var plt = new BroadcastChannel(STR+'-lower-third-channel');
    var psb = new BroadcastChannel(STR+'-scrolling-banner-channel');
    var pct = new BroadcastChannel(STR+'-chapter-transition-channel');

    //Listen to Broadcast Channels (for preview)
    psi.onmessage = plt.onmessage = psb.onmessage = pct.onmessage = function(event) { var DATA = JSON.parse(event.data); DataHandler(DATA); };
}
else{
    //Connect & Listen to SSE Connection
    var source = new EventSource('../scripts/php/updates.php?str='+STR);
    source.onmessage = function(event) { var DATA = JSON.parse(event.data); DataHandler(DATA); };
}

//Open Broadcast Channels (for dependent apps)
var lsc = new BroadcastChannel(STR+'-local-style-channel');
var lmc = new BroadcastChannel(STR+'-local-messages-channel');

//Open Broadcast Channel for CSS Editor
var cec = new BroadcastChannel('css-editor-channel');
cec.onmessage = function (ev){ AddCSS(ev.data); }

//Add Additional CSS sent by Editor
function AddCSS(DATA) {
	var AdditionalCSS = document.getElementById('AdditionalCSS');
	var Background = document.getElementById('Background');
	var Background_source = Background.getElementsByTagName('source')[0];


	if (!AdditionalCSS) {
		AdditionalCSS = document.createElement('style');
		AdditionalCSS.setAttribute('id', "AdditionalCSS");
		document.head.appendChild(AdditionalCSS);
	}
    //
    var url_adjusted_css = DATA.css.replace(/url\(["']?img\//g, 'url("/streaming/v2/styles/img/');
	AdditionalCSS.textContent = url_adjusted_css;
    document.getElementById('style').value = DATA.code;
    
    //Update Background Video Loop
    var bkg = "../styles/vid/bkg-"+DATA.code+".webm";
    FileExists(bkg).then(exists => {
        if (!exists) { bkg = "../styles/vid/bkg-enpjj.webm"; }
        Background_source.src = bkg;
        Background.load();
    });
}

//Message Global Variables
var ClearNote = new Array();
var KillNote = new Array();
var LastMessage;
var lastModified;

//Counter Global Variables
var Time = 0;
const FrameRate = 25;
const Interval = 1000/FrameRate;
var CountUp = null;

function playImmediately() {
    Credits.play();
    //Make sure it doesn't loop
    Credits.removeEventListener('canplaythrough', playImmediately);
}

function playAfterDelay() {
    setTimeout(() => {
        Credits.play();
    }, 1000);
    //Make sure it doesn't loop
    Credits.removeEventListener('canplaythrough', playAfterDelay);
}

function ResetCounter(TIME){
    Time = TIME*FrameRate;
}

function SetCounter(TARGET, START){
    //Parameters
    if(START) Time = parseInt(START)
    
    //Show Counter
    var Counter = document.getElementById("Counter");
    Counter.removeAttribute("style");
    
    //Clear Interval
    clearInterval(CountUp);

    CountUp = setInterval(function () {
    var Hours = Math.trunc(Time/60/60/FrameRate);
    var Minutes = Math.trunc(Time/60/FrameRate)-(Hours*60);
    var Seconds = Math.trunc(Time/FrameRate)-(Minutes*60)-(Hours*60*60);
    var Frames = Time-(Seconds*FrameRate)-(Minutes*60*FrameRate)-(Hours*60*60*FrameRate);

    if(Hours<10){ Hours = "0"+Hours; }
    if(Minutes<10){ Minutes = "0"+Minutes; }
    if(Seconds<10){ Seconds = "0"+Seconds; }
    if(Frames<10){ Frames = "0"+Frames; }

    Counter.innerHTML = Hours+":"+Minutes+":"+Seconds+":"+Frames;
    Time ++
    }, Interval);
}

//Recieved Data Handler Function
function DataHandler(DATA){
	//Log Recieved Message
    console.log("Command received: " + event.data);
	
	//Create Last_DATA if it doesn't exist
	if(typeof last_DATA === 'undefined'){
        window.last_DATA = "";
    }
	
	//Handle new DATA
	if(JSON.stringify(DATA) != JSON.stringify(last_DATA)){
		//Update Last_DATA
		last_DATA = DATA;
		//Global Parameters
		if(DATA['type'] == "gp"){ GlobalParameters(DATA); }
		//Show Runner
		if(DATA['type'] == "sr"){ ShowRunner(DATA['cmd']); }
		//Stream Info
		if(DATA['type'] == "si"){ StreamInfo(DATA); }
		//Stream Info
		if(DATA['type'] == "lt"){ LowerThrid(DATA); }
		//Chapter Transition
		if(DATA['type'] == "ct"){ ChapterTransition(DATA); }
		//Scrolling Banner
		if(DATA['type'] == "sb"){ ScrollingBanner(DATA); }
		//Count Down
		if(DATA['type'] == "cd"){ CountDown(DATA['delai']); }
		//Show Note
		if(DATA['type'] == "ms"){ ShowMessage(DATA); }
		//Reset Counter
		if(DATA['type'] == "cnt"){ ResetCounter(DATA['start']); }
	}
}

//Global Parameters ---------->
function GlobalParameters(DATA)
{
	//Sort Data
	var ID = DATA['id'];
	var STATUS = DATA['status'];
	
	//Turn Element ON/OFF
	var ELEMENT = document.getElementById(ID);
	if(STATUS == "OFF"){ if(!ELEMENT.classList.contains("OFF")){ ELEMENT.classList.add("OFF"); } }
	else{ if(ELEMENT.classList.contains("OFF")){ ELEMENT.classList.remove("OFF"); } }
    
    //Populate Console
	console.log (ID + ': ' + STATUS);
}

//Show Runner ---------->
function ShowRunner(CMD)
{
	//Set Common Variables
	var style = document.getElementById('style').value;
	var Background = document.getElementById('Background');
	var Background_source = Background.getElementsByTagName('source')[0];
	var Introduction = document.getElementById('Introduction');
	var Pause = document.getElementById('Pause');
	var Credits = document.getElementById('Credits');
	var Credits_source = Credits.getElementsByTagName('source')[0];
	var Partenaire = document.getElementById('Partenaire');

	if(CMD == "welcome")
	{
		//Clean up from other states
		if(!Pause.classList.contains("OFF")){ Pause.classList.add("OFF"); }
		if(Credits.classList.contains("CUT")){ Credits.classList.remove("CUT"); }
		if(!Credits.classList.contains("OFF")){ Credits.classList.add("OFF"); }
		if(Partenaire.classList.contains("OFF")){ Partenaire.classList.remove("OFF"); }

		//Play Waiting Music
		if(!document.getElementById('Music')){ InitializePlayer(); }

		//Set Background Video Loop
		var bkg = "../styles/vid/bkg-"+style+".webm";
        FileExists(bkg).then(exists => {
            if (!exists) { bkg = "../styles/vid/bkg-enpjj.webm"; }
            Background_source.src = bkg;
            Background.load();
        });
		
		//FadeIn Background
		if(Background.classList.contains("OFF")){ Background.classList.remove("OFF"); }

		//FadeIn Intro Texts
		if(Introduction.classList.contains("OFF")){ Introduction.classList.remove("OFF"); }
	}
	else if(CMD == "intro")
	{
		//FadeOut Music
		if(document.getElementById('Music')){ FadeOutPlayer(); }

		//Load Intro
		var crd = "../styles/vid/in-"+style+".webm";
        FileExists(crd).then(exists => {
            if (!exists) { crd = "../styles/vid/in-enpjj.webm"; }
            Credits_source.src = crd;
            Credits.load();
        });

		//FadeOut Intro Texts & Partenaire
		if(!Introduction.classList.contains("OFF")){ Introduction.classList.add("OFF"); }
		if(!Partenaire.classList.contains("OFF")){ Partenaire.classList.add("OFF"); }

		//CutIn Intro
		if(!Credits.classList.contains("CUT")){ Credits.classList.add("CUT"); }
		if(Credits.classList.contains("OFF")){ Credits.classList.remove("OFF"); }
		
		//Play Intro (t+1s)
        Credits.addEventListener('canplaythrough', playAfterDelay);

		//FadeOut Background & clean up other states (t+5s)
		setTimeout(function(){
			//Clean other states
			if(!Introduction.classList.contains("OFF")){ Introduction.classList.add("OFF");}
			if(!Background.classList.contains("OFF")){ Background.classList.add("OFF"); }
			if(!Pause.classList.contains("OFF")){ Pause.classList.add("OFF"); }
			if(Partenaire.classList.contains("OFF")){ Partenaire.classList.remove("OFF"); }
		}, 3500);
	}
	else if(CMD == "stream")
	{
		//Clean other states
		if(document.getElementById('Music')){ FadeOutPlayer(); }
		if(!Introduction.classList.contains("OFF")){ Introduction.classList.add("OFF");}
		if(!Background.classList.contains("OFF")){ Background.classList.add("OFF"); }
		if(!Pause.classList.contains("OFF")){ Pause.classList.add("OFF"); }
		if(Credits.classList.contains("CUT")){ Credits.classList.remove("CUT"); }
		if(!Credits.classList.contains("OFF")){ Credits.classList.add("OFF"); }
	}
	else if(CMD == "pause")
	{
		//Clean up from other states
		if(!document.getElementById('Music')){ InitializePlayer(); }
		if(!Introduction.classList.contains("OFF")){ Introduction.classList.add("OFF") };
		if(!Background.classList.contains("OFF")){ Background.classList.add("OFF") };
		if(Credits.classList.contains("CUT")){ Credits.classList.remove("CUT"); }
		if(!Credits.classList.contains("OFF")){ Credits.classList.add("OFF"); }

		//FadeIn Pause
		if(Pause.classList.contains("OFF")){ Pause.classList.remove("OFF"); }
	}
	else if(CMD == "outro")
	{
		//Load Outro
		var crd = "../styles/vid/out-"+style+".webm";
        FileExists(crd).then(exists => {
            if (!exists) { crd = "../styles/vid/out-enpjj.webm"; }
            Credits_source.src = crd;
            Credits.load();
        });
		
		//FadeOut Music
		if(document.getElementById('Music')){ FadeOutPlayer(); }
		
		//CutIn Outro & Play
		if(!Credits.classList.contains("CUT")){ Credits.classList.add("CUT"); }
		if(Credits.classList.contains("OFF")){ Credits.classList.remove("OFF"); }
        
        //Play video once loaded
        Credits.addEventListener('canplaythrough', playImmediately);

		//Clean up from other states (t+1s)
		setTimeout(function(){
			if(!Introduction.classList.contains("OFF")){ Introduction.classList.add("OFF");}
			if(!Background.classList.contains("OFF")){ Background.classList.add("OFF") };
			if(!Pause.classList.contains("OFF")){ Pause.classList.add("OFF"); }
		},1000);
	}
}

//Stream Info ---------->
function StreamInfo(DATA)
{
	//Sort Data
	var TITLE = DATA['title'];
	var SUBTITLE = DATA['subtitle'];
	var DATE = DATA['date'];
    var STYLE = DATA['style'];

    //Get Old CSS Style CSS
    var Old_CSS = document.getElementById("AddedStyleSheet");
    
    //Get Dinamic Style Editor JS
    var Style_JS = document.getElementById("DinamicStyleEditor");
    
    if(STYLE != "" && STYLE != "ENPJJ"){
        var CSS = document.createElement("link");
            CSS.setAttribute("id","AddedStyleSheet");
            CSS.setAttribute("rel","stylesheet");
            CSS.setAttribute("type", "text/css");
            CSS.setAttribute("href","/streaming/v2/styles/"+STYLE+".css");
        
        //Account for CSS Editor
        if(STYLE == "css-editor"){
            CSS.setAttribute("href","/streaming/v2/scripts/css/editor.css");
            STYLE = document.getElementById('style').value;
            
            // Add Dynamic CSS rerival
            var JS = document.createElement("script");
                JS.setAttribute("id", "DinamicStyleEditor")
                JS.setAttribute("src", "/streaming/v2/scripts/js/dse.js")
            document.head.appendChild(JS);
        }
        
        //Remove previously added style sheet if any
        if(Old_CSS){ document.head.removeChild(Old_CSS); }
        //Add new style sheet
        document.head.appendChild(CSS);
        }
    else{
        //Remove previously added style sheet and Dinamic Style Editor JS
        if(Old_CSS){ document.head.removeChild(Old_CSS); }
        if(Style_JS){ document.head.removeChild(Style_JS); }
        }
	
	//Store Style on page
	document.getElementById('style').value = STYLE;
    
	//Change Video Background
	var Background = document.getElementById('Background');
	var Background_source = Background.getElementsByTagName('source')[0];
	var bkg = "../styles/vid/bkg-"+STYLE+".webm";
    FileExists(bkg).then(exists => {
        if (!exists) { bkg = "../styles/vid/bkg-enpjj.webm"; }
        Background_source.src = bkg;
        Background.load();
    });

	//Insert New Stream Info
	document.getElementById("Title_Introduction").innerHTML = TITLE;
	document.getElementById("Title_Copyright").innerHTML = TITLE.replace(/<br>/gi, " ");
	document.getElementById("SubTitle_Introduction").innerHTML = SUBTITLE;
	document.getElementById("SubTitle_Copyright").innerHTML = SUBTITLE.replace(/<br>/gi, " ");
	document.getElementById("Date").innerHTML = DATE;
    
	//Populate data-line value to help CSS Styling
	if(TITLE.match(/<br\s*\/?>/ig) != null){ document.getElementById("Title_Introduction").dataset.line = TITLE.match(/<br\s*\/?>/ig).length+1; }
	else{ document.getElementById("Title_Introduction").dataset.line = 1; }
	
	//Send relevant info to update dependent apps (bkg)
	const CAST = {str:STR, style:STYLE};
	lsc.postMessage(CAST);
	
    //Populate Console
	console.log ("Stream Info Updated!");
}

//Lower Thrid ---------->

//Store Previous LowerThird
var previous_NAME;
var previous_FUNCTION;
var previous_TRANSLATION;

function LowerThrid(DATA)
{
	//Common variables
	var LowerThird = document.getElementById("LowerThird");
	var Name = document.getElementById("Name");
	var Function = document.getElementById("Function");
	var Translation = document.getElementById("Translation");
		
	//Sort Data
	var STATE = DATA['state'] || "OFF";
	var NAME = DATA['name'] || "";
	var FUNCTION = DATA['function'] || "";
	var TRANSLATION = DATA['translation'] || "";
	
	if(NAME == "")
	{
		NAME = previous_NAME;
		FUNCTION = previous_FUNCTION;
		TRANSLATION = previous_TRANSLATION;
	}
	else
	{
		previous_NAME = NAME;
		previous_FUNCTION = FUNCTION;
		previous_TRANSLATION = TRANSLATION;
	}
	
	//Adjust Function Width
	if(FUNCTION.length>60 && FUNCTION.match(/<br\s*\/?>/ig) == null){
        var hyphenation = FUNCTION.indexOf(' ',FUNCTION.length/2);	
        FUNCTION = FUNCTION.slice(0, hyphenation) + "<br \>" + FUNCTION.slice(hyphenation+1);
    }
	
	//Adjust Translation Width
	if(TRANSLATION.length>60 && TRANSLATION.match(/<br\s*\/?>/ig) == null){
        var hyphenation = TRANSLATION.indexOf(' ',TRANSLATION.length/2);	
        TRANSLATION = TRANSLATION.slice(0, hyphenation) + "<br \>" + TRANSLATION.slice(hyphenation+1);
    }
	
	//Insert New Text
	Name.innerHTML = NAME;
	Function.innerHTML = FUNCTION;
	Translation.innerHTML = TRANSLATION;
	//Change State
	LowerThird.className = STATE;
	console.log ("Lower Thrid Updated!");
}

//ChapterTransition ---------->
function ChapterTransition(DATA)
{
	//Set Common Variables
    var ID = DATA['id'] | "";
	var STATE = DATA['state'];
	var TITLE = DATA['title'];
	var style = document.getElementById('style').value;
	var Cover = document.getElementById('Cover');
	var Background = document.getElementById('Background');
	var Background_source = Background.getElementsByTagName('source')[0];
	var Chapter = document.getElementById('Chapter');
	var Transition = document.getElementById('Transition');
	var Title_Chapter = document.getElementById('Title_Chapter');
	
	//Get mode
	var mode = document.getElementById("mode").value;

	//Only if mode not defined
	if(mode == "")
	{
        //Set Transition Type
        Chapter.classList.add('CUT')
        Background.classList.add('CUT')
        
        //Prepare Background
        var bkg = "../styles/vid/bkg-"+style+".webm";
        FileExists(bkg).then(exists => {
            if (!exists) { bkg = "../styles/vid/bkg-enpjj.webm"; }
            //Compare bkg à ce qui est déjà chargé et change si différent
            if(Background_source.src.split('/').pop() != bkg.split('/').pop()){ 
                Background_source.src = bkg;
                Background.load();
            }
        });

		if(STATE == "ON")
		{
			//Insert TITLE
			Title_Chapter.innerHTML = TITLE;
			
            //Launch Transition
            Cover.classList.add('ON');
			
            //Play audio
            Transition.play();
            
            //Turn ON everything with 0.5s delay
            setTimeout(function(){
                Chapter.classList.remove('OFF')
                Background.classList.remove('OFF')
            },500);            
		}
		else if(STATE == "OFF")
		{
            //Launch Transition
            Cover.classList.add('ON');
			
            //Play audio
            Transition.play();
            
            //Turn OFF everything with 0.5s delay
            setTimeout(function(){
                Chapter.classList.add('OFF')
                Background.classList.add('OFF')
            },500);

            //Reset Transition Type
            setTimeout(function() {
                Chapter.classList.remove('CUT');;
                Background.classList.remove('CUT');
                       }, 1000)
		}
        //Reset Cover
        setTimeout(function() { Cover.classList.remove('ON'); }, 1000)
	}
	else if(EID == ID){//Only uptade Element ID and ID to update match
        Title_Chapter.innerHTML = TITLE;//Just insert TITLE if mode "transition" (static) or "demo"
    }

	console.log ("Chapter Transition Updated!");
}

//Scrolling Banner ---------->

//Store Previous Banner
var previous_MESSAGE;
//var previous_CLASS;

function ScrollingBanner(DATA)
	{
	//Receieve Variables
	var MESSAGE = DATA['message'] || "";
	//var CLASS = DATA['class'];
	var STATE = DATA['state'];
	
	if(MESSAGE == "")
	{
		MESSAGE = previous_MESSAGE;
		//CLASS = previous_FUNCTION;
	}
	else
	{
		previous_MESSAGE = MESSAGE;
		//previous_CLASS = CLASS;
	}

	//Change State
	document.getElementById("ScrollingBanner").className= STATE;
	
		if(STATE == "ON")
		{
			//Add Class
			//document.getElementById("ScrollingBanner").classList.add(CLASS);
			//Insert New Text
			document.getElementById("Banner").innerHTML = MESSAGE;
			//Calculate and set animation duration (15s min)
			DURATION = Math.round(MESSAGE.length/10+5); if(DURATION<15) DURATION = 15;
			document.getElementById("Banner").style.animationDuration = DURATION + 's';
			//Start Animation from beginning
			document.getElementById("Banner").style.animationName = "ScrollRighToLeft";
		}
		else if(STATE == "OFF"){ setTimeout(function(){ document.getElementById("Banner").style.animationName = ""; }, 1000); }//Cancel animation after FadeOut
	console.log ("Scrolling Banner Updated!");
	}

//Pause Count Down ---------->
var MyCountDown;

function CountDown(DELAI)
	{
	received_data=DELAI.split(":");
		
	//Receieve Variables
	var HOURS = Number(received_data[0]);
	var MINUTES = Number(received_data[1]);
	if(received_data.length == 2){ var SECONDS = 0; }
	else{ var SECONDS = Number(received_data[2]); }
	
	var StartTime = Date.now();
	var DelayedTime = ((((HOURS*60)+MINUTES)*60)+SECONDS)*1000;
	var EndTime = StartTime+DelayedTime;
	
	clearInterval(MyCountDown)
	MyCountDown = setInterval(StartTimer, 1000, EndTime);
	console.log ('Countdown (initial) : ' + received_data[0] + 'h' + received_data[1] + 'm' + received_data[2] + 's');
	}

function StartTimer(EndTime)
	{
	var CurrentTime = Date.now();
	var TimeRemaining = EndTime-CurrentTime;
	var CountDownTarget = document.getElementById("countdown");

	//Set Timer Variables
	var DELAI = new Date(); DELAI.setTime(EndTime);		
	var TIME = new Date(); TIME.setTime(CurrentTime);
	HoursRemaining = DELAI.getHours()-TIME.getHours();
	MinutesRemaining = DELAI.getMinutes()-TIME.getMinutes();
	SecondsRemaining = DELAI.getSeconds()-TIME.getSeconds();
	if(SecondsRemaining < 0){ MinutesRemaining -=1; SecondsRemaining +=60; }
	if(MinutesRemaining < 0){ HoursRemaining -=1; MinutesRemaining +=60; }
	if(HoursRemaining < 0){ HoursRemaining +=24; }
	
	//Control for leading 0 in timer
	if(HoursRemaining<10){ TimerHours = "0"+HoursRemaining; } else{ TimerHours = HoursRemaining; }
	if(MinutesRemaining<10){ TimerMinutes = "0"+MinutesRemaining; } else{ TimerMinutes = MinutesRemaining; }
	if(SecondsRemaining<10){ TimerSeconds = "0"+SecondsRemaining; } else{ TimerSeconds = SecondsRemaining; }
	//Set Timer display for Consol
	var ConsolTimer = TimerHours + 'h' + TimerMinutes + 'm' + TimerSeconds + 's';
		
	if(TimeRemaining > 600000)//More than 10 min remains
		{
		DelaiMinutes = DELAI.getMinutes()
		if(DelaiMinutes<10){ DelaiMinutes = "0"+DelaiMinutes; }
		
		//Account for time of day
		if(DELAI.getHours()>=TIME.getHours()) { var DAY = [" ce", " aujourd'hui", " cette", " ce"]; }
		else { var DAY = [" demain", " demain", " demain", " demain"] }
			
		EndTimeText = DELAI.getHours() + "h" + DelaiMinutes;
		if(DELAI.getHours()<12){ TimerText = DAY[0] + " matin à "; }
		else if(DELAI.getHours()==12){ TimerText = DAY[1] + " à "; }
		else if(DELAI.getHours()>12 && DELAI.getHours()<17){ TimerText = DAY[2] + " après-midi à "; }
		else if(DELAI.getHours()>=17 && DELAI.getHours()<24){ TimerText = DAY[3] + " soir à "; }
		
		CountDownTarget.innerHTML = TimerText + EndTimeText + ".";
		//if(CountDownTarget.classList.contains("waiting")) { CountDownTarget.classList.remove("waiting"); }
		console.log ('Countdown (clock) : ' + ConsolTimer);
		}
	else if(TimeRemaining <= 600000 && TimeRemaining > 60000)//Between 10 and 1 min remains
		{
		// Control for plurals
		if(MinutesRemaining>1){ MinPluri = "s"; } else{ MinPluri = ""; }
		if(SecondsRemaining>1){ SecPluri = "s"; } else{ SecPluri = ""; }
		
		CountDownTarget.innerHTML = " dans " + MinutesRemaining + " minute" + MinPluri + " et " + SecondsRemaining + " seconde" + SecPluri + ".";
		//if(CountDownTarget.classList.contains("waiting")) { CountDownTarget.classList.remove("waiting"); }
		console.log ('Countdown (timer) : ' + ConsolTimer);
		}
	else
		{
		CountDownTarget.innerHTML = " dans quelques instants...";
		//if(!CountDownTarget.classList.contains("waiting")) { CountDownTarget.classList.add("waiting"); }
		console.log ('Countdown (transparent) : ' + ConsolTimer);
		}
	//Stop Timer
	if(TimeRemaining<=0) { clearInterval(MyCountDown); }
	}

//Messages ---------->

//Listen to PREVIEW
lmc.onmessage = function (ev){ var DATA=ev.data; ShowMessage(DATA); }

//Show Messages & Previews
function ShowMessage(DATA){    
	//Seperate DATA
    var ID = DATA['id'];
    var STAMP = DATA['stamp'];
    var CONTENT = DATA['content'];

    //IF PREVIEW
    if(ID == 'preview'){
        //Adjust ID function of STAMP
        ID = STAMP+'Preview';
        //Update PREVIEW if exists
        if(document.getElementById(ID))
		{
            if(CONTENT === ""){
                if(!window.KillPreview) { DeleteMessage(ID); }
                else{ console.log("Waiting for previous preview to end.") }
            }
            else{
                document.getElementById(ID).innerHTML = CONTENT;
                console.log (STAMP + ' Preview updated: ' + CONTENT);
            }
        }
        //Create PREVIEW if doesn't exist
        else if(CONTENT != "")
		{
            //Determin CLASS function of STAMP
            if(STAMP == "Public"){ var CLASS = (window.NoteType == "Right") ? "Left" : "Right"; }
            else{ CLASS = STAMP; }
            //Preview Creation
            CreateTextBubble(ID, STAMP, CLASS, CONTENT);
            console.log (STAMP +' Preview added: ' + CONTENT);
        }
    }
    //IF Distant CLEAR ALL command (no ID)
    else if(STAMP == 'Delete'){ DeleteMessage(ID); }
    //IF Distant CLEAR ALL command (no ID)
    else if(ID == '' && STAMP == '' && CONTENT == ''){ ClearMessages(); }
    //IF Regular Note
    else{
        //Determine LIFESPAM
        if(STAMP == 'Public') { LIFESPAN = 28800000; }//8h (toujours) 
        else if(STAMP == 'Interne') { LIFESPAN = 28800000; }//8h (toujours)
        //Allow for custom LIFESPAN
        else{
            var content_timeout = STAMP.split(":");
            var HOURS = Number(content_timeout[0]);
            var MINUTES = Number(content_timeout[1]);

            if(content_timeout.length == 2){ SECONDS = 0; }
            else{ var SECONDS = Number(content_timeout[2]); }

            var LIFESPAN = ((((HOURS*60)+MINUTES)*60)+SECONDS)*1000;
        }
        //Create New Message (Check for existing ID first)
        if(!document.getElementById(ID)){
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
            if(document.getElementById('PublicPreview')){ DeleteMessage('PublicPreview'); }
            if(document.getElementById('InternePreview')){ DeleteMessage('InternePreview'); }
            //Only Log INTERN messages if MODE = LIGHT
            var MODE = document.getElementById('mode').value;
            if(STAMP == 'Interne' && MODE != 'light'){ console.log ('Internal Message: ' + CONTENT); }
            //Message Creation
            setTimeout(function() {
                CreateTextBubble(ID, STAMP, CLASS, CONTENT);
            }, 1000);
            if(MODE != 'light' && STAMP == "Public") { window.setTimeout(function () { document.getElementById('Notification').play();  }, 1000); }
            ClearNote[ClearNote.length] = window.setTimeout(function () { Transition(document.getElementById(ID),'OUT'); clearTimeout(ClearNote[ClearNote.length]);  }, LIFESPAN);
            KillNote[KillNote.length] = window.setTimeout(function () { document.getElementById(ID).remove(); clearTimeout(KillNote[KillNote.length]);  }, LIFESPAN+1000);
            console.log ('Message Added!');
        }
    }
}

function CreateTextBubble(ID, STAMP, CLASS, CONTENT){
	var TextBubble = document.createElement('div');
        TextBubble.style.opacity = 0;
        TextBubble.style.position = "absolute";
        TextBubble.setAttribute('class', "TextBubble " + CLASS);
        TextBubble.setAttribute('id',ID);
        TextBubble.setAttribute('data-stamp',STAMP);
        TextBubble.setAttribute('onclick',"SelectMessage('"+ID+"')");
        TextBubble.innerHTML = CONTENT;
	document.getElementById("Messages").appendChild(TextBubble);
    
    waitForContentToLoad(TextBubble, () => {
        Transition(TextBubble, 'IN');
    });
}

function Transition(BUBBLE, DIRECTION){
    
    if(BUBBLE.id == "InternePreview" || BUBBLE.id == "PublicPreview"){
        if(DIRECTION == "OUT"){
            DIRECTION = "OFF";
        }
    }
    
    let Scale = 1;
    let Transition = "all .5s ease-in-out";
    let Spacer = document.getElementById('Messages');
    let ElementStyle = window.getComputedStyle(BUBBLE);
    let ElementWidth = BUBBLE.getBoundingClientRect().width;
    let ElementHeight = BUBBLE.getBoundingClientRect().height;
    let ElementMarginTop = parseFloat(ElementStyle.marginTop);
    let ElementMarginBottom = parseFloat(ElementStyle.marginBottom);
    let MessagesStyle = window.getComputedStyle(Spacer);
    let MessagesPaddingLeft = parseFloat(MessagesStyle.paddingLeft);
    let MessagesPaddingRight = parseFloat(MessagesStyle.paddingRight);
    let MessagesPaddingBottom = parseFloat(MessagesStyle.paddingBottom);
        
    if(document.getElementById('mode').value == 'light'){ Scale = 2.5; }
    
    let Height = (ElementHeight + ElementMarginTop + ElementMarginBottom) / Scale + 'px';
    
    if(DIRECTION == "IN"){
        //Set Start Position
        BUBBLE.style.opacity = 1;
        BUBBLE.style.transform = "scale(0)";
        BUBBLE.style.bottom = MessagesPaddingBottom + 'px';
        if(BUBBLE.classList.contains("Left")){ BUBBLE.style.left = MessagesPaddingLeft + "px"; }
        else if(BUBBLE.classList.contains("Right")){ BUBBLE.style.right = MessagesPaddingRight + "px"; }

        //Animation
        requestAnimationFrame(() => {
            BUBBLE.style.transition = Transition;
            BUBBLE.style.transform = "scale(1)";
            Spacer.style.setProperty('--transition', Transition);
            Spacer.style.setProperty('--height', Height);
        });
        
        //Set End Position
        setTimeout(function() {
            Spacer.style.removeProperty('--height');
            Spacer.style.removeProperty('--transition');
            BUBBLE.style.position = "static";
            BUBBLE.style.bottom = "0px";
            if(BUBBLE.classList.contains("Left")){ BUBBLE.style.left = "0px"; }
            else if(BUBBLE.classList.contains("Right")){ BUBBLE.style.right = "0px"; }
        }, 500);
    } else if(DIRECTION == "OUT"){
        //Set Start Position
        BUBBLE.style.removeProperty('transition');
        BUBBLE.style.height = ElementHeight / Scale + 'px';
        BUBBLE.style.position = "relative";
        
        //Animation
        requestAnimationFrame(() => {
            BUBBLE.style.transition = Transition;
            if(ElementStyle.getPropertyValue('float') == "left"){ BUBBLE.style.left = "-" + (ElementWidth + MessagesPaddingLeft) + "px"; }
            else if(ElementStyle.getPropertyValue('float') == "right"){ BUBBLE.style.right = "-" + (ElementWidth + MessagesPaddingRight) + "px"; }
        });
        
        //Set End Position
        setTimeout(function() {
            BUBBLE.style.height = 0;
            BUBBLE.style.padding = 0;
            BUBBLE.style.margin = 0;
        }, 500);
    } else if(DIRECTION == "OFF"){
        //Set Start Position
        Spacer.style.setProperty('--height', Height);
        BUBBLE.style.removeProperty('transition');
        BUBBLE.style.position = "absolute";
        BUBBLE.style.bottom = MessagesPaddingBottom + 'px';
        if(BUBBLE.classList.contains("Left")){ BUBBLE.style.left = MessagesPaddingLeft + "px"; }
        else if(BUBBLE.classList.contains("Right")){ BUBBLE.style.right = MessagesPaddingRight + "px"; }        

        //Animation
        setTimeout(function() {
            BUBBLE.style.transition = Transition;
            Spacer.style.setProperty('--transition', Transition);
            BUBBLE.style.transform = "scale(0)";
            Spacer.style.removeProperty('--height');
        }, 500);
    }
}

function SelectMessage(ID){
    var STAMP = document.getElementById(ID).dataset.stamp;
    var CONTENT = document.getElementById(ID).innerHTML;
	const DATA = {id:ID, stamp:STAMP, content:CONTENT};
    lmc.postMessage(DATA);
}

function DeleteMessage(ID){
	let TextBubble = document.getElementById(ID);
    Transition(TextBubble, 'OUT');
	
    window.setTimeout(function () {
		document.getElementById(ID).remove();		
		//Reset Bubbles Left/Right IF none left
		if(document.getElementsByClassName("TextBubble").length == 0){
            delete window.NoteType;
			delete window.last_message;
		}
    }, 1000);
}

function ClearMessages(){
	//Clear all Timeouts
	for (var c=0; c<ClearNote.length; c++) { clearTimeout(ClearNote[c]); }
	for (var k=0; k<KillNote.length; k++) { clearTimeout(KillNote[k]); }

	//Fade then kill all TextBubbles
	var TextBubbles = document.getElementsByClassName("TextBubble");
	for (var i = 0; i < TextBubbles.length; i++) { Transition(TextBubbles[i], 'OUT'); }
	window.setTimeout(function () { document.getElementById("Messages").innerHTML = '';  }, 1000);
	
	//Reset Bubbles Left/Right
	delete window.NoteType;
    delete window.last_message;
}