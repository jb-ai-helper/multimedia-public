<?php
// Error Management
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction pour générer le CSV
function generateCSV($data, $filename = 'publipostage.csv') {
    if (empty($data) || !is_array($data)) {
        throw new InvalidArgumentException('Les données fournies sont invalides ou vides.');
    }

    // En-têtes HTTP pour forcer le téléchargement du fichier CSV
    header('Content-Type: text/csv; charset=windows-1252');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Ouvre un flux en mémoire
    $fp = fopen('php://output', 'w');

    // Récupère les clés des colonnes pour les en-têtes
    $headers = array_map(function ($value) {
        return mb_convert_encoding($value, 'Windows-1252', 'UTF-8');
    }, array_keys($data[0]));
    fputcsv($fp, $headers, ',', '"');

    // Écrit chaque ligne dans le CSV
    foreach ($data as $row) {
        $ansiRow = array_map(function ($value) {
            return mb_convert_encoding($value, 'Windows-1252', 'UTF-8');
        }, $row);
        fputcsv($fp, $ansiRow, ',', '"');
    }

    fclose($fp);
    exit;
}

// Fonction pour convertir une date ou gérer les valeurs spéciales
function convertDate($date) {
    if ($date === "Inscription en continu") {
        return PHP_INT_MAX; // Valeur très grande pour être en dernier
    }
    if ($date === "Date à déterminer") {
        return PHP_INT_MAX - 1; // Juste avant "Inscription en continu"
    }
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        return strtotime("{$parts[2]}-{$parts[1]}-{$parts[0]}"); // Convertir au format UNIX
    }
    return false; // Date invalide
}

// Fonction pour ordonner les lignes du CSV
function sortExcel($Excel) {
    usort($Excel, function ($a, $b) {
        // Remplacer les points par des virgules pour forcer un format numérique correct
        $pftA = floatval(str_replace(',', '.', $a['PFTNumber']));
        $pftB = floatval(str_replace(',', '.', $b['PFTNumber']));

        // 1. Comparer les PFTNumber
        if ($pftA !== $pftB) {
            return $pftA <=> $pftB;
        }

        // 2. Comparer AxisNumber si les PFTNumber sont égaux
        $axisA = intval($a['AxisNumber']);
        $axisA = intval($a['AxisNumber']);
        $axisB = intval($b['AxisNumber']);

        if ($axisA !== $axisB) {
            return $axisA <=> $axisB;
        }

        // 3. Comparer les dates (DLRC) si PFTNumber et AxisNumber sont égaux
        $dlrcA = convertDate($a['DLRC']);
        $dlrcB = convertDate($b['DLRC']);

        return $dlrcA <=> $dlrcB;
    });

    // Ajouter les colonnes AnchorPTF et AnchorAxis
    foreach ($Excel as $i => $row) {
        // AnchorPTF : Nom du PTF si différent de la ligne précédente
        if ($i == 0 || $Excel[$i]['PTFName'] != $Excel[$i - 1]['PTFName']) {
            $Excel[$i]['AnchorPTF'] = $row['PTFName'];
        } else {
            $Excel[$i]['AnchorPTF'] = '';
        }

        // AnchorAxis : Nom de l'Axe avec numéro et "&fsi", si différent de la ligne précédente
        if ($i == 0 || $Excel[$i]['AxisName'] != $Excel[$i - 1]['AxisName']) {
            $Excel[$i]['AnchorAxis'] = $row['AxisNumber'] . "&fsi" . $row['AxisName'];
        } else {
            $Excel[$i]['AnchorAxis'] = '';
        }
    }

    // Retourner les données triées et enrichies
    return $Excel;
}

// Fonction pour détecter l'encodage d'un fichier
function detect_file_encoding($file_path) {
    $content = file_get_contents($file_path);
    $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1, ISO-8859-15, CP1252, ASCII', true);
    return $encoding;
}

// Fonction pour convertir le contenu du fichier en UTF-8
function convert_to_utf8($file_path) {
    $content = file_get_contents($file_path);
    $encoding = detect_file_encoding($file_path);

    if ($encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    }

    // Vérifier et convertir les caractères invalides en UTF-8
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = utf8_encode($content);
    }

    return $content;
}

// Fonction pour assainir les chaînes de caractères
function sanitize_string($string) {
    // Utiliser iconv pour remplacer les caractères invalides par un caractère valide
    $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
    return $string;
}

// Fonction pour remplacer les guillemets par "&gui"
function replaceQuotes($data) {
    foreach ($data as $x => $val) {
        // Remplacer tous les types de guillemets par un marqueur temporaire
        $val = str_replace(['«', '»', '“', '”', '"'], '&TEMPGUI', $val);

        // Supprimer les espaces inutiles autour des guillemets temporaires
        $val = preg_replace('/\s*&TEMPGUI\s*/', '&TEMPGUI', $val);

        // Remplacer les guillemets entourant une expression
        $val = preg_replace('/&TEMPGUI(.*?)&TEMPGUI/', '&gui$1&gui', $val);

        // Gérer les guillemets accolés à des apostrophes ou des caractères
        $val = preg_replace("/([a-zA-Z0-9'])&gui(.*?)&gui/", '$1&gui$2&gui', $val);

        // Gérer les doubles ou imbriqués comme ""empowerment"" ou '"empowerment"'
        $val = preg_replace("/&gui&gui(.*?)&gui&gui/", '&gui$1&gui', $val);

        // Supprimer tout résidu de guillemets temporaires restants
        $val = str_replace('&TEMPGUI', '&gui', $val);

        // Mise à jour de la valeur dans le tableau
        $data[$x] = $val;
    }
    return $data;
}

// Un fichier CSV est envoyé
if(isset($_POST["submit"])) {
    //Hide Form
    $form_visibility = "none";

    $originalFile = $_FILES["fileToUpload"]["tmp_name"];// Open the uploaded file
    $fileContent = convert_to_utf8($originalFile); // Convert file content to UTF-8

    // Write the UTF-8 encoded content to a temporary file
    $tempFile = tmpfile();
    fwrite($tempFile, $fileContent);
    rewind($tempFile);

    // Open CSV
    $metaData = stream_get_meta_data($tempFile);
    $file = fopen($metaData['uri'], 'r');

    if ($file === false) {
        die("Error: Unable to open the uploaded file.");
    }

    $CSV = [] ;
    $i = 0;

    /* ---------- Nettoyage & labélisation ---------- */

    while(($Data = fgetcsv($file, 1000, ",")) !== FALSE) {
        // Convertit chaque valeur en UTF-8 après lecture
        foreach ($Data as &$value) {
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
            $value = sanitize_string($value); // Assainit les données

            // Remplacer les balises BR par &br
            $value = preg_replace("/<br\W*?\/>/", '&br', $value);

            // Remplacer les balises P par &pg
            $value = preg_replace('#</p>#', '&pg', $value);

            // Supprimer les balises UL|OL et remplacer les listes
            $value = preg_replace('#<ol>#', '', $value);
            $value = preg_replace('#</ol>#', '&fin', $value);
            $value = preg_replace('#<ul>#', '', $value);
            $value = preg_replace('#</ul>#', '&fin', $value);
            $value = preg_replace('#<li>#', '&li', $value);
            $value = preg_replace('#<(/li)>#', '&br', $value);
            $value = str_replace("&br&fin", '', $value);

            // Supprimer les balises A tout en gardant le texte et le lien
            $value = preg_replace('/<a\s+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i', '$2 ($1)', $value);

            // Supprimer toutes les balises HTML sauf les commentaires
            $value = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $value);

            // Extraire le lien des commentaires
            $value = preg_replace("/<!--\s*document\.getElementById\('.*?'\)\.innerHTML\s*=\s*'(.*?)';\s*\/\/\s*-->/s", '$1', $value);
            
            //Suppression explicite du contenu des balises script
            $value = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $value);

            // Décoder les entités HTML
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Assainir les chaînes de caractères à nouveau après le décodage
            $value = sanitize_string($value);
        }

        // Premier retrait des espaces multiples
        $Data = preg_replace('/\s\s+/', ' ', $Data);

        // Retirer les espaces dans "Code du stage" (11)
        if (isset($Data[11])) {
            $Data[11] = preg_replace("/\s+/", "", $Data[11]);
        }

        // Gestion des caractères œ
        $Data = str_ireplace("oeuvr", 'œuvr', $Data);
        $Data = str_ireplace("oeil", 'œil', $Data);
        $Data = str_ireplace("coeur", 'cœur', $Data);
        $Data = str_ireplace("choeur", 'chœur', $Data);
        $Data = str_ireplace("foet", 'fœt', $Data);

        // Remplacer les espaces insécables par des espaces réguliers
        $Data = str_replace("\xC2\xA0", ' ', $Data);
        
        // Convertir tous les types d'apostrophes par l'apostrophe standard
        $Data = str_replace(["’", "‵", "‘", "′", "`"], "'", $Data);
        
        // Convertir tous les types d'apostrophes par l'apostrophe standard
        $Data = replaceQuotes($Data);

        // Correction des espaces avant , et . (retirer les espaces)
        $Data = str_replace(" .", '&dot', $Data);
        $Data = str_replace(" ,", '&com', $Data);

        // Deuxième retrait des espaces multiples
        $Data = preg_replace('/\s\s+/', ' ', $Data);

        // Correction des espaces avant ! ; : ? (ajouter un espace insécable)
        $Data = preg_replace('/([a-zA-Z\d])([!\?;:])/', '$1&nbs$2', $Data);

        // Correction des espaces après . , ; (sauf pour les chiffres après , et .)
        $Data = preg_replace('/(;)([a-zA-Z\d])/', '$1 $2', $Data);
        $Data = preg_replace('/(&com;)([a-zA-Z])/', ', $2', $Data);
        $Data = preg_replace('/(&dot;)([a-zA-Z])/', '. $2', $Data);
        
        //Retour des véritables points et virgules
        $Data = str_replace("&com", ',', $Data);
        $Data = str_replace("&dot", '.', $Data);

        // Diverses corrections
        $Data = str_replace("( ", '(', $Data);
        $Data = str_replace(" )", ')', $Data);
        $Data = str_replace(" (s)", '(s)', $Data);
        $Data = preg_replace('/\?+/', '?', $Data);
        $Data = preg_replace('/!+/', '?', $Data);
        $Data = str_replace("http&nbs:", 'http:', $Data);
        $Data = str_replace("https&nbs:", 'https:', $Data);

        // Retirer les &pg finaux
        foreach ($Data as $x => $val) {
            $pg = explode("&pg", $val);
            $pg = array_map('trim', $pg);
            $pg = array_filter($pg);
            $Data[$x] = implode("&pg", $pg);
        }

        // Retirer les &br finaux
        foreach ($Data as $x => $val) {
            $br = explode("&br", $val);
            $br = array_map('trim', $br);
            $br = array_filter($br);
            $Data[$x] = implode("&br", $br);
        }

        // Troisième retrait des espaces multiples
        $Data = preg_replace('/\s\s+/', ' ', $Data);

        // Retrait des espaces de début et de fin
        foreach ($Data as $x => $val){
            $Data[$x] = trim($val); 
        }

        // Vider les données avec seulement &br ou un seul caractère
        foreach ($Data as $x => $val) {
            if(strlen(str_replace("&br", '', $val)) <= 1) {
                $Data[$x] = "";
            }
        }

        // Vérifier et assainir les données après chaque transformation
        foreach ($Data as &$value) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                $value = mb_convert_encoding($value, 'UTF-8', 'auto');
            }
            $value = sanitize_string($value);
        }

        //Get Column Name (first line of the CSV) for the Keys
        if ($i == 0) {
            $ColName = $Data;
        } else {
            //Get the rest of the data from CSV fro the Values
            $CSV[] = $Data;
            $year = $_POST["year"];

            //Get Year
            if (isset($Data[25])) {
                $dateParts = explode('/', $Data[25]);
                if (count($dateParts) === 3) {
                    $DLRC_year = $dateParts[2]; // Obtenir l'année
                } else {
                    // Gestion des erreurs si le format de la date n'est pas correct
                    $DLRC_year = ''; // ou une autre valeur par défaut ou logique d'erreur
                }
            } else {
                // Gestion des erreurs si $Data[25] n'est pas défini
                $DLRC_year = ''; // ou une autre valeur par défaut ou logique d'erreur
            }

            //Get Mounth
            if (isset($Data[25])) {
                $dateParts = explode('/', $Data[25]);
                if (count($dateParts) === 3) {
                    $DLRC_month = $dateParts[1]; // Obtenir le mois
                } else {
                    // Gestion des erreurs si le format de la date n'est pas correct
                    $DLRC_month = ''; // ou une autre valeur par défaut ou logique d'erreur
                }
            } else {
                // Gestion des erreurs si $Data[25] n'est pas défini
                $DLRC_month = ''; // ou une autre valeur par défaut ou logique d'erreur
            }

            //1 = Collone "Edition", 5 = colonne "PTF" et 25 = DLRC
            if (isset($Data[1], $Data[25]) && $Data[1] == $year && ($DLRC_year == $year || ($DLRC_month == "12" && $DLRC_year == $year-1) || $DLRC_year == "")) {
                //Combine ColName (key) with Data (value)
                $Formations[] = array_combine($ColName, $Data);
            }
        }
        $i++;
    }
    fclose($file);

    /* ---------- Mise forme des données utiles ---------- */

    //Create PTF Base
    $ptf[0] = "";
    $ptf[1] = "Site Central";
    $ptf[2] = "Roubaix (Grand-Nord)";
    $ptf[3] = "Rennes (Grand-Ouest)";
    $ptf['4,1'] = "La Plaine Saint-Denis (Ile-de-France)";
    $ptf['4,2'] = "Antilles-Guyane";
    $ptf['4,3'] = "Réunion-Mayotte";
    $ptf[5] = "Nancy (Grand-Est)";
    $ptf[6] = "Dijon (Grand Centre)";
    $ptf[7] = "Lyon (Centre-Est)";
    $ptf[8] = "Bordeaux (Sud Ouest)";
    $ptf[9] = "Toulouse (Sud)";
    $ptf[10]= "Marseille (Sud-Est)";

    $ptf_name[0] = "";
    $ptf_name[1] = "Site Central";
    $ptf_name[2] = "Grand-Nord";
    $ptf_name[3] = "Grand-Ouest";
    $ptf_name[4] = "IDFOM";
    $ptf_name[5] = "Grand-Est";
    $ptf_name[6] = "Grand Centre";
    $ptf_name[7] = "Centre-Est";
    $ptf_name[8] = "Sud Ouest";
    $ptf_name[9] = "Sud";
    $ptf_name[10]= "Sud-Est";
    $ptf_name[11] = "ENM";

    $ptf_city[0] = "Paris";
    $ptf_city[1] = "Roubaix";
    $ptf_city[2] = "Roubaix";
    $ptf_city[3] = "Rennes";
    $ptf_city['4,1'] = "La Plaine Saint-Denis";
    $ptf_city['4,2'] = "Antilles-Guyane";
    $ptf_city['4,3'] = "Réunion-Mayotte";
    $ptf_city[5] = "Nancy";
    $ptf_city[6] = "Dijon";
    $ptf_city[7] = "Lyon";
    $ptf_city[8] = "Bordeaux";
    $ptf_city[9] = "Toulouse";
    $ptf_city[10]= "Marseille";
    $ptf_city[11]= "Bordeaux & Paris";

    //Create Crise Base
    $crise_code = [];

    //Formations "Hybridation"
    $hybridation_code = [];

    //Formations "Eco"
    $eco_code[0] = "06GE01";
    $eco_code[0] = "06SE05";

    //Formations "Placement"
    $placement_code = [];

    //Formations ENM
    $enm_code[0] = "02SC10";

    //Prepare Sessions Variables
    $harmonie_codes = [];

    //Set counter for ENM increment
    $ENMnb = 1;

    //Create new or regroup
    foreach ($Formations as $i => $Data)
    {
        //Initialisation des colonnes
        $Excel[$i]['Code'] = '';
        $Excel[$i]['PFTNumber'] = '';
        $Excel[$i]['@PTFColor'] = '';
        $Excel[$i]['AnchorPTF'] = '';
        $Excel[$i]['PTFName'] = '';
        $Excel[$i]['City'] = '';
        $Excel[$i]['DLRC'] = '';
        $Excel[$i]['Places'] = '';
        $Excel[$i]['Public'] = '';
        $Excel[$i]['Duration'] = '';
        $Excel[$i]['Location'] = '';
        $Excel[$i]['@QRCode'] = '';
        $Excel[$i]['Link'] = '';
        $Excel[$i]['AxisNumber'] = '';
        $Excel[$i]['AnchorAxis'] = '';
        $Excel[$i]['AxisName'] = '';
        $Excel[$i]['Title'] = '';
        $Excel[$i]['Subtitle'] = '';
        $Excel[$i]['Presentation'] = '';
        $Excel[$i]['Objectifs'] = '';
        $Excel[$i]['Content'] = '';
        $Excel[$i]['Methodes'] = '';
        $Excel[$i]['Sessions'] = '';
        $Excel[$i]['SessionsFull'] = '';
        $Excel[$i]['Info'] = '';
        $Excel[$i]['@PictoNational'] = '';
        $Excel[$i]['@PictoCrise'] = '';
        $Excel[$i]['@PictoHybridation'] = '';
        $Excel[$i]['@PictoEco'] = '';
        $Excel[$i]['@PictoPlacement'] = '';
        $Excel[$i]['@PictoENM'] = '';
        $more_info = [];

        foreach($Data as $cat => $info)
        {
            //Add Column related to 'PTF de rattachement'
            if($cat == 'PTF de rattachement') {
                $ptf_numb = array_search($info, $ptf);
                if($ptf_numb == '4,1' || $ptf_numb == '4,2' || $ptf_numb == '4,3') { $numb = 4; 
                }
                elseif($ptf_numb == 0) { $ptf_numb = $numb = 11; 
                }//Change 0 to 11 for ENM
                else { $numb = $ptf_numb; 
                }

                $name = $ptf_name[$numb];
                $city = $ptf_city[$ptf_numb];

                $Excel[$i]['PFTNumber'] = $ptf_numb;
                $Excel[$i]['@PTFColor'] = ".\\CouleursPTF\\".$numb.".ai";
                $Excel[$i]['AnchorPTF'] = '=SI([@PTFName]=E'.($i+1).';"";E'.($i+2).')';
                $Excel[$i]['PTFName'] = $name;
                $Excel[$i]['City'] = $city;
            }

            //Add Column related to 'Ouverture au national'
            if($cat == 'Ouverture au national') {
                if($info == "Ouvert au national") { $national = ".\\TamponsAI\\TamponNational.ai"; 
                }
                else{ $national = ""; 
                }
                $Excel[$i]['@PictoNational'] = $national;
            }

            //Add Column related to 'Code du stage'
            if($cat == 'Code du stage') {
                //Tampon Crise
                if(in_array($info, $crise_code)) { $crise = '.\\TamponsAI\\TamponCrise.ai'; 
                }
                else{ $crise = ""; 
                }
                $Excel[$i]['@PictoCrise'] = $crise;

                //Tampon Hybridation
                if(in_array($info, $hybridation_code)) { $hybridation = '.\\TamponsAI\\TamponHybridation.ai'; 
                }
                else{ $hybridation = ""; 
                }
                $Excel[$i]['@PictoHybridation'] = $hybridation;

                //Tampon Écoresponsabilité
                if(in_array($info, $eco_code)) { $eco = '.\\TamponsAI\\TamponsEco.ai'; 
                }
                else{ $eco = ""; 
                }
                $Excel[$i]['@PictoEco'] = $eco;

                //Tampon Placement
                if(in_array($info, $placement_code)) { $placement = '.\\TamponsAI\\TamponPlacement.ai'; 
                }
                else{ $placement = ""; 
                }
                $Excel[$i]['@PictoPlacement'] = $placement;

                //Tampon ENM
                if(in_array($info, $enm_code)) { $enm = '.\\TamponsAI\\TamponsENM.ai'; 
                }
                else{ $enm = ""; 
                }
                $Excel[$i]['@PictoENM'] = $enm;

                $Excel[$i]['Code'] = $info;                
            }

            //Add Column related to 'Axe' (Theme1)
            if($cat == 'Axe') {
                $theme1_name = substr($info, 4);
                $theme1_number = intval(substr($info, 0, 3));

                if($theme1_name == "") { $theme1_name = "Offres de formation de l'École nationale de la magistrature (ENM)";
                }
                if($theme1_number == 0) { $theme1_number = 9;
                }

                //"&fsi" = \h = Fin du signé imbriqué
                $Excel[$i]['AxisNumber'] = $theme1_number;
                $Excel[$i]['AnchorAxis'] = '=SI([@AxisName]=O'.($i+1).';"";M'.($i+2).'&"&fsi"&O'.($i+2).')';//O = AxisName column; M = AxisNumber column
                $Excel[$i]['AxisName'] = $theme1_name;
            }

            //Add Column related to 'Lien'
            if ($cat == 'Lien') {
                $link = substr($info, 8);
                $node = explode('/', $link)[2];

                $Excel[$i]['@QRCode'] = '.\\CodesQR\\'.$node.'.ai';
                $Excel[$i]['Link'] = $link;
            }

            //Add Column related to 'Places'
            if ($cat == 'Effectif / Nombre de places') {
                if (is_numeric($info)) {
                    $Excel[$i]['Places'] = intval($info);
                } else {
                    $Excel[$i]['Places'] = "--";
                    $more_info[] = "Nombre de places : ".$info;
                }
            }

            //Regroupe additional info
            if ($cat == 'Informations complémentaires' || $cat == 'Informations sur les dates') {
                $more_info[] = $info; 
            }

            if ($cat == 'Pré-requis') {
                if($info != "") {
                    $more_info[] = "Pré-requis : ".$info; 
                }
            }

            //Add Column related to 'Durée'
            if ($cat == 'Durée') {
                // Initialiser $half pour chaque entrée
                $half = 0;

                if(preg_match_all('/(\d+)(?:[\s+]?[hH])/', $info, $duration)) {
                    if(preg_match('/(h30)/', $info)) {
                        $half = ',5'; 
                    } else {
                        $half = '' ; 
                    }
                    $Excel[$i]['Duration'] = intval($duration[0][0]).$half;
                }
                elseif (preg_match_all('/(\d+)(?:[\s+]?[jJ])/', $info, $duration)) {
                    if(preg_match('/(et demi)|(,5)/', $info)) {
                        $half = 3; 
                    } else {
                        $half = 0; 
                    }
                    if (isset($duration[0][0]) && is_numeric($duration[0][0])) {
                        $Excel[$i]['Duration'] = intval($duration[0][0]) * 6 + $half;
                    } else {
                        $Excel[$i]['Duration'] = "--"; // Gestion des erreurs si $duration[0][0] n'est pas valide
                    }
                } elseif (preg_match_all('/^\d+$|^\d+,\d+$/', $info, $duration)) {
                    $Excel[$i]['Duration'] = $duration[0][0];
                } else {
                    $Excel[$i]['Duration'] = "--"; 
                }
            }

            //Get Harmonie Codes            
            if ($cat == 'Sessions') {
                if(preg_match_all('/(\d\s*?){8}/', $info, $codes)) {
                    $Excel[$i]['Harmonie'] = implode(";", preg_replace('/\s+/', '', $codes[0])); 
                }
                else { $Excel[$i]['Harmonie'] = "--"; 
                }
                $Excel[$i]['Harmonie Original'] = $info;
            }

            //Add Column related to 'Dates'        
            if ($cat == 'Dates') {
                $dates = explode(',', $info);
                $dates = array_map('trim', $dates);

                $sessions = [];
                foreach ($dates as $d => $value) {
                    // Vérifier si la valeur est vide
                    if (empty($value)) {
                        $sessions[$d] = "Dates à déterminer.";
                        continue; // Passer au prochain élément
                    }

                    // Supprimer la mention "(Jour entier)" si elle existe
                    $value = str_replace('(Jour entier)', '', $value);
                    $value = trim($value); // Supprime les espaces en début/fin
                    $value = preg_replace('/\s+/', ' ', $value); // Compresse les espaces multiples en un seul

                    // Nettoyer les "&nbs" avant les ":" dans les heures
                    $value = preg_replace('/&nbs:(\d{2})/', ':$1', $value);

                    // Cas 1 : Une seule date avec deux heures (07/10/2025 - 09:30 - 17:00)
                    $value = preg_replace('/^(\d{2}\/\d{2}\/\d{4}) - (\d{2}:\d{2}) - (\d{2}:\d{2})$/',
                                          'Le $1 de $2 à $3', $value);

                    // Cas 2 : Deux dates avec des heures pour chaque date (22/09/2025 - 09:30 - 24/09/2025 - 17:00)
                    $value = preg_replace('/^(\d{2}\/\d{2}\/\d{4}) - (\d{2}:\d{2}) - (\d{2}\/\d{2}\/\d{4}) - (\d{2}:\d{2})$/',
                                          'Du $1 - $2 au $3 - $4', $value);

                    // Cas 3 : Deux dates sans heures (19/06/2025 - 20/06/2025)
                    $value = preg_replace('/^(\d{2}\/\d{2}\/\d{4}) - (\d{2}\/\d{2}\/\d{4})$/',
                                          'Du $1 au $2', $value);

                    // Cas 4 : Une seule date sans heures (04/11/2025)
                    $value = preg_replace('/^(\d{2}\/\d{2}\/\d{4})$/',
                                          'Le $1', $value);

                    // Ajouter "Session X&nbs:"
                    $sessions[$d] = "Session " . ($d + 1) . "&nbs: " . $value;
                }

                // Joindre les sessions avec un séparateur
                $Excel[$i]['Sessions'] = implode('&br', $sessions);
            }

            //Transpose Other Columns                
            if ($cat == 'Titre') {
                $Excel[$i]['Title'] = $info; 
            }

            if ($cat == 'Sous-titre') {
                $Excel[$i]['Subtitle'] = $info; 
            }

            if ($cat == 'Présentation') {
                $Excel[$i]['Presentation'] = preg_replace("/\n/", "&br", $info); 
            }

            if ($cat == 'Objectifs') {
                $Excel[$i]['Objectifs'] = preg_replace("/\n/", "&br", $info); 
            }

            if ($cat == 'Contenu') {
                $Excel[$i]['Content'] = preg_replace("/\n/", "&br", $info); 
            }

            if ($cat == 'Méthodes pédagogiques') {
                $Excel[$i]['Methodes'] = preg_replace("/\n/", "&br", $info); 
            }

            if ($cat == 'Public visé') {
                $Excel[$i]['Public'] = $info; 
            }

            if ($cat == 'Lieu') {
                $Excel[$i]['Location'] = preg_replace("/\n/", "&br", $info); 
            }

            //Transpose DLRC and deal with empty field                
            if ($cat == 'Date limite de réception des candidatures') {
                if($info == "") { $dlrc = "Inscription en continu"; 
                }
                else{ $dlrc = $info; 
                }
                $Excel[$i]['DLRC'] = $dlrc;
            }

            $Excel[$i]['Info'] = implode("&pg", array_filter($more_info));
        }
        //Deal with NO session & NO DRLC
        if($Excel[$i]['Sessions'] == "Dates à déterminer." && $Excel[$i]['DLRC'] == "Inscription en continu") { $Excel[$i]['DLRC'] = "Date à déterminer"; 
        }

        //Change Codes for ENM
        if($ENMnb<10) {
            $ENMnb_fixed = "0".$ENMnb; 
        } else{
            $ENMnb_fixed = $ENMnb; 
        }
        if($Excel[$i]['PFTNumber'] == 0) {
            $Excel[$i]['Code'] = substr($year, 2)."ENM".$ENMnb_fixed; $ENMnb++; 
        }

        //Get previously formated column and populate arrays    
        $codes = explode(';', $Excel[$i]['Harmonie']);
        $texts = explode('&br', $Excel[$i]['Sessions']);
        $sessions_full = $harmonie = '';

        if(count($codes) == 1 && $codes[0] != "--") {
            //Just one Harmonie code
            $sessions_full = $Excel[$i]['Sessions'].'&brNuméro Harmonie : '.$codes[0]; 
        } elseif (count($codes) > 1) {
            //More than one Harmonie code
            if(count($texts) > 1) {
                //More than one line of text
                foreach($texts as $t => $line){
                    if(array_key_exists($t, $codes)) { $harmonie = '&br(Numéro Harmonie : '.$codes[$t].')'; 
                    } else {
                        $harmonie = ' (pas de code Harmonie disponible)'; 
                    }
                    $sessions_full.= $line.$harmonie;
                    //Re-add <BR> between sessions
                    if($t != count($texts)-1) { $sessions_full.= '&br'; 
                    }
                }
            } else{
                //Just one line of text
                foreach($codes as $c => $value){
                    $harmonie.= '&brNuméro Harmonie (Session '.($c+1).') : '.$value;
                }
                $sessions_full = $Excel[$i]['Sessions'].$harmonie;
            }
        } else{
            $sessions_full = $Excel[$i]['Sessions']; 
        }

        //Save Final SessionFull + Reset Variables
        $Excel[$i]['SessionsFull'] = $sessions_full;
        unset($sessions_full);
        unset($harmonie);

        //Rearrange Excel
        $order = array("Code","PFTNumber","@PTFColor","AnchorPTF","PTFName","DLRC","Places","Public","Duration","Location","@QRCode","Link","AxisNumber","AnchorAxis","AxisName","Title","Subtitle","Presentation","Objectifs","Content","Methodes","SessionsFull","Info","@PictoNational","@PictoCrise","@PictoHybridation","@PictoEco","@PictoPlacement","@PictoENM");
        foreach($order as $k) {
            $Output[$k] = $Excel[$i][$k]; 
        }
        $Excel[$i] = $Output;
    }
    // Trier les données et ajouter les colonnes AnchorPTF et AnchorAxis
    $Excel = sortExcel($Excel);
    // Génère le fichier CSV
    generateCSV($Excel);
}

?>
