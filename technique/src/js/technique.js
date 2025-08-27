// JavaScript Document

//Raccourcis Clavier
var keys = {};
onkeydown = onkeyup = function(e){
	e = e || event;
	e.which = e.which || e.keyCode;
	keys[e.which] = e.type === 'keydown';
		
	if(e.key == "m" && e.altKey == true) { window.location.assign("gestion.php"); }//Alt + l
}

// Force le rechargement de Manager si rechargé via le cache
window.addEventListener('pageshow', (event) => {
    if (event.persisted && window.location.href.includes('gestion.php')) {
        location.reload(); 
    }
});

function New(){
    var Sure = confirm('Vous êtes sur le point de réinitialiser le formulaire.\r\nToute information non sauvegardée sera perdue.');
    if(Sure) {
        window.location = '../technique/';
    }
}

function DownloadCSV() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "src/php/export.php", true);
    xhr.responseType = "blob";
    xhr.onload = function () {
        if (xhr.status === 200) {
            var blob = new Blob([xhr.response], { type: "text/csv;charset=utf-8;" });
            var link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "interventions.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            console.error("Failed to download file.");
        }
    };
    xhr.send();
}

//Setup the SELECT service element
function Select(SELECT, OPTION) {
    var select = document.getElementById(SELECT);
    var found = false;

    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].value.startsWith(OPTION)) {
            select.options[i].selected = true;
            found = true;
            break;
        }
    }

    // If service is empty or not found in the options, select the placeholder option
    if (!found || service === "") {
        select.selectedIndex = 0;
    }
	select.dispatchEvent(new Event('change'));
}

function PreciseLocation(){
    var location = document.getElementById('location');
	//Reset Location Menu
	if(!window.previous_location) window.previous_location = null;
	if(window.previous_location && window.previous_location !== location.selectedOptions[0]){
		const previous = window.previous_location;
		if(previous.value.startsWith("RES-")){
			previous.value = "RES";
			previous.textContent = "Résidence (précisez ↴)";
		} else if (previous.value.startsWith("PTF-")){
			previous.value = "PTF";
			previous.textContent = "PTF (précisez ↴)";
		}
	}
	window.previous_location = location.selectedOptions[0];
	
	//Remove any sub-menu
	var submenus = document.querySelectorAll('[id^="sub-location"]');
	if(submenus.length > 0){
		submenus.forEach(el => {
			el.parentElement.remove();
		});
	}

	//Add sub-menu for location
	var option_prompt = document.createElement("option");
		option_prompt.disabled = true;
		option_prompt.selected = true;
		option_prompt.textContent = "Sélectionner";
		option_prompt.value = "";

	if(location.value.includes("PTF")){
		var select = document.createElement('select');
			select.setAttribute('id', "sub-location");
			select.appendChild(option_prompt);
			select.onchange = function() {
				setLocation(this);
			};
		
		var label = document.createElement('label');
			label.setAttribute('for', "sub-location");
			label.innerHTML = "PTF&nbsp;:&nbsp;";
			label.appendChild(select);

		const options = [
			{ label: "PTF Grand-Nord", value: "GN" },
			{ label: "PTF Île-de-France", value: "IDFOM-PSD" },
			{ label: "MU Antilles-Guyane", value: "IDFOM-AG" },
			{ label: "MU Réunion-Mayotte", value: "IDFOM-RM" },
			{ label: "MU Océan Pacifique", value: "IDFOM-OP" },
			{ label: "PTF Grand-Centre", value: "GC" },
			{ label: "PTF Centre-Est", value: "CE" },
			{ label: "PTF Grand-Ouest", value: "GO" },
			{ label: "PTF Grand-Est", value: "GE" },
			{ label: "PTF Sud-Ouest", value: "SO" },
			{ label: "PTF Sud-Est", value: "SE" },
			{ label: "PTF Sud", value: "SUD" }
		];
		
		options.forEach(opt => {
			const option = document.createElement("option");
			option.textContent = opt.label;
			option.value = opt.value;
		
		select.appendChild(option);
		});
		
		location.parentElement.insertAdjacentElement("afterend", label);
	} else if(location.value.includes("RES")){
		//Sub Menu for Bâtiment
		var select_wing = document.createElement('select');
			select_wing.setAttribute('id', "sub-location-wing");
			select_wing.appendChild(option_prompt);
			select_wing.onchange = function() {
				addFloor(this);
			};
		
		var label_wing = document.createElement('label');
			label_wing.setAttribute('for', "sub-location-wing");
			label_wing.innerHTML = "Bâtiment&nbsp;:&nbsp;";
			label_wing.appendChild(select_wing);

		const options_wing = [
			{ label: "A", value: "A" },
			{ label: "B", value: "B" },
			{ label: "C", value: "C" }
		];
		
		options_wing.forEach(opt => {
			const option = document.createElement("option");
			option.textContent = opt.label;
			option.value = opt.value;
		
		select_wing.appendChild(option);
		});
		
		location.parentElement.insertAdjacentElement("afterend", label_wing);
	}
	
	//Change Place Holder for Description
    var description = document.getElementById('description');
    if(location.value == 'ELSE'){
        description.setAttribute('placeholder', "Description de la demande et du lieu de l'intervention...");
    } else {
        description.setAttribute('placeholder', "Description de la demande...");
    }
    
}

function addFloor(what){
	
	if(document.getElementById('sub-location-floor')){
		document.getElementById('sub-location-floor').parentElement.remove();
	}

	var option_prompt = document.createElement("option");
		option_prompt.disabled = true;
		option_prompt.selected = true;
		option_prompt.textContent = "Sélectionner";
		option_prompt.value = "";

	var FloorNb = what.value == "A" || what.value == "B" ? 4 : 3;
	
	const options_floor = [
		{ label: "1\u1d49\u02b3", value: "1" },
		{ label: "2\u1d49", value: "2" },
		{ label: "3\u1d49", value: "3" }
	];

	if(FloorNb == 4){
		options_floor.unshift(
			{ label: "Rez-de-chaussée", value: "0" }
		);		
	}
	//Sub Menu for Floors
	var select_floor = document.createElement('select');
		select_floor.setAttribute('id', "sub-location-floor");
		select_floor.appendChild(option_prompt);
		select_floor.onchange = function() {
			addRoom(this);
		};

	var label_floor = document.createElement('label');
		label_floor.setAttribute('for', "sub-location-bat");
		label_floor.innerHTML = "Étage&nbsp;:&nbsp;";
		label_floor.appendChild(select_floor);

	options_floor.forEach(opt => {
		const option = document.createElement("option");
		option.textContent = opt.label;
		option.value = opt.value;

	select_floor.appendChild(option);
	});

	what.parentElement.insertAdjacentElement("afterend", label_floor);
}

function addRoom(what){
	
	if(document.getElementById('sub-location-room')){
		document.getElementById('sub-location-room').parentElement.remove();
	}
	
	var wing = document.getElementById('sub-location-wing').value;
	var floor = what.value;
	
	const 	RoomNb = [];
			RoomNb["A0"] = 8;
			RoomNb["A1"] = 13;
			RoomNb["A2"] = 16;
			RoomNb["A3"] = 16;
			RoomNb["B0"] = 11;
			RoomNb["B1"] = 19;
			RoomNb["B2"] = 23;
			RoomNb["B3"] = 23;
			RoomNb["C1"] = 21;
			RoomNb["C2"] = 25;
			RoomNb["C3"] = 25;

	var option_prompt = document.createElement("option");
		option_prompt.disabled = true;
		option_prompt.selected = true;
		option_prompt.textContent = "Sélectionner";
		option_prompt.value = "";

	//Sub Menu for Floors
	var select_room = document.createElement('select');
		select_room.setAttribute('id', "sub-location-room");
		select_room.appendChild(option_prompt);
		select_room.onchange = function() {
			setLocation(this);
		};

	var label_room = document.createElement('label');
		label_room.setAttribute('for', "sub-location-bat");
		label_room.innerHTML = "Chambre&nbsp;:&nbsp;";
		label_room.appendChild(select_room);
	
	for(let r = 1; r<= RoomNb[wing+floor]; r++){
		var room = r;
		if(r<10) room = '0'+r;
		const	option = document.createElement("option");
				option.textContent = room;
				option.value = wing+floor+room;
		
		select_room.appendChild(option);
	}
	what.parentElement.insertAdjacentElement("afterend", label_room);
}

function setLocation(what){
	//Remove any sub-menu for location
	var submenus = document.querySelectorAll('[id^="sub-location"]');

	if(submenus.length > 0){
		submenus.forEach(el => {
			el.parentElement.remove();
		});
	}

	//
    var location = document.getElementById('location');
	
	for (let option of location.options) {
		if (option.value === location.value) {
			let text = what.options[what.selectedIndex].textContent;
			if(location.value.includes("RES")){ text = "Résidence ("+what.value+")"; }
			option.value = location.value+"-"+what.value;
			option.textContent = text;
			break;
		}
	}
}

function Login() {
    let ref = document.getElementById('ref').innerHTML;
    let password = prompt('Renseignez votre clé d\'identification :', '');
    
    // Create an AJAX request to send the password securely
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "src/php/login.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                // Process server response
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    let newUrl = window.location.origin + window.location.pathname + "?ref=" + ref + "&token=" + response.token;
                    history.replaceState(null, '', newUrl);
                    location.reload();
                } else {
                    alert(response.message);
                }
            } else {
                alert("Erreur lors de la vérification de la clé !");
            }
        }
    };

    var data = {
        ref: ref,
        password: password
    };

    xhr.send(JSON.stringify(data));
}

function Open(what){
    var ref = what.getAttribute('data-ref');
    window.location = "../technique/?ref=" + ref;
}

function DelayedAlert(REF, MSG){
    var AlertBox = document.createElement('div');
        AlertBox.setAttribute('class', "alertbox");
        AlertBox.innerHTML = MSG;
    
    document.body.appendChild(AlertBox);
    
    setTimeout(() => {
        window.location.href = "?ref=" + REF;
        history.replaceState(null, '', 'manager.php');
    }, 3000);
}

//Send original request
function Send() {
	//Check for any sub-menus
	var submenus = document.querySelectorAll('[id^="sub-location"]');
	if(submenus.length > 0){
		alert("Terminez d'abord votre sélection...");
		return;
	}

    Save().then(response => {
        if (!isNaN(response)) {
            Email('team');
            Email('applicant');
            DelayedAlert(response, 'La demande d\'intervention technique a bien été envoyée !');
        } else {
            alert(response);
        }
    }).catch(error => {
        alert(error);
    });
}

function Quote() {
	document.getElementById('status').value = 'quoted';
	Save().then(response => {
		if (!isNaN(response)) {
			Email('boss');
			Email('applicant');
			DelayedAlert(response, 'La demande d\'engagement financier supplémentaire à bien été envoyée !');
		} else {
			alert(response);
		}
	}).catch(error => {
		alert(error);
	});
}

function Cancel() {
    var AreYouSure = confirm('Merci de confirmer l\'annulation de cette demande d\'intervention technique. Une fois annulée, elle ne pourra pas être réouverte.');
    if (AreYouSure) {
		document.getElementById('status').value = 'canceled';
        Save().then(response => {
            if (!isNaN(response)) {
                Email('team');
                Email('applicant');
                DelayedAlert(response, 'La demande d\'intervention technique a bien été annulée !');
            } else {
                alert(response);
            }
        }).catch(error => {
            alert(error);
        });
    }
}

function Edit() {
    Save().then(response => {
        if (!isNaN(response)) {
            Email('applicant');
            DelayedAlert(response, 'La demande d\'intervention technique a bien été modifiée !');
        } else {
            alert(response);
        }
    }).catch(error => {
        alert(error);
    });
}

function Approve() {
    Save().then(response => {
        if (!isNaN(response)) {
            Email('applicant');
			Email('team');
            DelayedAlert(response, 'La demande d\'engagement financier supplémentaire a bien été acceptée !');
        } else {
            alert(response);
        }
    }).catch(error => {
        alert(error);
    });
}

function Accept() {
    Save().then(response => {
        if (!isNaN(response)) {
            Email('applicant');
            DelayedAlert(response, 'La demande d\'intervention technique a bien été acceptée !');
        } else {
            alert(response);
        }
    }).catch(error => {
        alert(error);
    });
}

function Finish() {
    Save().then(response => {
        if (!isNaN(response)) {
            Email('boss');
            Email('applicant');
            DelayedAlert(response, 'La demande d\'intervention technique a bien été terminée !');
        } else {
            alert(response);
        }
    }).catch(error => {
        alert(error);
    });
}

function Close() {
    Save().then(response => {
        if (!isNaN(response)) {
            Email('applicant');
            DelayedAlert(response, 'La demande d\'intervention technique a bien été clôturée !');
        } else {
            alert(response);
        }
    }).catch(error => {
        alert(error);
    });
}

function Save() {
    return new Promise((resolve, reject) => {
        var ref = document.getElementById('ref').innerHTML;
        var service = document.getElementById('service').value;
        var agent = document.getElementById('agent').value;
        var email = document.getElementById('email').value;
        var date = document.getElementById('date').value;
        var delivery = document.getElementById('delivery').value;
        var description = document.getElementById('description').value;
        var location = document.getElementById('location').value;
        var status = document.getElementById('status').value;
        
        // If 'news' status, save 'pending'
        if(status == "new") status = 'pending';

        //Empty field check
        if (!ref || !service || !agent || !email || !date || !delivery || !description) {
            reject("Tous les champs sont obligatoires !");
            return;
        }
		
		//email validation
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
			reject("L'email renseigné n'est pas valide !");
			return;
		}
		
		//name validation
		if (!/^[A-Za-zÀ-ÖØ-öø-ÿ'’ -]{2,}$/.test(agent.trim())) {
			reject("L'identité de l'agent n'est pas valide !");
			return;
		}

        var data = {
            ref: ref,
            service: service,
            agent: agent,
            email: email,
            date: date,
            delivery: delivery,
            description: description,
            location: location,
            status: status
        };

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "src/php/save.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    resolve(xhr.responseText);
                } else {
                    reject("Erreur lors de l'enregistrement des données !");
                }
            }
        };

        xhr.send(JSON.stringify(data));
    });
}

function Email(TO) {
    return new Promise((resolve, reject) => {
        var ref = document.getElementById('ref').innerHTML;
        var applicant = document.getElementById('email').value;
        var status = document.getElementById('status').value;
        var action = '';
        
        //Email Addresses
        var boss = 'christophe.conceicao@justice.fr';
        var team = 'mickael.alais@justice.fr, djamal.belhadi@justice.fr, hocine.bouhadja@justice.fr';

        //to depending on TO
        var to = '';
        var getTokenPromise;
        if (TO === 'boss') {
            to = boss;
            getTokenPromise = GetToken('boss');
        } else if (TO === 'team') {
            to = team;
            getTokenPromise = GetToken('team');
        } else if (TO === 'all') {
            to = applicant + ', ' + team + ', ' + boss;
            getTokenPromise = Promise.resolve('');
        } else if (TO === 'applicant') {
            to = applicant;
            getTokenPromise = Promise.resolve('');
        }

    
    /*LIFE CYCLLE
		no expense required : pending > accepted (team) > finished (team) > closed (boss) ou canceled
		requiring some expenses : pending > quoted (team) > approved (boss) > finished (team) > closed (boss) ou canceled
	*/

        //Translate Status
        if(status == 'pending'){
            status = 'a été modifiée';
        }else if(status == 'quoted'){
            status = 'a été devisée et est en attente de validation';
        } else if (status == 'accepted' || status == 'approved') {
            status = 'est en cours de réalisation';
        } else if (status == 'finished') {
            status = 'a été réalisée';
        } else if (status == 'closed') {
            status = 'a été archivée';
        } else if (status == 'canceled') {
            status = 'a été annulée';
        } else {
            status = 'a été envoyée'; // For 'new' status
        }
        
        //Personnalize email
        getTokenPromise.then(token => {
            if (TO === 'boss') {
                action = `Vous pouvez maintenant passer à l'étape suivante en cliquant sur 
                          <a href="https://multimedia.enpjj.fr/technique/?ref=${ref}&token=${token}">ce lien</a>`;
            } else if (TO === 'team') {
                action = `Vous pouvez maintenant passer à l'étape suivante en cliquant sur 
                          <a href="https://multimedia.enpjj.fr/technique/?ref=${ref}&token=${token}">ce lien</a>`;
            }

            var subject = 'Demande d\'intervention technique n°' + ref;
            var content = `
                <p>Bonjour,</p>
                <p>La demande d'intervention technique n°<a href="https://multimedia.enpjj.fr/technique/?ref=${ref}">${ref}</a> ${status}.</p>
                <p>${action}</p>
                <p>Merci.</p>
            `;

            var data = {
                to: to,
                from: applicant,
                subject: subject,
                content: content
            };

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "src/php/email.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        console.log(xhr.responseText);
                        resolve(xhr.responseText);
                    } else {
                        reject("Erreur lors de l'envoi de l'email !");
                    }
                }
            };

            xhr.send(JSON.stringify(data));
        }).catch(error => {
            reject(error);
        });
    });
}

function GetToken(type) {
    return new Promise((resolve, reject) => {
        var ref = document.getElementById('ref').innerHTML;

        var data = {
            ref: ref,
            type: type
        };

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "src/php/token.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    resolve(xhr.responseText);
                } else {
                    reject("Erreur lors de la récupération du token !");
                }
            }
        };

        xhr.send(JSON.stringify(data));
    });
}