<?php
// /organigramme/manager.php<br>

session_start();

// Chemin vers le fichier organigramme.json
$organigrammePath = $_SERVER['DOCUMENT_ROOT'] . '/organigramme/src/json/organigramme.json';

// Si le fichier n'existe pas, on le crée avec une structure vide
if (!file_exists($organigrammePath)) {
    $data = ['groups' => []];
    if (!is_dir(dirname($organigrammePath))) {
        mkdir(dirname($organigrammePath), 0777, true);
    }
    file_put_contents($organigrammePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    $jsonContent = file_get_contents($organigrammePath);
    $data = json_decode($jsonContent, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        die('Erreur de décodage JSON : ' . json_last_error_msg());
    }
}

$groupes = isset($data['groups']) ? $data['groups'] : [];
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Organigramme</title>
    <link rel="stylesheet" href="/organigramme/src/css/manager.css">
</head>
<body>
    <h1>Organigramme de l'établissement</h1>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <?php afficherGroupes($groupes); ?>

    <h2>Mettre à jour l'organigramme</h2>
    <form action="/organigramme/src/php/organize.php" method="post" enctype="multipart/form-data">
        <label for="fichierTxt">Sélectionnez le fichier .txt :</label>
        <input type="file" name="fichierTxt" id="fichierTxt" accept=".txt" required>
        <input type="submit" value="Mettre à jour">
    </form>

    <!-- Lien pour télécharger l'organigramme au format .txt -->
    <p><a href="/organigramme/src/php/download.php" target="_blank">Télécharger l'organigramme au format .txt</a></p>

    <!-- Modal pour afficher la liste des métiers -->
    <div id="modal-overlay">
        <div id="modal-content">
            <span id="modal-close">X</span>
            <div id="modal-body"></div>
        </div>
    </div>
    <script src="src/js/manager.js"></script>
    </body>
</html>
<?php
function afficherGroupes($groupes) {
    echo '<ul>';
    foreach ($groupes as $groupe) {
        echo '<li>' . htmlspecialchars($groupe['group']);
        // Affichage du lien [métiers] s'il y a des métiers
        if (!empty($groupe['jobs'])) {
            $jobsJson = htmlspecialchars(json_encode($groupe['jobs']), ENT_QUOTES, 'UTF-8');
            echo ' <a href="#" class="show-jobs" data-jobs=\'' . $jobsJson . '\'>[métiers]</a>';
        }
        if (!empty($groupe['subgroups'])) {
            afficherGroupes($groupe['subgroups']);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>
