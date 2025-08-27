<?php
if (!empty($_GET['file'])) {
    // Transfert vers le script de téléchargement
    require_once __DIR__ . '/src/php/download.php';
    exit;
}

// Suppression des fichiers expirés (appel en début de page)
require_once __DIR__ . '/src/php/clean.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Transfert de fichiers</title>
	<link rel="stylesheet" href="src/css/transfert.css">
	<script src="src/js/transfert.js" defer></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
	<link rel="icon" href="favicon.ico" />
</head>
<body>
	<main>
		<h1>Zone de transfert</h1>
		<div id="drop-zone" class="drop-zone">
		  <p>Glissez / Sélectionnez vos fichiers ou dossiers</p>
		</div>
		<section id="file-list" class="file-list">
		  <h2>Fichiers hébergés</h2>
		  <ul id="file-items"></ul>
		</section>
		<button id="download-selected" style="display:none; margin-top: 1rem;">
			Télécharger sélection (ZIP)
		</button>
	</main>
</body>
</html>
