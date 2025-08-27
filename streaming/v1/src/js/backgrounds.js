// JavaScript Document

//Open Broadcast Channels
var bk = new BroadcastChannel('obs-background-channel');
var cd = new BroadcastChannel('obs-count-down-channel');
var si = new BroadcastChannel('obs-stream-info-channel');

function UrlExists(url)
{
    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();
    return http.status!=404;
}

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
	
	if(document.getElementById("Title")){
		if(TITLE.match(/<br\s*\/?>/ig) != null){ document.getElementById("Title").dataset.line = TITLE.match(/<br\s*\/?>/ig).length+1; }
		else{ document.getElementById("Title").dataset.line = 1; }
	}
    
    if(document.getElementById("Title"))
        {
        //Insert New Text
        document.getElementById("Title").innerHTML = "<span>"+TITLE+"</span>";
        document.getElementById("SubTitle").innerHTML = MESSAGE;
        document.getElementById("Date").innerHTML = DATE;
            
        //Add Style CSS File
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
    }
    if(document.getElementById('video')){
        //Change Video Background
        var VIDEO = document.getElementById('video');
        var SOURCE = VIDEO.getElementsByTagName('source')[0];
        var URL = "../styles/src/vid/bkg-"+STYLE+".webm";

        if(UrlExists(URL)){
            SOURCE.setAttribute('src', URL);
            VIDEO.load();
            VIDEO.play();
        }
        else{
            SOURCE.setAttribute('src', "../src/vid/bkg-enpjj.webm");
            VIDEO.load();
            VIDEO.play();
        }
    }
    
    //Populate Console
	console.log ('Title: ' + received_data[0] + ', Message: ' + received_data[1] + ', Date: ' + received_data[2] + ', Style Sheet: ' + received_data[3]);
}

//Background Data
bk.onmessage = function (ev)
	{
	//Receive Data
	received_data=ev.data.split("|");
		
	//Sort Data
	var MESSAGE = received_data[0];
	
	//Insert New Text
	document.getElementById("message").innerHTML = MESSAGE;
	console.log ('Message: ' + received_data[0]);
	}

function RewindAfterward(video){
	var delai = 30000-video.duration*1000; // 30s Full Running Time
	if(video.currentTime == video.duration){
		video.pause();
		setTimeout(() => {  video.currentTime = 0; }, delai);
	}
}

//Countdown
var MyCountDown;

cd.onmessage = function (ev)
	{
	received_data=ev.data.split(":");
		
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