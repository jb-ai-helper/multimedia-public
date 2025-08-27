// JavaScript Document
var site = parent.parent.site;

var playlist;
var videos = new Array;
var index = -1;
var seed = new Date().getTime();
var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            playlist = JSON.parse(xhttp.responseText);
            FilterVideos();
        }
    };
//Open Request
xhttp.open("GET", "/signage/location/" + site + "/json/playlist.json"+"?"+seed, true);
//Disable Browser Cache
xhttp.setRequestHeader('Cache-Control', 'no-cache, no-store, max-age=0');
xhttp.setRequestHeader('Expires', 'Thu, 1 Jan 1970 00:00:00 GMT');
xhttp.setRequestHeader('Pragma', 'no-cache');
//Send Request
xhttp.send();

function FilterVideos(){
    var now = new Date();
    playlist.forEach( video => {
        //Get Current Date
        let start = new Date(video['start']);
        let end = new Date(video['end']);

        //Check if the date is correct            
        if(now.getTime() > start.getTime() && now.getTime() < end.getTime()){
            //Get Current Time
            var nowISO = now.toISOString();
            var today = nowISO.split('T')[0];
            var from = new Date(today + 'T' + video['from']);
            var to = new Date(today + 'T' + video['to']);
            
            //Check if the time is correct
            if(now.getTime() > from.getTime() && now.getTime() < to.getTime()){
                let SRC = "/signage/src/vid/playlist/"+video['src']+".mp4";
                videos.push(SRC);
            }
        }
    });
    //Start Playing Videos
    PlayVideos();
}

function PlayVideos(){  
    var transition = document.getElementById('Tansition');
    var player = document.getElementById('Player');
    
    player.addEventListener("ended", Next);
    player.addEventListener("timeupdate", TransitionAway);
    player.addEventListener("play", function() { transition.className = "OFF";});
    player.addEventListener("error", function(){
        console.error("Erreur de chargement, passage à la vidéo suivante.");
        Next();
    });
    Next();
}

function Next(){
    index++;
    var player = document.getElementById('Player');
    var Layout = window.parent.parent.document.getElementById('Layout');
    var Overlay = window.parent.parent.document.getElementById('Overlay');
    
    //Load next video in playlist
    if(index < videos.length) {
        player.src = videos[index];
    } else {
        //Fade out Layout & Overlay
        Layout.className = "OFF";
        Overlay.classList.remove("ON");
        setTimeout(() => Layout.contentWindow.location.reload(), 1000);
    }
}

function TransitionAway(){
    var transition = document.getElementById('Tansition');
    var player = document.getElementById('Player');
    var ending = player.duration - 1; //1 sec
    
    //Fade to black after ending time
    if (player.currentTime >= ending && !transition.classList.contains('ON')) {
        transition.className = "ON";
    }
}