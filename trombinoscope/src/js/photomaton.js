// /trombinoscope/src/js/photomaton.js

/******************************
 * Chargement de l'organigramme
 ******************************/
var organigramme = { groups: [] };
fetch('/organigramme/src/json/organigramme.json')
  .then(response => response.json())
  .then(data => { 
      organigramme = data;
      console.log("Organigramme chargé :", organigramme);
  })
  .catch(err => console.error(err));

/******************************
 * Fonction de capitalisation
 ******************************/
function capitalizeWords(str) {
  return str.split(" ").map(function(token) {
    return token.split("-").map(function(word) {
      if(word.length === 0) return "";
      return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    }).join("-");
  }).join(" ");
}

/******************************
 * Variables Globales
 ******************************/

var agentFicheExists = false;

/******************************
 * 1. Gestion Progression des champs avec ProgressForm
 ******************************/
function ProgressForm() {
  // Stop la progression si un fiche agent existe déjà
  if (agentFicheExists) return;
  // Récupérer tous les éléments ayant la classe "step" dans l'ordre d'apparition
  var steps = Array.from(document.getElementsByClassName('step'));
  var lastFilledIndex = -1;
  
  // Trouver le dernier index dont la valeur est considérée comme remplie
  for (var i = 0; i < steps.length; i++) {
    var field = steps[i];
    var filled = false;
    if (field.type === "checkbox") {
      filled = field.checked; // pour une checkbox, on considère qu'elle est remplie si cochée
    } else {
      filled = (field.value.trim() !== "");
    }
    if (filled) {
      lastFilledIndex = i;
    }
  }    
  // Masquer les containers pour les étapes après le dernier champ rempli
  for (var i = lastFilledIndex + 1; i < steps.length; i++) {
    var container = document.getElementById(steps[i].id + "Container");
    if (container) {
      container.style.display = "none";
    }
  }
  
  // Si un champ est rempli et qu'il n'est pas le dernier, afficher le container du champ suivant
  if (lastFilledIndex >= 0 && lastFilledIndex < steps.length - 1) {
    var nextContainer = document.getElementById(steps[lastFilledIndex + 1].id + "Container");
    if (nextContainer) {
      nextContainer.style.display = "block";
    }
  }
  checkForm();
}

// Ajout d'un écouteur sur chaque champ avec la classe "step"
document.querySelectorAll('.step').forEach(function(field) {
    if(field.type == 'text'){
        field.addEventListener('keyup', function() {
            this.value = capitalizeWords(this.value);
            ProgressForm();
            });
    } else {
        field.addEventListener('change', ProgressForm);
    }
});

/******************************
 * 2. Gestion de la section photo par états via setPhotomaton(state)
 ******************************/
function initializePhotomaton(){
    let consent = document.getElementById('consentement');
    if (consent.checked){
        setPhotomaton('initial');
    } else {
        setPhotomaton('off');
    }
}

function setPhotomaton(state) {
  // "initial": webcam activée, guides visibles, boutons : "Masquer les guides", "Prendre la photo", "Charger une image"
  if (state === "off") {
    // État off : masquer la section photo et effacer le canvas.
    document.getElementById('photoSection').style.display = 'none';
    // Optionnellement, réinitialiser l'affichage de la webcam pour l'état initial
    document.getElementById('video').style.display = 'none';
    document.getElementById('canvas').style.display = 'none';
    overlay.style.display = 'none';
    // Effacer le contenu du canvas
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    context.clearRect(0, 0, canvas.width, canvas.height);
    // Stop la webcam
    stopVideo();
    photoTaken = false;
  } else if (state === "initial") {
    document.getElementById('photoSection').style.display = 'block';
    document.getElementById('video').style.display = 'block';
    document.getElementById('canvas').style.display = 'none';
    overlay.style.display = 'block';
    guideButton.textContent = "Masquer les guides";
    captureButton.textContent = "Prendre la photo";
    // Démarrage de la webcam
    startVideo();
    photoTaken = false;
  } else if (state === "photo") {
    // "photo": photo affichée, guides masqués, boutons : "Afficher les guides" et "Reprendre la photo"
    document.getElementById('photoSection').style.display = 'block';
    document.getElementById('video').style.display = 'none';
    document.getElementById('canvas').style.display = 'block';
    overlay.style.display = 'none';
    guideButton.textContent = "Afficher les guides";
    captureButton.textContent = "Reprendre la photo";
    // Stop la webcam
    stopVideo();
    photoTaken = true;
  }
  checkForm();
}

/******************************
 * 3. Gestion de la Webcam et des boutons de la section photo
 ******************************/
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const overlay = document.getElementById('overlay');
const guideButton = document.getElementById('guideButton');
const captureButton = document.getElementById('captureButton');
const submitButton = document.getElementById('submitButton');
let photoTaken = false;
let stream = null;

function startVideo() {
  const constraints = {
    video: {
      width: { min: 1280, ideal: 1920 },
      height: { min: 720, ideal: 1080 },
      aspectRatio: 16 / 9
    }
  };
  navigator.mediaDevices.getUserMedia(constraints)
    .then(s => {
      stream = s;
      video.srcObject = stream;
      document.getElementById('messageContainer').style.display = 'block';
      document.addEventListener('keydown', handleKeyDown);
    })
    .catch(error => {
      console.error("Erreur d'accès à la webcam:", error);
      alert("Veuillez autoriser l'accès à la webcam dans votre navigateur.");
    });
}

function stopVideo() {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
  document.getElementById('messageContainer').style.display = 'none';
  document.removeEventListener('keydown', handleKeyDown);
}

guideButton.addEventListener('click', function() {
    if(overlay.style.display == 'block'){
        overlay.style.display = 'none';
        guideButton.textContent = 'Afficher les guides';
    } else{
        overlay.style.display = 'block';
        guideButton.textContent = 'Masquer les guides';
    }
});

captureButton.addEventListener('click', function() {
  if (!photoTaken) {
    takePhoto();
    setPhotomaton("photo");
  } else {
      setPhotomaton("initial");
  }
});

function takePhoto() {
  flashAnimation();
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const context = canvas.getContext('2d');
  context.drawImage(video, 0, 0, canvas.width, canvas.height);
  video.style.display = 'none';
  canvas.style.display = 'block';
  document.getElementById('messageContainer').style.display = 'none';
  document.removeEventListener('keydown', handleKeyDown);
}

function resetPhoto() {
  video.style.display = 'block';
  canvas.style.display = 'none';
  document.getElementById('messageContainer').style.display = 'block';
  document.addEventListener('keydown', handleKeyDown);
}

function flashAnimation() {
  const flash = document.createElement('div');
  flash.style.position = 'fixed';
  flash.style.top = '0';
  flash.style.left = '0';
  flash.style.width = '100%';
  flash.style.height = '100%';
  flash.style.backgroundColor = 'white';
  flash.style.opacity = '1';
  flash.style.zIndex = '1000';
  document.body.appendChild(flash);
  let opacity = 1;
  const fadeOut = setInterval(() => {
    opacity -= 0.1;
    flash.style.opacity = opacity;
    if (opacity <= 0) {
      clearInterval(fadeOut);
      document.body.removeChild(flash);
    }
  }, 50);
}

function handleKeyDown(event) {
  if (event.code === 'Space') {
    event.preventDefault();
    if (!photoTaken) {
      takePhoto();
      setPhotomaton("photo");
      photoTaken = true;
    } else {
      resetPhoto();
      setPhotomaton("initial");
      photoTaken = false;
    }
  }
}
/******************************
 * 4. Soumission du formulaire
 ******************************/
function checkForm(){
    var steps = Array.from(document.getElementsByClassName('step'));
    let missingInfo = 0;
    
    for (var i = 0; i < steps.length; i++) {
        var field = steps[i];
        
        if (field.type === "checkbox"){
            if(field.checked && !photoTaken) missingInfo++;
        } else {
            if(field.value.trim() == "") missingInfo++;
        }
    }    
    if(missingInfo > 0){
        submitButton.disabled = true;
    } else {
        submitButton.disabled = false;
    }
}
submitButton.addEventListener('click', function(event) {
  event.preventDefault();
  
  // Désactiver les boutons pour éviter les doubles soumissions
  submitButton.disabled = true;
  captureButton.disabled = true;
  
  const consentChecked = document.getElementById('consentement').checked;
  
  const prenom = document.getElementById('prenom').value.trim();
  const nom = document.getElementById('nom').value.trim();
  const rattachement = document.getElementById('rattachement').value;
  const poste = document.getElementById('poste').value;
  const dateArrivee = document.getElementById('dateArrivee').value;
  
  const formData = new FormData();
  formData.append('prenom', prenom);
  formData.append('nom', nom);
  formData.append('rattachement', rattachement);
  formData.append('poste', poste);
  formData.append('dateArrivee', dateArrivee);
  
  const fichePosteInput = document.getElementById('fichePoste');
  if (fichePosteInput.files.length > 0) {
    formData.append('fichePoste', fichePosteInput.files[0]);
  }
  
  // Si le consentement est coché et qu'une photo est prise
  if (consentChecked && photoTaken) {
    const dataURL = canvas.toDataURL('image/jpeg');
    formData.append('photo', dataURL);
    }
  
  fetch('src/php/save.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    document.getElementById('formulaire').innerHTML = '<p>' + data +'</p>';
  })
  .catch(error => {
    console.error('Erreur lors de l\'envoi des données:', error);
    alert('Une erreur est survenue lors de l\'enregistrement de votre fiche agent.');
    submitButton.disabled = false;
    captureButton.disabled = false;
  });
});

/******************************
 * 5. Vérification AJAX de l'existence d'une fiche agent
 ******************************/
function checkAgentExistence() {
  const prenomField = document.getElementById('prenom');
  const nomField = document.getElementById('nom');
  const prenom = prenomField.value.trim();
  const nom = nomField.value.trim();
  if (prenom !== '' || nom !== '') {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'src/php/check.php?prenom=' + encodeURIComponent(prenom) + '&nom=' + encodeURIComponent(nom));
    xhr.onload = function() {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        if (response.exists) {
          document.getElementById('rattachement').value = response.rattachement;
          document.getElementById('rattachementContainer').style.display = 'block';
          updatePosteOptions();
          document.getElementById('poste').value = response.poste;
          document.getElementById('posteContainer').style.display = 'block';
          document.getElementById('dateArrivee').value = response.dateArrivee;
          document.getElementById('dateArriveeContainer').style.display = 'block';
          if (response.fichePoste != "") {
            document.getElementById('fichePosteLink').innerHTML = '<a href="/organigramme/postes/' + response.fichePoste + '.pdf" target="_blank">Voir fiche de poste existante</a>';
          }
          // Si une photo existe, charger l'image dans le canvas et coche le consentement
          if (response.photo != "") {
            const img = new Image();
            img.src = '/trombinoscope/photos/' + response.photo + '.jpg';
            img.onload = function() {
              const canvas = document.getElementById('canvas');
              const context = canvas.getContext('2d');
              canvas.width = img.width;
              canvas.height = img.height;
              context.drawImage(img, 0, 0);
              photoTaken = true;
            };
            // Afficher la section photo et mettre à jour l'état
            setPhotomaton("photo");
            // Coche la case consentement
            document.getElementById('consentement').checked = true;
          } else {
            // Sinon, si aucune photo n'existe, laisser la case consentement décochée et la section photo masquée
            document.getElementById('consentement').checked = false;
            setPhotomaton("off");
          }
          document.getElementById('consentementContainer').style.display = 'block';
          submitButton.textContent = "Sauvegarder";
          agentFicheExists = true;
        } else { // Aucun fichier agent trouvé
          if (agentFicheExists) {
            document.getElementById('fichePosteLink').innerHTML = "";
            document.getElementById('rattachement').value = "";
            document.getElementById('poste').value = "";
            document.getElementById('dateArrivee').value = "";
            agentFicheExists = false;
            ProgressForm();
            setPhotomaton("off");
            submitButton.textContent = "Envoyer";
          }
        }
      }
    };
    xhr.send();
  }
}
document.getElementById('prenom').addEventListener('keyup', checkAgentExistence);
document.getElementById('nom').addEventListener('keyup', checkAgentExistence);

// Mise à jour du menu "poste" en fonction du groupe sélectionné
function updatePosteOptions() {
  const rattachement = document.getElementById('rattachement').value;
  const posteSelect = document.getElementById('poste');
  posteSelect.innerHTML = '<option value="">Sélectionnez votre poste</option>';
  if (rattachement !== "") {
    const group = findGroupByCode(rattachement, organigramme.groups);
    if (group && group.jobs && group.jobs.length > 0) {
      const sortedJobs = group.jobs.slice().sort((a, b) => a.job.localeCompare(b.job));
      sortedJobs.forEach(function(job) {
        const opt = document.createElement('option');
        opt.value = job.code;
        opt.textContent = job.job;
        posteSelect.appendChild(opt);
      });
    }
  }
  document.getElementById('posteContainer').style.display = 'block';
}

// Recherche récursive d'un groupe par code
function findGroupByCode(code, groups) {
  for (let i = 0; i < groups.length; i++) {
    if (groups[i].code === code) {
      return groups[i];
    }
    if (groups[i].subgroups && groups[i].subgroups.length > 0) {
      const found = findGroupByCode(code, groups[i].subgroups);
      if (found) return found;
    }
  }
  return null;
}
