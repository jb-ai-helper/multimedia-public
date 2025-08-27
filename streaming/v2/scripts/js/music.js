// JavaScript Document

//Album Info
var copyright = "Musique&nbsp;: <i>Noir Et Blanc Vie</i> © YouTube Audio Library";
var playlist = [
	"../musics/noir-et-blanc-vie/baeb-steps_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/birth-noir_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/brighton-lights_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/establishment-85_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/false-startz_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/fonkee-ryde_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/great-whyte-18_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/kid-sos_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/longest-run_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/prophet-7_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/shredded-aka-full-circle_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/skewls-owt_noir-et-blanc-vie.mp3", 
	"../musics/noir-et-blanc-vie/still-not-rite_noir-et-blanc-vie.mp3"];

//Setup Global Variables
var lastSong = null;
var selection = null;
var MODE = URL.searchParams.get("mode");

function InitializePlayer()
{
	if(MODE != "light")
	{
		//Setup Copyright & Audio Player Elements
		var AudioCopyrightElement = document.createElement("div")
			AudioCopyrightElement.setAttribute("id", "Artist");

		var AudioPlayerElement = document.createElement("audio")
			AudioPlayerElement.setAttribute("id", "Music");

		//Place new Element on the page
		document.body.appendChild(AudioCopyrightElement);
		document.body.appendChild(AudioPlayerElement);


		// Get Audio Player & Set Parameters
		var player = document.getElementById("Music");
			player.autoplay = true;
			player.volume = 0.25;

			// Run function when the song ends
			player.addEventListener("ended", SelectRandom);

			//Select & Play Song
			SelectRandom();
			document.getElementById('Artist').innerHTML = copyright;
			player.play();
	}
}

function SelectRandom()
{
	var player = document.getElementById("Music");
	while(selection == lastSong){ selection = Math.floor(Math.random() * playlist.length); }
	lastSong = selection; // Remember the last song
	player.src = playlist[selection]; // Tell HTML the location of the new song
}

function FadeOutPlayer(T)
{
	//Set Default Transition Time (1s)
	if(!T){ T = 1; }//SECONDS
	
	//FadeOut Copyright
	var copyright = document.getElementById("Artist");
		copyright.className = "OFF";
	
	//Get Player Volume
	var player = document.getElementById("Music");
	var V = player.volume;
	
	//FadeOut Music
	var I = 25; //IMAGES/s
	var Interval = 1000/I //MILLISECONDS/i
	var AMOUNT = V/I/T;
	var FadeOutMusic = setInterval(LowerVolume, Interval, AMOUNT);
	setTimeout(function(){ clearInterval(FadeOutMusic) }, T*1000);
	
	//Delete Player Elements
	setTimeout(DeletePlayer, T*1000+1000);
}

function DeletePlayer()
{
	if(document.getElementById("Music")){ document.body.removeChild(document.getElementById("Music")); }
	if(document.getElementById("Artist")){ document.body.removeChild(document.getElementById("Artist")); }
}

function LowerVolume(AMOUNT)
{
	if(document.getElementById("Music"))
	{
		var player = document.getElementById("Music");
		if(player.volume - AMOUNT > 0) { player.volume = player.volume - AMOUNT; }
		else{ player.volume = 0; }
	}
}