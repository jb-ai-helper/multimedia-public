<?php
// /organigramme/src/php/mapping.php

session_start();

if (!isset($_SESSION['ancienGroupes']) || !isset($_SESSION['nouveauOrganigramme'])) {
    header('Location: /organigramme/index.php?message=Session expirée.');
    exit();
}

$ancienOrganigramme = $_SESSION['ancienGroupes'];
$nouveauOrganigramme = $_SESSION['nouveauOrganigramme'];

// Fonction récursive pour extraire, pour chaque groupe, le code, le nom normalisé et le level
function obtenirGroupesInfos($groups, &$infos = []) {
    foreach ($groups as $group) {
        // On accepte les groupes sans code (clé vide)
        $code = isset($group['code']) ? $group['code'] : '';
        $infos[$code] = [
            'name'  => trim(mb_strtolower($group['group'])),
            'level' => isset($group['level']) ? $group['level'] : ''
        ];
        if (!empty($group['subgroups'])) {
            obtenirGroupesInfos($group['subgroups'], $infos);
        }
    }
    return $infos;
}

// Fonction récursive pour extraire les métiers (jobs) en se basant sur leur code et nom
function obtenirCodesJobs($groups, &$codes = []) {
    foreach ($groups as $group) {
        if (!empty($group['jobs'])) {
            foreach ($group['jobs'] as $job) {
                if (isset($job['code'])) {
                    $codes[$job['code']] = trim(mb_strtolower($job['job']));
                }
            }
        }
        if (!empty($group['subgroups'])) {
            obtenirCodesJobs($group['subgroups'], $codes);
        }
    }
    return $codes;
}

$anciensGroupesInfos = obtenirGroupesInfos($ancienOrganigramme['groups']);
$nouveauxGroupesInfos = obtenirGroupesInfos($nouveauOrganigramme['groups']);

$ancienJobs = obtenirCodesJobs($ancienOrganigramme['groups']);
$nouveauxJobs = obtenirCodesJobs($nouveauOrganigramme['groups']);

// On considère qu'un groupe doit être mappé si l’un des cas suivants est vrai :
// 1. Le code ancien n’existe pas dans les nouveaux.
// 2. Le code existe, mais le level diffère.
$groupesASupprimer = [];
foreach ($anciensGroupesInfos as $oldCode => $oldInfo) {
    if (!isset($nouveauxGroupesInfos[$oldCode])) {
        $groupesASupprimer[$oldCode] = $oldInfo['name'] . ' (level ancien : ' . $oldInfo['level'] . ')';
    } else {
        if ($oldInfo['level'] !== $nouveauxGroupesInfos[$oldCode]['level']) {
            $groupesASupprimer[$oldCode] = $oldInfo['name'] 
                . ' (level ancien : ' . $oldInfo['level'] 
                . ', nouveau : ' . $nouveauxGroupesInfos[$oldCode]['level'] . ')';
        }
    }
}

// Pour les jobs, on compare simplement sur le code
$jobsASupprimer = array_diff_key($ancienJobs, $nouveauxJobs);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mapping des groupes et métiers</title>
    <link rel="stylesheet" href="/organigramme/src/css/organigramme.css">
</head>
<body>
    <h1>Mapping des anciens éléments vers les nouveaux</h1>
    <form action="/organigramme/src/php/update.php" method="post">
        <?php if (!empty($groupesASupprimer)): ?>
            <fieldset>
                <legend>Mapping des groupes</legend>
                <?php foreach ($groupesASupprimer as $oldCode => $oldLabel): ?>
                    <div>
                        <label for="mapping_group_<?php echo htmlspecialchars($oldCode); ?>">
                            <?php echo htmlspecialchars($oldLabel . ' (' . $oldCode . ')'); ?> :
                        </label>
                        <select name="mapping_groups[<?php echo htmlspecialchars($oldCode); ?>]" id="mapping_group_<?php echo htmlspecialchars($oldCode); ?>">
                            <option value="">-- Sélectionnez un nouveau groupe --</option>
                            <?php foreach ($nouveauxGroupesInfos as $newCode => $newInfo):
                                // Si le code est le même, on pré-sélectionne cette option,
                                // afin que si seul le level diffère, le mapping soit prérempli.
                                $selected = ($newCode === $oldCode) ? ' selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($newCode); ?>"<?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($newInfo['name'] . ' (' . $newCode . ') - level : ' . $newInfo['level']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>

        <?php if (!empty($jobsASupprimer)): ?>
            <fieldset>
                <legend>Mapping des métiers</legend>
                <?php foreach ($jobsASupprimer as $oldJobCode => $oldJobName): ?>
                    <div>
                        <label for="mapping_job_<?php echo htmlspecialchars($oldJobCode); ?>">
                            <?php echo htmlspecialchars($oldJobName . ' (' . $oldJobCode . ')'); ?> :
                        </label>
                        <select name="mapping_jobs[<?php echo htmlspecialchars($oldJobCode); ?>]" id="mapping_job_<?php echo htmlspecialchars($oldJobCode); ?>">
                            <option value="">-- Sélectionnez un nouveau métier --</option>
                            <?php foreach ($nouveauxJobs as $newJobCode => $newJobName): ?>
                                <option value="<?php echo htmlspecialchars($newJobCode); ?>">
                                    <?php echo htmlspecialchars($newJobName . ' (' . $newJobCode . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>

        <input type="submit" value="Mettre à jour">
    </form>
</body>
</html>
