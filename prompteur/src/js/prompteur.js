var currentElement;
var currentSize;
var currentScript;
var currentTransform;
var autoScroll;
var isPlaying = false; // Pour vérifier si le défilement est en cours

function togglePlayPause() {
    var playPauseButton = document.getElementById("playPauseButton");
    isPlaying = !isPlaying; // Alterne entre lecture et pause

    if (isPlaying) {
        playPauseButton.innerHTML = "pause";
        var speed = document.getElementById("speed").value;
        AutoScroll(speed); // Lance le défilement avec la vitesse sélectionnée
    } else {
        playPauseButton.innerHTML = "play_arrow";
        clearInterval(autoScroll); // Arrête le défilement
    }
}

function FullScreenIN(what){
    document.documentElement.webkitRequestFullScreen();
    what.innerHTML = "close_fullscreen";
    what.setAttribute( "onClick", "FullScreenOUT(this)" );
}

function FullScreenOUT(what){
    document.exitFullscreen();
    what.innerHTML = "open_in_full";
    what.setAttribute( "onClick", "FullScreenIN(this)" );
}

function FlipScript(){
	if(currentElement == null){ currentElement = document.getElementById("script"); }
	if(currentTransform == null){ currentTransform = currentElement.style.transform; }
	currentElement.style.transform = (currentTransform == "translateX(-50%)") ? "scale(-1, 1) translateX(50%)" : "translateX(-50%)";
	currentTransform = currentElement.style.transform;
}

window.addEventListener('wheel', function(event){
    if (!isPlaying) {
        // La molette agit normalement quand le défilement automatique est en pause
        return true;
    }

    // Si lecture active, ajuste la vitesse
    if (currentElement == null) { currentElement = document.getElementById("script"); }
    var currentSpeed = document.getElementById("speed").value;

    if (event.deltaY < 0) { currentSpeed = Number(currentSpeed) + 1; }
    else if (event.deltaY > 0) { currentSpeed = Number(currentSpeed) - 1; }

    document.getElementById("speed").value = currentSpeed;
    Update(document.getElementById("speed"));
    event.preventDefault(); // Empêche le comportement par défaut de la molette
    return false;
}, { passive: false });

function Update(what){
	
	var type = what.id;
	
	if(type == "speed"){
		var speed = document.getElementById("speed").value;
		document.getElementById("speed_output").innerHTML = speed;
        //speed = (speed*speed*speed)/100;
		AutoScroll(speed);
		
	}
	else if(type == "size"){
		var originalSize = 10;
		currentSize = (what.value/100)*originalSize + "vw";
		document.getElementById("script").style.fontSize = currentSize;
		document.getElementById("size_output").innerHTML = what.value;
		}
	}

function AutoScroll(SPEED){
	clearInterval(autoScroll);
	autoScroll = setInterval(
        function(){
            if(SPEED != 0){ window.scrollTo(0, window.scrollY-SPEED); }
            if(window.scrollY == 0){ SPPED = 0; }
        }, 20);
}

function ToggleEdit(){
	
	if(currentElement == null){ currentElement = document.getElementById("script"); }
	var type = currentElement.tagName;

	if(type == 'DIV'){
		var newElement = document.createElement("TEXTAREA");
			newElement.setAttribute('id', 'script');
			newElement.addEventListener("focusout", ToggleEdit);
			newElement.onkeyup = function(){ currentScript = currentElement.value; };
			newElement.style.transform = "translateX(-50%)";
	}
	else if(type == 'TEXTAREA'){
		var newElement = document.createElement("DIV");
			newElement.setAttribute('id', 'script');
			newElement.addEventListener("dblclick", ToggleEdit);
            newElement.addEventListener("click", FlipScript);
			
		if(currentTransform == null){ currentTransform = currentElement.style.transform; }
			newElement.style.transform = currentTransform;
	}

	if(currentSize == null){ currentSize = currentElement.style.fontSize; }
	if(currentScript == null){ currentScript = currentElement.innerHTML; }
	
	//DEal with carriage return and BR
	if(type == 'DIV'){ currentScript = currentScript.replace(/<br\s*[\/]?>/gi, "\n"); }
	else if(type == 'TEXTAREA'){ currentScript = currentScript.replace(/\r\n?|\n/g, "<br />"); }
	
	newElement.style.fontSize = currentSize;
	newElement.innerHTML = currentScript;
	
	var parent = currentElement.parentElement;
		parent.removeChild(currentElement);
		parent.appendChild(newElement);
	
	newElement.focus();
	currentElement = newElement;
}