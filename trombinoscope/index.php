<?php
// /trombinoscope/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// charger l’organigramme
$jsonPath = $_SERVER['DOCUMENT_ROOT'].'/organigramme/src/json/organigramme.json';
if (!file_exists($jsonPath)) {
    die('organigramme.json introuvable.');
}
$data = json_decode(file_get_contents($jsonPath), true);
if (!$data) {
    die('Erreur JSON : '.json_last_error_msg());
}
$groupes = $data['groups'] ?? [];

// construits les maps nécessaires
function mapJobLevels(array $gs, array &$m = []) {
    foreach ($gs as $g) {
        foreach ($g['jobs'] ?? [] as $j) {
            $m[$j['code']] = (int)$j['level'];
        }
        mapJobLevels($g['subgroups'] ?? [], $m);
    }
    return $m;
}
function mapGroupNames(array $gs, array &$m = []) {
    foreach ($gs as $g) {
        if ($g['code']!=='') {
            $m[$g['code']] = $g['group'];
        }
        mapGroupNames($g['subgroups'] ?? [], $m);
    }
    return $m;
}
function mapJobNames(array $gs, array &$m = []) {
    foreach ($gs as $g) {
        foreach ($g['jobs'] ?? [] as $j) {
            if (preg_match('/^-?\s*(.*?)\s*\(/', $j['job'], $ma)) {
                $m[$j['code']] = trim($ma[1]);
            } else {
                $m[$j['code']] = stripslashes($j['job']);
            }
        }
        mapJobNames($g['subgroups'] ?? [], $m);
    }
    return $m;
}

$jobLevelMap  = mapJobLevels($groupes);
$groupNameMap = mapGroupNames($groupes);
$jobNameMap   = mapJobNames($groupes);

// filtrer par groupe + sous‑groupes
function findGroup(array $gs, string $code) {
    foreach ($gs as $g) {
        if ($g['code'] === $code) return $g;
        if ($r = findGroup($g['subgroups'] ?? [], $code)) {
            return $r;
        }
    }
    return null;
}
function collectCodes(array $g): array {
    $c = [$g['code']];
    foreach ($g['subgroups'] as $s) {
        $c = array_merge($c, collectCodes($s));
    }
    return $c;
}

// lecture des paramètres
$groupeFiltre = $_GET['groupe'] ?? 'ENPJJ';
$vue = $_GET['vue'] ?? 'flat';
$tree = [];

// charger les agents
$agentsDir = $_SERVER['DOCUMENT_ROOT'].'/organigramme/agents/';
$personnes = [];
foreach (glob($agentsDir.'*.xml') as $f) {
    $x = @simplexml_load_file($f);
    if (!$x) continue;
    $pr = (string)$x->prenom;
    $nm = (string)$x->nom;
    $cg = (string)$x->rattachement;
    $cj = (string)$x->poste;
    $lvl= $jobLevelMap[$cj] ?? 0;
    $ref= pathinfo($f,PATHINFO_FILENAME);
    $p  = $_SERVER['DOCUMENT_ROOT'].'/trombinoscope/photos/'.$ref.'.jpg';
    $photo = file_exists($p)
           ? 'photos/'.basename($p)
           : 'src/img/placeholder.svg';
    $personnes[] = [
        'prenom'=>$pr,
        'nom'=>mb_strtoupper($nm,'UTF-8'),
        'codeGroupe'=>$cg,
        'groupe'=>$groupNameMap[$cg]??'Inconnu',
        'poste'=>$jobNameMap[$cj]  ??$cj,
        'photo'=>$photo,
        'level'=>$lvl,
    ];
}

// application du filtre groupe
if ($groupeFiltre!=='') {
    if ($node = findGroup($groupes,$groupeFiltre)) {
        $valid = collectCodes($node);
    } else {
        $valid = [$groupeFiltre];
    }
    $personnes = array_filter(
        $personnes,
        fn($p)=>in_array($p['codeGroupe'],$valid,true)
    );
}

// préparer pour flat-view
setlocale(LC_COLLATE,'fr_FR.UTF-8');
usort($personnes,fn($a,$b)=>strcoll($a['nom'],$b['nom']));

// préparer pour arbre
$personsByGroup = [];
foreach ($personnes as $p) {
    $personsByGroup[$p['codeGroupe']][] = $p;
}
function buildTree(array $gs, array $byGroup): array {
    $tree = [];
    foreach ($gs as $g) {
        $tree[] = [
            'code'=>$g['code'],
            'group'=>$g['group'],
            'agents'=>$byGroup[$g['code']] ?? [],
            'subgroups'=> buildTree($g['subgroups']??[], $byGroup)
        ];
    }
    return $tree;
}
if ($vue==='arbre') {
    $root = findGroup($groupes,$groupeFiltre);
    $tree = buildTree($root ? [$root] : [], $personsByGroup);
}

function renderTree(array $nodes) {
	foreach ($nodes as $n) {
		// Tri des agents par fonction (ordre alphabétique FR)
        usort($n['agents'], function($a, $b) {
            return strcoll($a['poste'], $b['poste']);
        });
		// chef = agent au level minimal
		$levels = array_column($n['agents'],'level');
		$minL   = $levels ? min($levels) : 0;
		$chefs  = array_filter($n['agents'], fn($a)=>$a['level']===$minL);
		$chef   = $chefs ? array_shift($chefs) : null;
		echo '<div class="group-node">';
			// titre
			echo '<div class="group-title">'.htmlspecialchars($n['group']).'</div>';
			// chef
			if ($chef) {
				echo '<div class="chef" data-level="'. $chef['level'] .'">';
				echo '<img src="'.htmlspecialchars($chef['photo']).'" alt="">';
				echo '<p class="name">'.htmlspecialchars($chef['prenom'].' '.$chef['nom']).'</p>';
				echo '<p class="function">'.htmlspecialchars($chef['poste']).'</p>';
				echo '</div>';
			}
			// enfants
			echo '<div class="children">';
			// agents
			$others = array_filter($n['agents'], fn($a)=>!($a && $a['level']===$minL));
			if ($others) {
				// Trouver le min parmi les enfants uniquement
				$childLevels   = array_column($others, 'level');
				$minCL = $childLevels ? min($childLevels) : $minL;

				foreach ($others as $o) {
					$diff = $o['level'] - $minCL;
					echo '<div class="agent" data-level="'. $o['level'] .'" style="--diff:'. $diff .'">';
					echo '<img src="'.htmlspecialchars($o['photo']).'" alt="">';
					echo '<p class="name">'.htmlspecialchars($o['prenom'].' '.$o['nom']).'</p>';
					echo '<p class="function">'.htmlspecialchars($o['poste']).'</p>';
					echo '</div>';
				}
			}
			// sous‑groupes
			if (!empty($n['subgroups'])) {
				//echo '<div class="subgroups">';
				renderTree($n['subgroups']);
				//echo '</div>';
			}
			echo '</div>';
		echo '</div>';
	}
}

// helper génère options
function genererOptions(array $gs, int $niv=0): string {
    global $groupeFiltre;
    $o='';
    foreach ($gs as $g) {
        $ind=str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$niv);
        $sel=($g['code']===$groupeFiltre)?' selected':'';
        $o.="<option value=\"{$g['code']}\"{$sel}>{$ind}{$g['group']}</option>\n";
        $o.=genererOptions($g['subgroups']??[], $niv+1);
    }
    return $o;
}

// render flat & arbre
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Trombinoscope</title>
	<link rel="stylesheet" href="src/css/trombinoscope.css">
	<link id="print-flat" rel="stylesheet" href="src/css/print-flat.css" media="print" disabled>
	<link id="print-tree" rel="stylesheet" href="src/css/print-tree.css" media="print" disabled>
</head>
<body>
	<div id="title"></div>
	
	<div id="header">
		<h1>Trombinoscope</h1>
		<center><a href="photomaton.php">Prendre une photo</a></center><br />
	</div>

  <div id="controls">
	<label>Groupe :
		<select id="groupe"><?= genererOptions($groupes) ?></select>
	</label>
	<label>Vue :
		<select id="vue">
			<option value="flat" <?= $vue==='flat'?'selected':'' ?>>Liste</option>
			<option value="arbre"<?= $vue==='arbre'?'selected':'' ?>>Arbre</option>
		</select>
	</label>
	<?php
		$lvlArr = array_column($personnes,'level');
		if (count($lvlArr) > 0) {
			$minLvl = min($lvlArr);
			$maxLvl = max($lvlArr);
			$delta  = $maxLvl - $minLvl;
		} else {
			// pas d’agents => pas de curseur
			$minLvl = 0;
			$maxLvl = 0;
			$delta  = 0;
		} ?>
		<label>Niveau de détails :
			<span id="niveau-val"><?= $delta ?></span>
			<input type="range" id="niveau" min="0" max="<?= $delta ?>" value="<?= $delta ?>">
		</label>
  </div>

  <!-- flat view -->
  <div id="flat-view" style="<?= $vue==='flat'?'':'display:none' ?>">
    <?php foreach($personnes as $p): ?>
      <div class="personne" data-level="<?= $p['level'] ?>">
        <img src="<?= htmlspecialchars($p['photo']) ?>" alt="<?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?>">
        <p class="name"><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></p>
        <p class="function"><?= htmlspecialchars($p['poste']) ?></p>
      </div>
    <?php endforeach ?>
  </div>

  <!-- hierarchical view -->
  <div id="hier-view" style="<?= $vue==='arbre'?'':'display:none' ?>">
    <?php renderTree($tree); ?>
  </div>

  <script src="src/js/trombinoscope.js"></script>
</body>
</html>
