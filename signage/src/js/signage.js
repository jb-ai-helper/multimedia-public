// JavaScript Document

const pageName = window.location.pathname.split('/').pop();
let manager_page = 'gestion.php';

if(site == 'sc'){ manager_page = 'manager.php'; }

//Raccourcis Clavier
var keys = {};
onkeydown = onkeyup = function(e){
	e = e || event;
	e.which = e.which || e.keyCode;
	keys[e.which] = e.type === 'keydown';
		
	if(e.key == "m" && e.altKey == true) { window.location.assign("/signage/location/" + site + "/" + manager_page); }//Alt + C    
}

if (pageName !== 'manager.php'){
    //Connect & Listen to SSE Connection
    var command = new EventSource('/signage/src/php/update.php?file=command&site=' + site);
        command.onmessage = function(event) { var DATA = JSON.parse(event.data); Recieve(DATA); };
}

function Recieve(DATA){
    if(typeof last_DATA === 'undefined'){ window.last_DATA = DATA; }
	if(JSON.stringify(DATA) != JSON.stringify(last_DATA))
	{
        console.log("Received Data:", DATA);
		//Log Recieved Message
		console.log("Command received: " + event.data);
		//Update Last_DATA
		last_DATA = DATA;
		
        //Alert Recieved
		if(DATA['type'] == "alert")
        {
            var Overlay = document.getElementById('Overlay');
                Overlay.innerHTML = "";

            //Clear Current Timeouts
            if(typeof Player_END !== 'undefined'){ clearTimeout(Player_END); }
            if(typeof Player_OFF !== 'undefined'){ clearTimeout(Player_OFF); }
            //Set Overlay Design
            Overlay.className = DATA['cmd'];
            if(DATA['cmd'] == 'intrusion'){ PlaySound('/signage/src/aud/intrusion.mp3'); }
        }
        
        //Refresh Recieved
		if(DATA['type'] == "refresh")
        {
            if(DATA['cmd'] == 'full'){
                
                var Overlay = document.getElementById('Overlay');
                    Overlay.classList.remove("ON");
                var Layout = document.getElementById('Layout');
                    Layout.className = "OFF";
                var iframes = Layout.contentWindow.document.getElementsByTagName('iframe');
                    iframes = Array.from(iframes);
                
                setTimeout(() => {
                    iframes.forEach((iframe) => {
                        var iframe_url = new URL(iframe.src);
                        if (iframe_url.origin === window.location.origin){
                            iframe.addEventListener('load', console.log('Refresed: ', iframe.id));
                            iframe.contentWindow.location.reload(true);
                        }
                        else{ console.log('Avoided: ', iframe.id); }
                    });
                    window.location.reload(true);
                }, 1500);
            }
            else if(DATA['cmd'] == 'simple'){
                //Clear Current Timeouts
                if(typeof Layout_OFF !== 'undefined'){ clearTimeout(Layout_OFF); }
                if(typeof Layout_RELOAD !== 'undefined'){ clearTimeout(Layout_RELOAD); }
                
                var Overlay = document.getElementById('Overlay');
                    Overlay.classList.remove("ON");
                
                var Layout = document.getElementById('Layout');
                    Layout.className = "OFF";
                setTimeout(() => Layout.contentWindow.location.reload(), 1500);
            }
        }
    }
}

async function StartSignage(){
    var now = new Date();
    
    //Récupère le style et l'ajoute à Layout si les dates correspondent
    const styleDetails = await getStyleDetails();
        
    if (styleDetails) {
        var { style_name, style_start, style_end } = styleDetails;
        console.log("Style Name:", style_name);
        console.log("Style Start:", style_start);
        console.log("Style End:", style_end);
        
        style_start = new Date(style_start);
        style_end = new Date(style_end);

        if(style_start <= now && now <= style_end && style_name != ""){
            console.log('style here');
            document.getElementById('Overlay').classList.add(style_name);
        }
    }
    
    // 3. Récupérer les détails de l'événement
    const eventDetails = await getEventDetails();

    if (eventDetails) {
        var { vertical_event, horizontal_event, event_start, event_end, event_loop } = eventDetails;
         
        var event = window.innerWidth > window.innerHeight ? horizontal_event : vertical_event;
            event_start = new Date(event_start);
            event_end = new Date(event_end);

        if(event_start <= now && now <= event_end && event != ""){
            Showcase(event, event_loop);
        } else {
            ActivateLayout();
        }
    }
    
}

async function getStyleDetails() {
    try {
        var seed = new Date().getTime();
        var styleURL = '/signage/location/' + site + '/json/style.json?seed=' + seed;
        const response = await fetch(styleURL);
        const styleData = await response.json();

        //style_name, style_start, style_end
        var style_name = styleData.name;
        var style_start = styleData.start;
        var style_end = styleData.end;

        // Retourne les données récupérées
        return { style_name, style_start, style_end };
    } catch (error) {
        console.error('Erreur lors de la récupération des détails de style:', error);
        return null;
    }
}

async function getEventDetails() {
    try {
        var seed = new Date().getTime();
        var eventURL = '/signage/location/' + site + '/json/event.json?seed=' + seed;
        const response = await fetch(eventURL);
        const eventData = await response.json();

        var vertical_event = eventData.vertical;
        var horizontal_event = eventData.horizontal;
        var event_loop = eventData.loop;
        window.event_start = eventData.start;
        window.event_end = eventData.end;

        // Retourne les données récupérées
        return { vertical_event, horizontal_event, event_start, event_end, event_loop };
    } catch (error) {
        console.error('Erreur lors de la récupération des détails de l\'événement:', error);
        return null;
    }
}

function Showcase(VIDEO, CONTINUE){
    
    console.log("Event:", VIDEO);
    console.log("Event Loop:", CONTINUE);
    console.log("Event Start:", event_start);
    console.log("Event End:", event_end);

    //Get & Clear Overlay
    var Overlay = document.getElementById('Overlay');
        Overlay.innerHTML = "";
    //Get Layout
    var Layout = document.getElementById('Layout');
    //Get Orientation / Folder
    var folder = window.innerWidth > window.innerHeight ? "horizontal/" : "vertical/";
    //Create video player
    var player = document.createElement('video');
        player.src = '/signage/src/vid/' + folder + VIDEO + '.mp4';
        player.muted = true;
        player.autoplay = true;
        player.loop = false;
        //Play & FadeIN video with OFF timeout
        player.play().then(()=>{
            console.log("Playing:", player.src);
            Overlay.classList.add("ON");
            var video_duration = player.duration*1000;
            window.Player_OFF = setTimeout(() => Overlay.classList.remove("ON"), video_duration-(player.currentTime*1000)-1000);
            if(!CONTINUE) { window.Player_END = setTimeout(function() { ActivateLayout(); player.remove(); }, video_duration-(player.currentTime*1000)); }
            else { window.Player_END = setTimeout(() => Layout.contentWindow.location.reload(), video_duration-(player.currentTime*1000)); }
        })
        .catch(ActivateLayout);
    Overlay.appendChild(player);
}

function ActivateLayout(){
    var Layout = document.getElementById('Layout');
    var Overlay = document.getElementById('Overlay');
    
    //Play videos inside Layout THEN turn on Layout
    var Videos = Layout.contentWindow.document.getElementById('Videos').contentWindow.document.getElementById('Player');
        Videos.setAttribute('autoplay', true);
        Videos.play().then(() => {
                Layout.className = "ON";
                Overlay.classList.add("ON");
        });
}

function PlaySound(SOUND){
    var player = document.createElement('audio');
        player.setAttribute('src', SOUND);
        player.setAttribute('loop', 'true');
        player.play();
}