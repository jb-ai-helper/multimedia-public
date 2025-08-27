//Raccourcis Clavier
var keys = {};
onkeydown = onkeyup = function(e){
	e = e || event;
	e.which = e.which || e.keyCode;
	keys[e.which] = e.type === 'keydown';
    
	if(e.key == "m" && e.altKey == true) { window.location.assign("manager.php"); }//Alt + l
}


// REF does not exist
function MissingRef(){
    alert('Le lien spécifié n\'existe pas !');
}

// Copy Link On Click
function CopyShortLink(what){
    var REF = what.innerHTML;
    var ShortLink = "http://qr.enpjj.fr/"+REF;
    navigator.clipboard.writeText(ShortLink).then(() => {
                        alert(`Lien copié : ${ShortLink}`);
                    }).catch(err => {
                        console.error('Erreur lors de la copie :', err);
                    });
}

// Add Link
function AddLink(){
    var MSG = "Adresse pour la redirection :";
    let PROMPT = prompt(MSG, "");
    if (PROMPT != null && PROMPT != "") {
        var xhr = new XMLHttpRequest();
        var url = 'src/php/create.php';
        var params = 'link=' + encodeURIComponent(PROMPT);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText == "success") {
                    window.location.reload();
                } else {
                    alert(xhr.responseText);
                }
            }
        };

        xhr.send(params);
    }
}

// Edit Link
function EditLink(REF, LINK) {
    var MSG = "Nouvelle URL : \u26A0 Modifier le lien réinitialisera le compteur \u26A0";
    let PROMPT = prompt(MSG, LINK);
    if (PROMPT != null && PROMPT != "") {
        var xhr = new XMLHttpRequest();
        var url = 'src/php/edit.php';
        var params = 'ref=' + encodeURIComponent(REF) + '&link=' + encodeURIComponent(PROMPT);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText == "success") {
                    window.location.reload();
                } else {
                    alert(xhr.responseText);
                }
            }
        };

        xhr.send(params);
    }
}

// Edit Title
function EditTitle(REF, TITLE) {
    var MSG = "Nouveau titre :";
    let PROMPT = prompt(MSG, TITLE);
    if (PROMPT != null && PROMPT != "") {
        var xhr = new XMLHttpRequest();
        var url = 'src/php/edit.php';
        var params = 'ref=' + encodeURIComponent(REF) + '&title=' + encodeURIComponent(PROMPT);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText == "success") {
                    window.location.reload();
                } else {
                    alert(xhr.responseText);
                }
            }
        };

        xhr.send(params);
    }
}

// Edit Code
function EditRef(REF) {
    var MSG = "Nouvelle référence :";
    let PROMPT = prompt(MSG, REF);
    if (PROMPT != null && PROMPT != "") {
        var xhr = new XMLHttpRequest();
        var url = 'src/php/edit.php';
        var params = 'ref=' + encodeURIComponent(REF) + '&rename=' + encodeURIComponent(PROMPT);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText == "success") {
                    window.location.reload();
                } else {
                    alert(xhr.responseText);
                }
            }
        };

        xhr.send(params);
    }
}

// Delete Link
function DeleteLink(REF){
	var MSG = "Êtes-vous certain de vouloir supprimer ce lien ?";
    let CONF = confirm(MSG);
	if(CONF){
        var xhr = new XMLHttpRequest();
        var url = 'src/php/delete.php';
        var params = 'ref=' + encodeURIComponent(REF);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText == "success") {
                    window.location.reload();
                } else {
                    alert(xhr.responseText);
                }
            }
        };

        xhr.send(params);
    }
}