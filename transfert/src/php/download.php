<?php
require_once __DIR__ . '/secret.php';

$directory = __DIR__ . '/../../files';
$userFile  = basename($_GET['file'] ?? '');
$fullPath  = null;

/*────────────────────────
  Recherche du fichier demandé
 ────────────────────────*/
$stem = pathinfo($userFile, PATHINFO_FILENAME);
$ext  = pathinfo($userFile, PATHINFO_EXTENSION);

foreach (glob("$directory/{$stem}__*.{$ext}") as $p) {     // tous nos fichiers ont ce pattern
    $fullPath = $p;
    break;
}

if (!$fullPath || !is_file($fullPath)) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

/*────────────────────────
  Récupération du hash suffixe
 ────────────────────────*/
preg_match('/__(.+)\.[\w]+$/', basename($fullPath), $m);
$storedHash = $m[1] ?? '';

/*────────────────────────
  Calcul du hash de la clé du jour
 ────────────────────────*/
$dailyKey  = getDailyKey();                               // clé interne
$dailyHash = hash('sha256', $dailyKey);                   // hash à comparer

$password  = $_POST['password'] ?? null;

/*────────────────────────
  Cas 1 : suffixe == hash(clé jour)  → pas de mot de passe utilisateur
 ────────────────────────*/
if ($storedHash === $dailyHash && $password === null) {
    $password = $dailyKey;                                // on utilisera cette clé
}

/*────────────────────────
  Tentative de déchiffrement (si $password défini)
 ────────────────────────*/
if ($password !== null) {
    if (hash('sha256', $password) !== $storedHash) {      // hash ne correspond pas
        http_response_code(403);
        exit('Mot de passe incorrect.');
    }

    $data       = file_get_contents($fullPath);
    $iv         = substr($data, 0, 16);
    $ciphertext = substr($data, 16);
    $plain      = openssl_decrypt($ciphertext, 'AES-256-CBC',
                                  $password, OPENSSL_RAW_DATA, $iv);

    if ($plain === false) {
        http_response_code(500);
        exit('Erreur de déchiffrement.');
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $userFile . '"');
    header('Content-Length: ' . strlen($plain));
    echo $plain;
    exit;
}

/*────────────────────────
  Ici : le suffixe NE correspond PAS au hash de la clé du jour
  ⇒ il faut un mot de passe utilisateur
 ────────────────────────*/
/* 1. Appel via fetch() : renvoyer JSON */
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
       && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'password_required']);
    exit;
}

/* 2. Appel direct : prompt HTML/JS */
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Mot de passe requis</title></head>
<body>
<script>
const pass = prompt("Ce fichier est protégé. Entrez le mot de passe :");
if (pass !== null) {
  const f = document.createElement("form");
  f.method = "POST"; f.action = location.href;
  const i = document.createElement("input");
  i.type="hidden"; i.name="password"; i.value=pass;
  f.appendChild(i); document.body.appendChild(f); f.submit();
  setTimeout(() => history.back(), 500);
} else { history.back(); }
</script>
</body>
</html>
