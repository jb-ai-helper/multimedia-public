document.addEventListener('DOMContentLoaded', function () {
    
    /*window.addEventListener('keydown', (event) => {
        alert('Touche appuyée: ' + event.code);
    });*/
    
    // Récupérer le nom de la vidéo depuis l'URL
    const params = new URLSearchParams(window.location.search);
    const videoName = params.get('video') || 'test';
    const videoElement = document.getElementById('video');

    // Charger la vidéo et gérer les dimensions
    videoElement.src = "src/vid/" + videoName + ".mp4";

    // Gestion de la fin de la vidéo (be kind, rewind)
    videoElement.addEventListener('ended', () => {
        clearTimeout(fadeTimeout);  // Annule tout fondu en cours
        clearTimeout(videoFadeoutTimeout);  // Annule tout fondu de fin en cours

        // Réinitialiser la vidéo immédiatement après la fin pour éviter le saut d'image
        videoElement.currentTime = 0;  // Retourner au début de la vidéo
    });

    // Variables pour gérer l'état de l'utilisateur sur le tapis
    let onMat = false;  // Indique si l'utilisateur est sur le tapis
    let fadeTimeout;  // Pour gérer le fondu et arrêt de la vidéo
    let videoFadeoutTimeout;  // Pour gérer le fondu avant la fin de la vidéo
    let fadeInAudio;  // Intervalle pour le fondu entrant (augmentation du volume)
    let fadeOutAudio;  // Intervalle pour le fondu sortant (réduction du volume)

    // Fonction pour lire et afficher la vidéo (1 seconde de fondu)
    function playVideo() {
        clearTimeout(fadeTimeout);
        clearTimeout(videoFadeoutTimeout);
        clearInterval(fadeOutAudio);
        const videoContainer = document.getElementById('video-container');

        // Transition rapide (1 seconde) pour l'entrée visuelle
        videoContainer.style.transition = "opacity 1s ease-in-out";
        videoContainer.style.opacity = 1;

        // Démarrer la lecture de la vidéo
        videoElement.play();

        // Fondu audio progressif vers le volume maximum
        let fadeDuration = 1000;  // Durée totale du fondu (1 seconde pour correspondre au visuel)
        let fadeStep = 50;  // Intervalle de temps pour chaque étape d'augmentation du volume
        let volumeStep = (1 - videoElement.volume) / (fadeDuration / fadeStep);  // Augmenter progressivement le volume

        fadeInAudio = setInterval(() => {
            if (videoElement.volume < 1) {
                // Vérifier si l'ajout de volumeStep dépasse 1
                if (videoElement.volume + volumeStep > 1) {
                    videoElement.volume = 1;  // Fixer à 1 directement
                } else {
                    videoElement.volume += volumeStep;  // Augmenter progressivement le volume
                }
            } else {
                clearInterval(fadeInAudio);  // Une fois le volume à 1, arrêter l'intervalle
            }
        }, fadeStep);

        // Gestion du fondu à 1 seconde avant la fin de la vidéo
        const fadeDurationBeforeEnd = 1;  // Fondu 1 seconde avant la fin
        videoFadeoutTimeout = setTimeout(() => {
            videoContainer.style.transition = "opacity 1s ease-in-out"; // Revenir à 1 seconde de fondu
            videoContainer.style.opacity = 0;
        }, (videoElement.duration - fadeDurationBeforeEnd) * 1000); // Calculer quand démarrer le fondu
    }

    function stopVideo() {
        clearTimeout(fadeTimeout);
        clearTimeout(videoFadeoutTimeout);
        clearInterval(fadeInAudio);
        const videoContainer = document.getElementById('video-container');

        // Transition lente (5 secondes) pour la sortie visuelle
        videoContainer.style.transition = "opacity 5s ease-in-out";
        videoContainer.style.opacity = 0;  // Démarrer immédiatement le fondu visuel

        // Planifier l'arrêt complet de la vidéo après le fondu de 5 secondes
        let fadeDuration = 5000;  // Durée totale du fondu (5 secondes)
        let fadeStep = 50;  // Intervalle de temps pour chaque étape de réduction du volume
        let volumeStep = videoElement.volume / (fadeDuration / fadeStep);  // Diminuer le volume progressivement

        // Réduire le volume progressivement
        fadeOutAudio = setInterval(() => {
            if (videoElement.volume > 0.05) {
                videoElement.volume -= volumeStep;  // Réduction progressive du volume
            } else {
                clearInterval(fadeOutAudio);  // Une fois le volume presque à 0, arrêter l'intervalle
                videoElement.volume = 0;  // Assurer que le volume est bien à 0
            }
        }, fadeStep);

        // Arrêter la vidéo au bout des 5 secondes
        fadeTimeout = setTimeout(() => {
            clearInterval(fadeOutAudio);  // S'assurer que l'intervalle est bien arrêté
            videoElement.pause();
            videoElement.currentTime = 0;  // Réinitialiser la vidéo au début
            videoElement.volume = 1;  // Réinitialiser le volume pour la prochaine lecture
        }, fadeDuration);
    }

    // Fonction pour surveiller les boutons du tapis
    function checkGamepad() {
        
        // Vérifier si l'API Gamepad est compatible avec le navigateur
        if (!('getGamepads' in navigator)) {
            console.log('API Gamepad non compatible avec ce navigateur.');
            return;  // Arrêter l'exécution si non compatible
        }
        
        const gamepads = navigator.getGamepads();
        
        if (gamepads && gamepads[0]) {
            console.log('Gamepad:', gamepads[0]);
        }        
        
        if (gamepads[0]) {
            const gamepad = gamepads[0];

            // Vérifie si au moins un bouton est pressé
            const isPressed = gamepad.buttons.some(button => button.pressed);

            if (isPressed) {
                if (!onMat) {
                    // Si l'utilisateur n'était pas sur le tapis, on lance la vidéo
                    onMat = true;
                    playVideo();
                }
            } else if (onMat) {
                // Si aucun bouton n'est pressé et que l'utilisateur était sur le tapis, on commence le compte à rebours
                onMat = false;
                stopVideo();
            }
        }

        requestAnimationFrame(checkGamepad);  // Continuer la surveillance
    }

    // Démarrer la surveillance du gamepad
    requestAnimationFrame(checkGamepad);
});