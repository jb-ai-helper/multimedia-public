<?php
session_start();

// Chargement de l'organigramme (pour rattachement et postes)
$organigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme.json';
if (!file_exists($organigrammePath)) {
    die('Le fichier organigramme.json est introuvable.');
}
$jsonContent = file_get_contents($organigrammePath);
$organigrammeData = json_decode($jsonContent, true);
if ($organigrammeData === null && json_last_error() !== JSON_ERROR_NONE) {
    die('Erreur de décodage JSON : ' . json_last_error_msg());
}

// Fonction pour générer le menu déroulant des groupes (rattachement)
function genererOptions($groupes, $niveau = 0) {
    $options = '';
    foreach ($groupes as $groupe) {
        $indentation = str_repeat('&nbsp;', $niveau * 4);
        $codeGroupe = $groupe['code'];
        $nomAffiche = trim($groupe['group']);
        $value = ($codeGroupe !== '') ? $codeGroupe : 'no_code_' . uniqid();
        $options .= '<option value="' . htmlspecialchars($value) . '">' . $indentation . htmlspecialchars($nomAffiche) . '</option>';
        if (!empty($groupe['subgroups'])) {
            $options .= genererOptions($groupe['subgroups'], $niveau + 1);
        }
    }
    return $options;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Photomaton</title>
  <link rel="stylesheet" href="src/css/photomaton.css">
</head>
<body>
  <h1>Photomaton</h1>
  <p><a href="index.php">Retour au Trombinoscope</a></p>
  <form id="formulaire" enctype="multipart/form-data">
    <!-- Prénom -->
    <label for="prenom">Prénom :</label>
    <input type="text" id="prenom" name="prenom" class="step" required>

    <!-- Nom -->
    <div id="nomContainer" style="display:none;">
      <label for="nom">Nom :</label>
      <input type="text" id="nom" name="nom" class="step" required>
    </div>

    <!-- Rattachement -->
    <div id="rattachementContainer" style="display:none;">
      <label for="rattachement">Rattachement :</label>
      <select onChange="updatePosteOptions()" id="rattachement" name="rattachement" class="step" required>
        <option value="">Sélectionnez votre rattachement</option>
        <?php echo genererOptions($organigrammeData['groups']); ?>
      </select>
    </div>

    <!-- Poste -->
    <div id="posteContainer" style="display:none;">
      <label for="poste">Poste :</label>
      <select id="poste" name="poste" class="step" required>
        <option value="">Sélectionnez votre poste</option>
      </select>
    </div>

    <!-- Date d'arrivée & Fiche de poste personnalisée (optionnel) -->
    <div id="dateArriveeContainer" style="display:none;">
      <label for="dateArrivee">Depuis :</label>
      <input type="date" id="dateArrivee" name="dateArrivee" class="step" required>
      <label for="fichePoste">Fiche de poste personnalisée (optionnel, PDF) :</label>
      <input type="file" id="fichePoste" name="fichePoste" accept="application/pdf">
      <span id="fichePosteLink"></span>
    </div>

    <!-- Consentement -->
    <div id="consentementContainer" style="display:none;">
      <label>
        <input onChange="initializePhotomaton()" type="checkbox" id="consentement" name="consentement" class="step">
        Je consens à ce que mon image soit utilisée pour le trombinoscope et l'organigramme.
      </label>
    </div>

    <!-- Section photo (affichée après consentement si la fiche n'existe pas ou pour recadrer) -->
    <div id="photoSection" style="display:none;">
        
        <!-- Conteneur de la photo -->
        <div id="videoContainer" style="position: relative;">
            <video id="video" autoplay playsinline></video>
            <canvas id="canvas" style="display:none;"></canvas>
            <img id="overlay" src="src/img/guide.svg" style="position:absolute; top:0; left:0;">
        </div>
        
        <!-- Boutons de la section photo -->
        <div id="photoButtons">
            <button type="button" id="guideButton">Masquer les guides</button>
            <button type="button" id="captureButton">Prendre la photo</button>
        </div>
        
        <!-- Message d'aide -->
        <div id="messageContainer">
            Tenez-vous droit, face à la lumière.<br>
            Placez votre tête dans la zone verte et vos yeux dans la zone rouge.<br>
            Utilisez la barre d'espace pour prendre la photo si besoin.
        </div>
    </div>

    <!-- Bouton de soumission -->
    <button type="button" id="submitButton" disabled>Envoyer</button>
  </form>
  
  <script src="src/js/photomaton.js"></script>
</body>
</html>
