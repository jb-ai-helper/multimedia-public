// JavaScript Document

var lastSong = null;
var selection = null;

//Setup Copyright & Audio Player Elements
var AudioCopyrightElement = document.createElement("div")
	AudioCopyrightElement.setAttribute("id", "audio-copyright");

var AudioCopyrightCSS = document.createElement("link")
	AudioCopyrightCSS.setAttribute("rel", "stylesheet");
	AudioCopyrightCSS.setAttribute("href", "../src/css/musicplayer.css");

var PlayerElement = document.createElement("audio")
	PlayerElement.setAttribute("id", "audioplayer");

//Place new Element on the page
document.body.appendChild(AudioCopyrightElement);
document.head.appendChild(AudioCopyrightCSS);
document.body.appendChild(PlayerElement);

var player = document.getElementById("audioplayer");

//Album Info
var copyright = "Musique&nbsp;: <i>Noir Et Blanc Vie</i> © YouTube Audio Library";
var playlist = [
	"/streaming/src/son/noir-et-blanc-vie/baeb-steps_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/birth-noir_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/brighton-lights_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/establishment-85_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/false-startz_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/fonkee-ryde_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/great-whyte-18_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/kid-sos_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/longest-run_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/prophet-7_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/shredded-aka-full-circle_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/skewls-owt_noir-et-blanc-vie.mp3", 
	"/streaming/src/son/noir-et-blanc-vie/still-not-rite_noir-et-blanc-vie.mp3"];
	
// Get Audio Player & Set Parameters
	player.autoplay=true;
	// Run function when the song ends
	player.addEventListener("ended", selectRandom);

	//Select & Play Song
	selectRandom();
	document.getElementById('audio-copyright').innerHTML = copyright;
	player.play();

function selectRandom()
{
	while(selection == lastSong){ selection = Math.floor(Math.random() * playlist.length); }
	lastSong = selection; // Remember the last song
	player.src = playlist[selection]; // Tell HTML the location of the new song
}