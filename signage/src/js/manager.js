// JavaScript Document

async function Send(DATA, FILE) {
    var data = JSON.stringify(DATA);
    
    try {
        var UPDATE = '/signage/src/php/update.php?file=' + FILE + "&site=" + site;
        let response = await fetch(UPDATE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: data
        });

        //console.log("Response status: " + response.status);
        console.log("Data sent: " + data);

        // Check if the response is OK (status code 200-299)
        if (!response.ok) {
            throw new Error("HTTP error, status = " + response.status);
        }

        // Attempt to parse the response as JSON
        let responseData = await response.json();
        console.log("Response data: ", responseData);
        
        //Alert Success if Playlist or Event
        if(responseData['status'] == 'success' && FILE != 'command'){
            let File_msg = FILE.charAt(0).toUpperCase() + FILE.slice(1).toLowerCase();
            alert(File_msg+" saved!");
            Trigger('fullrefresh');
        }

    } catch (error) {
        // If an error occurs, log the error and the full response for debugging
        console.error("Error sending data: ", error);
        try {
            let textResponse = await response.text();
            console.error("Response text: ", textResponse);
        } catch (textError) {
            console.error("Error reading response text: ", textError);
        }
    }
}

function ShowHide(what){
    var options = document.getElementById(what.id+'_options');
    what.checked ? options.style = "" : options.style = "display: none";
    console.log('Clicked on:', what.id);
}

function Trigger(TYPE){
    var type;
    var cmd;
    
    if(TYPE == "firealarm"){ type = "alert"; cmd = "incendie"; }
    else if(TYPE == "intrusionalert"){ type = "alert"; cmd = "intrusion"; }
    else if(TYPE == "fullrefresh"){ type = "refresh"; cmd = "full"; }

    const DATA = {type:type, cmd:cmd};
    Send(DATA, 'command');
}

function Save(TYPE){
    
    if(TYPE == "style"){
        var name = document.getElementById('style_select').value;
        var start = document.getElementById('style_start').value;
        var end = document.getElementById('style_end').value;
        const DATA = {name:name, start:start, end:end};
        Send(DATA, TYPE);
    }
    
    if(TYPE == "event"){
        var vertical = document.getElementById('vertical_select').value;
        var horizontal = document.getElementById('horizontal_select').value;
        var start = document.getElementById('event_start').value;
        var end = document.getElementById('event_end').value;
        var loop = document.getElementById('playlist_checkbox').checked;
        var DATA = {vertical:vertical, horizontal:horizontal, start:start, end:end, loop:loop};
        Send(DATA, TYPE);
    }
    
    if(TYPE == "playlist"){
        var playlist = new Array();
        var entries = Array.from(document.getElementsByClassName('entry'));
            entries.forEach((entry) => {
                let video = {};
                    video['src'] = entry.getElementsByClassName('video')[0].value;
                    video['start'] = entry.getElementsByClassName('start')[0].value;
                    video['end'] = entry.getElementsByClassName('end')[0].value;
                    video['from'] = entry.getElementsByClassName('from')[0].value;
                    video['to'] = entry.getElementsByClassName('to')[0].value;
                playlist.push(video);
            })
        const DATA = playlist;
        console.log(DATA);
        Send(DATA, TYPE);
    }
}

function Upload(what){

    var file = what.files[0];
    var name = what.files[0].name;//Get file name
        name = name.match(/[^\.]+/)[0];//Delete extension
        name = NormalizeName(name);//Format name

    var data = new FormData();
        data.append("file", file);

    var dir = "&dir=" + getVideoType(what);    
    var FilePath = "/signage/src/php/upload.php?name="+name+dir;

    var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                alert(xhttp.responseText);
                location.reload();
            }
        };
        xhttp.open("POST", FilePath, true);
        xhttp.send(data);
}

function Delete(what){
    var dir = "&dir="+getVideoType(what);
    
    if (what instanceof HTMLSelectElement){
        var video = what.value;
    } else {
        var video = what.parentElement.getElementsByTagName('select')[0].value;
    }
    
    var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                alert(xhttp.responseText);
                location.reload();
            }
        };
        xhttp.open("POST", "/signage/src/php/delete.php?file="+video+dir, true);
        xhttp.send();
}

async function SetUpPage(){
    //Récupère le style
    const styleDetails = await getStyleDetails();
        
    if (styleDetails) {
        var { style_name, style_start, style_end } = styleDetails;
        console.log("Style Name:", style_name);
        console.log("Style Start:", style_start);
        console.log("Style End:", style_end);
    }
    
    SetTime(document.getElementById('style_start'), style_start);
    SetTime(document.getElementById('style_end'), style_end);
    SelectOption(document.getElementById('style_select'), style_name)
    
    // Récupère les listes de videos
    try {
        var listURL = '/signage/src/php/list.php';
        const response = await fetch(listURL);
        const data = await response.json();

        // Initialiser les tableaux avec les données récupérées (global variables)
        window.videos = data.videos;
        window.vertical_events = data.vertical;
        window.horizontal_events = data.horizontal;

        // Afficher les données pour vérifier
        console.log("Videos:", videos);
        console.log("Vertical Events:", vertical_events);
        console.log("Horizontal Events:", horizontal_events);

        // 3. Récupérer les détails de l'événement et attendre la réponse
        const eventDetails = await getEventDetails();
        
        if (eventDetails) {
            var { vertical_event, horizontal_event, event_start, event_end, event_loop } = eventDetails;
            console.log("Vertical Event:", vertical_event);
            console.log("Horizontal Event:", horizontal_event);
            console.log("Event Start:", event_start);
            console.log("Event End:", event_end);
            console.log("Event Loop:", event_loop);
        }
        
        MakeList(document.getElementById('vertical_select'), vertical_events);
        SelectOption(document.getElementById('vertical_select'), vertical_event)
        MakeList(document.getElementById('horizontal_select'), horizontal_events);
        SelectOption(document.getElementById('horizontal_select'), horizontal_event)
        MakeList(document.getElementById('playlist_add'), videos);
        MakeList(document.getElementById('playlist_delete'), videos);
        
        SetTime(document.getElementById('event_start'), event_start);
        SetTime(document.getElementById('event_end'), event_end);
        
        document.getElementById('playlist_checkbox').checked = event_loop;

    } catch (error) {
        console.error('Erreur lors de la récupération des fichiers:', error);
    }
    // Lance la construction de la playlist
    BuildPlaylist();
    
}

function MakeList(IN, WITH){
    WITH.forEach((src) => {
        var option = document.createElement('option');
            option.setAttribute('value', src);
            option.innerHTML = src;
        
        IN.appendChild(option);
    });
}

function SetTime(ELEMENT, TIME){
    ELEMENT.value = TIME;
    // Dispatch Change Event
    setTimeout(() => {
                ELEMENT.dispatchEvent(new Event('change'));
            }, 0);
}

function SelectOption(ELEMENT, OPTION){
    // Parcourir toutes les options de l'élément <select>
    for (let i = 0; i < ELEMENT.options.length; i++) {
        // Vérifier si l'option correspond à la valeur fournie
        if (ELEMENT.options[i].value === OPTION) {
            // Définir l'option comme sélectionnée
            ELEMENT.selectedIndex = i;
            // Dispatch Change Event
            setTimeout(() => {
                        ELEMENT.dispatchEvent(new Event('change'));
                    }, 0);
            break; // Sortir de la boucle une fois l'option trouvée et sélectionnée
        }
    }
}

function BuildPlaylist(){
    // Crée une seed pour éviter le cash de la playlist
    var seed = Math.round(Math.random()*10000);
    // Récupère les données de la playlist
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            playlist = JSON.parse(xhttp.responseText);
            console.log('Playlist:', playlist);
            playlist.forEach((video) => { AddEntry(video); });
        }
    };
    //Open Request
    var jsonURL = "json/playlist.json"+"?"+seed;
    xhttp.open("GET", jsonURL, true, true);
    //Disable Browser Cache
    xhttp.setRequestHeader('Cache-Control', 'no-cache, no-store, max-age=0');
    xhttp.setRequestHeader('Expires', 'Thu, 1 Jan 1970 00:00:00 GMT');
    xhttp.setRequestHeader('Pragma', 'no-cache');
    //Send Request
    xhttp.send();
}

function AddEntry(video){

    var playlist_div = document.getElementById('playlist');
    
    // Dropdown Menu Index = 0 >> STOP
    if (video.value === "") { return; }
    
    //Add video from Dropdown Menu
    if(!video['src']){
        //Get video SRC
        var SRC = video.value;
        //Reset Dropdown Menu
        video.selectedIndex = 0;
        //Rebuild video object from default
        var video = [];
            video['src'] = SRC;
            video['start'] = "0001-01-01";
            video['end'] = "9999-12-31";
            video['from'] = "00:00";
            video['to'] = "23:59";
    }
        
    //Generate new entry
    var entry = document.createElement('li');
        entry.setAttribute('class', 'entry');

    var select = document.createElement('select');
        select.setAttribute('class', 'video');

    var play = document.createElement('div');
        play.setAttribute('class', 'preview');
        play.setAttribute('onclick', 'Preview(this)');

    var start = document.createElement('input');
        start.setAttribute('class', 'start');
        start.setAttribute('type', 'date');
        start.setAttribute('value', video['start']);

    var end = document.createElement('input');
        end.setAttribute('class', 'end');
        end.setAttribute('type', 'date');
        end.setAttribute('value', video['end']);

    var from = document.createElement('input');
        from.setAttribute('class', 'from');
        from.setAttribute('type', 'time');
        from.setAttribute('value', video['from']);

    var to = document.createElement('input');
        to.setAttribute('class', 'to');
        to.setAttribute('type', 'time');
        to.setAttribute('value', video['to']);
	
	var up = document.createElement("div");
		up.setAttribute('class', "moveup");
		up.setAttribute('onclick', "MoveUp(this)");
	
	var down = document.createElement("div");
		down.setAttribute('class', "movedown");
		down.setAttribute('onclick', "MoveDown(this)");
	
	var del = document.createElement("div");
		del.setAttribute('class', "delete");
		del.setAttribute('onclick', "DeleteEntry(this)");
    
    MakeList(select, videos);
    SelectOption(select, video['src']);

    entry.appendChild(select);
    entry.appendChild(del);
    entry.appendChild(down);
    entry.appendChild(play);
    entry.appendChild(up);
    entry.appendChild(start);
    entry.appendChild(end);
    entry.appendChild(from);
    entry.appendChild(to);
    playlist_div.appendChild(entry);
}

function getVideoType(what){
    var Type = what.parentElement?.id || what.parentElement?.parentElement?.id;
        Type = Type.includes('_') ? Type.split('_')[0] : Type;
    return Type;
}

function Preview(what){
    var Type = getVideoType(what);
    var Video = what.parentElement.getElementsByTagName('select')[0].value + '.mp4';
    window.open("/signage/src/vid/" + Type + '/' + Video,"_blank");
}

function DeleteEntry(what){
    var playlist_div = document.getElementById('playlist');
    var entries = playlist_div.getElementsByClassName('entry');
    var entry = what.parentElement;
    
    if(entries.length != 1){ playlist_div.removeChild(entry); }
    else{ alert("Playlist cannot be empty!") }
}

function Check(what, ID){
    var ELEMENT = document.getElementById(ID);
    what.value != "" ? ELEMENT.checked = true : ELEMENT.checked = false;
    // Dispatch Change Event
    setTimeout(() => {
                ELEMENT.dispatchEvent(new Event('change'));
            }, 0);
}

function Show(what, ID){
    var Element = document.getElementById(ID);
    Element.classList.toggle('hidden', !(what.checked));
}

function Hide(what, ID){
    var Element = document.getElementById(ID);
    Element.classList.toggle('hidden', what.checked);
}

function Unable(CONDITIONS, TARGET) {
    // Vérifie si toutes les cases à cocher spécifiées dans 'requiredCheckboxIds' sont cochées
    const allChecked = CONDITIONS.every(id => document.getElementById(id).checked);
    // Récupère la checkbox cible (par exemple, 'playlist')
    const ELEMENT = document.getElementById(TARGET);
    // Active ou désactive la checkbox cible selon l'état des checkboxes requises
    ELEMENT.disabled = !allChecked;
    // Uncheck checkbox if it is disabled
    if(!allChecked){ ELEMENT.checked = false; }
    // Dispatch Change Event
    setTimeout(() => {
                ELEMENT.dispatchEvent(new Event('change'));
            }, 0);
}