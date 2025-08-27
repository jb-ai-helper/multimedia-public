<!DOCTYPE html>
<?php
//Include Common Functions
include('../../../src/php/commontools.php');

//Get STR variable from URL
if(!empty($_GET['str'])){ $Stream = $_GET['str']; } else{ $Stream = ''; }

//If no Collection set get it from calendar
if(!empty($_GET['collection'])){ $collection = $_GET['collection']; $title_ref = "0000"; }
else{
    $_GET["info"] = 'collection'; $_GET["cal"] = $Stream; $collection = include('../scripts/php/google-calendar.php');
    $_GET["info"] = 'title_ref'; $_GET["cal"] = $Stream; $title_ref = include('../scripts/php/google-calendar.php');
}

?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Dynamic Graphics Controls (DGC)</title>
	<link rel="stylesheet" href="../scripts/css/ctrl.css">
	<script src="../../../src/js/jquery.js"></script>
	<script src="../scripts/js/ctrl.js"></script>
	<script src="../../../src/js/commontools.js"></script>
</head>

<body class="panel" onLoad="CheckStream()">

<!-- Stream Key -->
<div class="subsection">Stream Key : <?php  if($Stream != "") echo strtoupper($Stream); ?></div>
<div class="spacer"></div>
	
<!-- Global Parameters -->
<div id="GlobalParameters">
	<div class="subsection">Paramètres Globaux</div><input class="hidden" id="stream-key" value="<?php  echo $Stream; ?>">
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="Copyright" type="checkbox" checked><span class="slider round"></span></label>Afficher le bandeau de titre</div>
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="Marianne" type="checkbox" checked><span class="slider round"></span></label>Afficher la Marianne</div>
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="ENPJJ" type="checkbox" checked><span class="slider round"></span></label>Afficher le logo de l'ENPJJ</div>
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="Partenaire" type="checkbox" checked><span class="slider round"></span></label>Afficher le logo du partenaire principal</div>
	<!-- <div class="linked">
		<div class="lock" onDblClick="UnLink(this)">8</div>
	</div> -->

</div>
<div class="spacer"></div>

<!-- Show Runner -->
<div id="ShowRunner">
<div class="subsection">Déroulé de la Production</div>
	<button class="square" id="SR1" onclick="Run('welcome')">Attente</button>
	<button class="square" id="SR2" onclick="Run('intro')">Démarrage</button>
	<button class="square" id="SR3" onclick="Run('stream')">Direct</button>
	<button class="square" id="SR4" onclick="Run('pause')">Pause</button>
	<button class="square" id="SR5" onclick="Run('outro')">Générique</button>
	<button class="square" id="SR5" onclick="SetCounter()">Compteur</button>
</div>
<div class="spacer"></div>

<!-- Title Collections -->
<div id="TitleCollections">
	<div class="subsection">Collections de Titres</div>
	<div class="dropdown full">
		<input autocomplete="off" onclick="DropDown(this)" onkeydown="return CorrectKeys(event)" onkeyup="this.value = this.value.toUpperCase()" id="collection-ref" value="<?php echo $collection ?>">
		<div class="dropdown-content">
		<ul id="predefined-collections">
		<?php

		$dir = "../../data/";
		$folder = opendir ($dir);

		while ($ref = readdir ($folder))
		{
			if (is_dir($dir.$ref) && $ref != "." && $ref != "..")
			{
			$Collections[]=$ref;
			}
		}

		closedir ($folder);    
		unset($ref);

		//Sort Alphabetically
		sort($Collections);
			   
		$nbCollections = count($Collections);
		foreach($Collections as $CollectionRef)
			{			
			echo '<li onclick="Load(this)">'."\r\n";
			echo "\t\t\t".'<span class="ref">'.$CollectionRef.'</span>'."\r\n";
			
            //Allow delition of everything except the ENPJJ collection
			if($CollectionRef != "ENPJJ"){ echo "\t\t\t".'<span style="cursor:pointer !important;" onclick="Delete(\''.$CollectionRef.'\')" class="delete">&#10006;</span></li>'."\r\n"; }
			
			//Stop Tab if last name
			if (--$nbCollections > 0){ echo "\t\t"; }
			}

			?>
		</ul></div>
	</div>
	<div class="spacer"></div>

<!-- Stream Info -->
<?php
	//Set Default Info
	$default_title = 'Titre du Stream';
	$default_subtitle = 'Bienvenue';
	$default_date = date("Y-m-d");
	$default_css = "enpjj";
	$default_style = "ENPJJ";

    //Get StyleFiles
    $dir = '../styles/';
    $folder = opendir ($dir);
    $StyleFiles = array();
    while ($file = readdir ($folder))
    {
        if (strpos($file, '.css') == true)
        {
        array_push($StyleFiles,basename($file,"."."css"));
        }
    }

    closedir ($folder);    
    unset($file);

    //Sort Alphabetically
    sort($StyleFiles);

    //Get StyleInfo
    $StyleInfo = array();
    foreach($StyleFiles as $FileRef)
    {
        $Line = array();
        $FilePath = $dir.$FileRef.".css";
        $OpenFilePath = fopen($FilePath, "r");
        while(!feof($OpenFilePath))
            {
            $Content = fgets($OpenFilePath);
            array_push($Line,$Content);
            }
        fclose($OpenFilePath);

        //Format Name
        $title = '';
        if (array_key_exists(0,$Line)) $title = trim($Line[0]);
        $title = str_replace("/* ","",$title);
        $title = str_replace(" */","",$title);
        //Build Array "StyleInfo"
        $StyleInfo[$FileRef] = $title;
        unset($Line); unset($title);
    }
	
    //If no auto stream scheduled
	if($title_ref == '0000' || $title_ref == '')
        {
		$stream_title = $default_title;
		$stream_subtitle = $default_subtitle;
		$stream_date = $default_date;
        $stream_css = $default_css;
		$stream_style = $default_style;
	   }
	else
	   {
		//Set Automatic Stream Info
		$AutoMetaData = "../../data/".$collection."/streams/".$title_ref.".txt";
		$AutoStream = fopen($AutoMetaData, "r");
        $AutoLine = array();
		while(!feof($AutoStream))
            {
            $AutoContent = fgets($AutoStream);
			array_push($AutoLine,$AutoContent);
            }
		fclose($AutoStream);

		//Prepare TITLE, MESSAGE, DATE and Style
		$stream_title = trim($AutoLine[0]);
		$stream_subtitle = trim($AutoLine[1]);
		$stream_date = trim($AutoLine[2]);
		$stream_css = trim($AutoLine[3]);
        
        if(isset($StyleInfo[$stream_css])){ $stream_style = $StyleInfo[$stream_css]; }
        else{ $stream_css == $default_css; $stream_style = $default_style; }
	   }
?>
<div id="StreamInfo">
	<div class="subsection">Information Générales</div>
    <div class="dropdown full">
        <input onclick="DropDown(this)" id="stream-info-style" data-css="<?php echo $stream_css ?>" placeholder="Style : <?php echo $default_style ?>" value="Style : <?php echo $stream_style ?>" readonly>
		<div class="dropdown-content">
		<ul id="predefined-styles">
        <li><span class="style">ENPJJ</span><span class="css hidden">enpjj</span>
		<?php            
		//Build List
		$nbStyleInfo = count($StyleInfo);
		foreach($StyleInfo as $StyleRef => $StyleName)
            {
            if($StyleName != "ENPJJ") echo '<li><span class="style">'.$StyleName.'</span><span class="css">'.$StyleRef.'</span>'."\r\n";
            //Stop Tab if last style
            if (--$nbStyleInfo > 0){ echo "\t\t"; }
            }
        ?>
		</ul></div>
	</div>
	<script>
	$("ul#predefined-styles li").click(function(){
		cur_style=$(this).children('.style').text();
		cur_file=$(this).children('.css').text();
		$("#stream-info-style:text").val('Style : '+cur_style);
		$("#stream-info-style:text").data("css", cur_file);
	});
	</script>
	<input class="full" id="stream-info-title" maxlength="100" placeholder="<?php echo $default_title ?>" value="<?php echo htmlentities($stream_title) ?>">
	<input class="full" id="stream-info-subtitle" placeholder="<?php echo $default_subtitle ?>" value="<?php echo htmlentities($stream_subtitle) ?>">
	<input class="full" id="stream-info-date" type="date" placeholder="<?php echo $default_date ?>" value="<?php echo $stream_date ?>">

	<div class="dropdown full"><div onclick="DropDown(this)" class="dropdown-menu">Rappel de titre :</div>
		<div class="dropdown-content">
		<ul id="predefined-stream-info">
		<?php
		$dir = "../../data/".$collection."/streams/";
		$folder = opendir ($dir);
        $DataFiles = array();

		while ($file = readdir ($folder))
		{
			if (strpos($file, '.txt') == true)
			{
			array_push($DataFiles,basename($file,"."."txt"));
			}
		}

		closedir ($folder);    
		unset($file);

		$nbData = count($DataFiles);

		//Get StreamInfo
        $StreamInfo = array();
		foreach($DataFiles as $FileRef)
		{
            $Line = array();
			$FilePath = $dir.$FileRef.".txt";
			$OpenFilePath = fopen($FilePath, "r");
			while(!feof($OpenFilePath))
				{
				$Content = fgets($OpenFilePath);
				array_push($Line,$Content);
				}
			fclose($OpenFilePath);
            //Build Multidimentional Array "StreamInfo"
            $title = $subtitle = $date = $style = '';
            if (array_key_exists(0,$Line)) $title = trim($Line[0]);
            if (array_key_exists(1,$Line)) $subtitle = trim($Line[1]);
            if (array_key_exists(2,$Line)) $date = trim($Line[2]);
            if (array_key_exists(3,$Line)) $style = trim($Line[3]);
            $StreamInfo[$FileRef] = array('title' => $title, 'subtitle' => $subtitle, 'date' => $date, 'style' => $style);				
			unset($Line); unset($title); unset($subtitle); unset($date); unset($style);
		}

		//Sort Multidimensional Array according to NAME
		/*uasort($StreamInfo, function($a, $b) { return $a['date'] <=> $b['date']; }); //PHP 7 and above*/
		uasort($StreamInfo, function($a, $b) { if ($a['date'] == $b['date']) { return 0; } return ($a['date'] < $b['date']) ? -1 : 1;});
			
		//Build List
		$nbStreamInfo = count($StreamInfo);
		foreach($StreamInfo as $FileRef => $Info)
		{
			//Prepare TITLE, SUBTITLE, DATE and STYLE
			$title = $Info['title'];
			$subtitle = $Info['subtitle'];
			$date = $Info['date'];
            $css = $Info['style'];
            if(isset($StyleInfo[$css])) $style = $StyleInfo[$css];
            else $style = 'ENPJJ';
			
			//Format Date
			$DateElements = explode("-",$date);
			$formated_date = $DateElements[2]."/".$DateElements[1]."/".$DateElements[0].'&nbsp;-&nbsp;';

            //Format Title & Shorten Title
			$beartitle= strip_tags($title);
			if(strlen($beartitle)>30)
			{
				$cleanend = strrpos($beartitle, " ", -(strlen($beartitle) - 30));
				$shorten_title = substr($beartitle,0,$cleanend);
				$shorten_title = $shorten_title."...";
			}
			else { $shorten_title = strip_tags($title); }
			
			echo "<li>".$formated_date.'<span class="title">'.$shorten_title.'</span>'."\r\n";
			echo "\t\t\t".'<span class="title-full">'.$title.'</span>'."\r\n";
			echo "\t\t\t".'<span class="subtitle">'.$subtitle.'</span>'."\r\n";
			echo "\t\t\t".'<span class="date">'.$date.'</span>'."\r\n";
			echo "\t\t\t".'<span class="file">'.$FileRef.'</span>'."\r\n";
            echo "\t\t\t".'<span class="style">'.$style.'</span>'."\r\n";
            echo "\t\t\t".'<span class="css">'.$css.'</span>'."\r\n";
			echo "\t\t\t".'<span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";

			//Stop Tab if last name
			if (--$nbStreamInfo > 0){ echo "\t\t"; }
			
			//Unset reused variables
			unset($FileRef);
			unset($Line);
			}
			?>
		</ul></div>
	</div>
	<button class="full" id="SI1" onclick="Send(this)">Envoyer</button>
	<button class="full" id="SI0" onclick="Save(this)">Sauvegarder</button>
	</div>
	<div class="spacer"></div>
	<script>
	$("ul#predefined-stream-info li").click(function(){
		cur_title=$(this).children('.title-full').html();
		cur_subtitle=$(this).children('.subtitle').html();
		cur_date=$(this).children('.date').text();
		cur_style=$(this).children('.style').text();
		cur_css=$(this).children('.css').text();
		$("#stream-info-style:text").val('Style : '+cur_style);
		$("#stream-info-style:text").data("css", cur_css);
		$("#stream-info-title:text").val(cur_title);
		$("#stream-info-subtitle:text").val(cur_subtitle);
		$("#stream-info-date").val(cur_date);
	});
	</script>
</div>

<!-- Lower Third -->
<div id="LowerThird">
	<div class="subsection">Tiers Inférieur</div>
	<input class="full" id="lower-thirds-name" placeholder="Prénom NOM" maxlength="75">
	<input class="full" id="lower-thirds-function" placeholder="Fonction" maxlength="145">
	<input class="full" id="lower-thirds-translation" placeholder="Traduction" maxlength="145">
	<div class="dropdown full"><div onclick="DropDown(this)" class="dropdown-menu">Rappel de participants :</div>
		<div class="dropdown-content">
		<ul id="predefined-lower-third">
		<?php

		$dir = "../../data/".$collection."/names/";
		$folder = opendir ($dir);
        
        $NameFiles = array();
		while ($file = readdir ($folder))
		{
			if (strpos($file, '.txt') == true)
			{
			array_push($NameFiles,basename($file,"."."txt"));
			}
		}

		closedir ($folder);    
		unset($file);
		
		$nbName = count($NameFiles);
            
        if($nbName > 0){
            //Sort by NAME
            foreach($NameFiles as $FileRef)
            {
                $Line = array();
                $FilePath = $dir.$FileRef.".txt";
                $OpenFilePath = fopen($FilePath, "r");
                while(!feof($OpenFilePath))
                    {
                    $Content = fgets($OpenFilePath);
                    array_push($Line,$Content);
                    }
                fclose($OpenFilePath);
                //Build Multidimentional Array "NameTag"
                $name = $function = $translation = '';
                if (array_key_exists(0,$Line)) $name = trim($Line[0]);
                if (array_key_exists(1,$Line)) $function = trim($Line[1]);
                if (array_key_exists(2,$Line)) $translation = trim($Line[2]);
                $NameTag[$FileRef] = array('name' => $name, 'function' => $function, 'translation' => $translation);
                unset($Line); unset($name); unset($function); unset($translation);
            }

            //Sort Multidimensional Array according to NAME
            /*uasort($NameTag, function($a, $b) { return $a['name'] <=> $b['name']; }); //PHP 7 and above*/
            uasort($NameTag, function($a, $b) { if ($a['name'] == $b['name']) { return 0; } return ($a['name'] < $b['name']) ? -1 : 1;});

            //Build List
            $nbNameTag = count($NameTag);
            foreach($NameTag as $FileRef => $Participant)
            {
                //Prepare NAMES and FUNCTION
                $name = $Participant['name'];
                $function = $Participant['function'];
                $translation = $Participant['translation'];
                $shorten_function = ShortenFunction($function);

                //Echo list of names
                echo '<li><span class="name">'.$name.'</span>'."\r\n";
                echo "\t\t\t".'<span class="function-short">'.$shorten_function.'</span>'."\r\n";
                echo "\t\t\t".'<span class="function-full">'.$function.'</span>'."\r\n";
                echo "\t\t\t".'<span class="translation">'.$translation.'</span>'."\r\n";
                echo "\t\t\t".'<span class="file">'.$FileRef.'</span>'."\r\n";
                echo "\t\t\t".'<span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";

                //Stop Tab if last name
                if (--$nbNameTag > 0){ echo "\t\t"; }

                //Unset reused variables
                unset($$FileRef);
                unset($Line);
            }
        }

		?>
		</ul></div>
	</div>
	<button class="full" id="LT1" onclick="Show(this)">Envoyer</button>
	<button class="hidden" id="LT2" onclick="Hide(this)">OFF</button>
	<button class="full" id="LT0" onclick="Save(this)">Sauvegarder</button>
	<div class="half_spacer"></div>
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="AutoLT" type="checkbox" checked><span class="slider round"></span></label>Auto OFF (10s)</div>
	<div class="spacer"></div>
	<script>
	$("ul#predefined-lower-third li").click(function(){
		cur_name=$(this).children('.name').text();
		cur_function=$(this).children('.function-full').html();
		cur_translation=$(this).children('.translation').html();
		$("#lower-thirds-name:text").val(cur_name);
		$("#lower-thirds-function:text").val(cur_function);
		$("#lower-thirds-translation:text").val(cur_translation);
	});
	</script>
</div>

<!-- Chapter Transition -->
<div id="ChapterTransition">
	<div class="subsection">Transition Chapitrage</div>
	<textarea class="full" id="chapter-html" placeholder="Titre du chapitre"></textarea>
	<div class="dropdown full"><div onclick="DropDown(this)" class="dropdown-menu">Rappel de chapitre :</div>
		<div class="dropdown-content">
		<ul id="predefined-chapters">
		<?php

		$dir = "../../data/".$collection."/chapters/";
		$folder = opendir ($dir);
        
        $PartFiles = array();
		while ($file = readdir ($folder))
		{
			if (strpos($file, '.txt') == true)
			{
            array_push($PartFiles,$dir.$file);
			}
		}

		closedir ($folder);    
		unset($file);

		$nbPart = count($PartFiles);
            
        if($nbPart > 0){
            foreach($PartFiles as $PartRef)
            {
                $Information = fopen($PartRef, "r");
                while(!feof($Information))
                {
                    $Content = fgets($Information);
                    $Line[] = $Content;
                }
                //Build Multidimentional Array "ScrollingBanner"
                $content = '';
                foreach($Line as $html){ $content.= $html; }
                $content = trim($content);
                $PartTitle[$PartRef] = array('content' => $content);
                fclose($Information);
                unset($Line);
                unset($content);
            }

            //Sort Multidimensional Array according to CONTENT
            /*uasort(PartTitle, function($a, $b) { return $a['content'] <=> $b['content']; }); //PHP 7 and above*/
            uasort($PartTitle, function($a, $b) { if ($a['content'] == $b['content']) { return 0; } return ($a['content'] < $b['content']) ? -1 : 1;});

            $nbPartTitle = count($PartTitle);
            foreach($PartTitle as $PartRef => $Title)
            {
                //Prepare values
                $content = $Title['content'];
                $shorten_content = ShortenTransition($content);
                $file = basename($PartRef,"."."txt");;

                echo '<li><span class="chapter-short">'.$shorten_content.'</span>';
                echo '<span class="chapter-full">'.$content.'</span>';
                echo '<span class="file">'.$file.'</span><span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";
                //Stop Tab if last name
                if (--$nbPartTitle > 0){ echo "\t\t"; }
            }
        }
				

			?>
		</ul></div>
	</div>
	<button class="full" id="CT1" onclick="Show(this)">Envoyer</button>
	<button class="hidden" id="CT2" onclick="Hide(this)">OFF</button>
	<button class="full" id="CT0" onclick="Save(this)">Sauvegarder</button>
	<div class="half_spacer"></div>
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="AutoCT" type="checkbox" checked><span class="slider round"></span></label>Auto OFF (10s)</div>
	<div class="spacer"></div>
	<script>
	$("ul#predefined-chapters li").click(function(){
		cur_text=$(this).children('.chapter-full').html();
		$("textarea#chapter-html").val(cur_text);
	});
	</script>
</div>

<!-- Scrolling Banner -->
<div id="ScrollingBanner">
	<div class="subsection">Bandeau Défilant</div>
	<!-- <div class="dropdown full" style="display: none">
		<div onclick="DropDown(this)" id="scrolling-banner-class" class="dropdown-menu" data-value="none"></div>
		<div class="dropdown-content">
		<ul id="predefined-banner-class">
			<li onclick="SetData('scrolling-banner-class', this)" data-value="chat"></li>
			<li onclick="SetData('scrolling-banner-class', this)" data-value="phone"></li>
			<li onclick="SetData('scrolling-banner-class', this)" data-value="emergency"></li>
		</ul>
		</div>
	</div> -->
	<input class="full" id="scrolling-banner-message" placeholder="Message à faire défiler..." value="">
	<div class="dropdown full"><div onclick="DropDown(this)" class="dropdown-menu">Rappel de bandeaux :</div>
		<div class="dropdown-content">
		<ul id="predefined-scrolling-banner">
		<?php

		$dir = "../../data/".$collection."/banners/";
		$folder = opendir ($dir);
        
        $BannerFiles = array();
		while ($file = readdir ($folder))
			{
			if (strpos($file, '.txt') == true)
				{
				array_push($BannerFiles,$dir.$file);
				}
			}

		closedir ($folder);    
		unset($file);

		$nbBanners = count($BannerFiles);

        if($nbBanners > 0){
            foreach($BannerFiles as $BannerRef)
            {
                $Line = array();
                $Information = fopen($BannerRef, "r");
                while(!feof($Information))
                {
                    $Content = fgets($Information);
                    array_push($Line,$Content);
                }
                //Build Multidimentional Array "ScrollingBanner"
                $content = $class = '';
                if (array_key_exists(0,$Line)) $content = trim($Line[0]);
                if (array_key_exists(1,$Line)) $class = trim($Line[1]);
                $ScrollingBanner[$BannerRef] = array('content' => $content, 'class' => $class);
                fclose($Information);
                unset($Line); unset($content); unset($class);
            }

            //Sort Multidimensional Array according to CONTENT
            /*uasort(ScrollingBanner, function($a, $b) { return $a['content'] <=> $b['content']; }); //PHP 7 and above*/
            uasort($ScrollingBanner, function($a, $b) { if ($a['content'] == $b['content']) { return 0; } return ($a['content'] < $b['content']) ? -1 : 1;});

            $nbScrollingBanner = count($ScrollingBanner);
            foreach($ScrollingBanner as $BannerRef => $Banner)
            {
                //Prepare NAMES and FUNCTION
                $content = $Banner['content'];
                $class = $Banner['class'];
                $shorten_content = ShortenScrollingBanner($content);			
                $file = basename($BannerRef,"."."txt");;

                echo '<li><span class="banner-short">'.$shorten_content.'</span>'."\r\n";
                echo "\t\t\t".'<span class="banner-full">'.$content.'</span>'."\r\n";
                echo "\t\t\t".'<span class="banner-class">'.$class.'</span>'."\r\n";
                echo "\t\t\t".'<span class="file">'.$file.'</span>'."\r\n";
                echo "\t\t\t".'<span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";
                //Stop Tab if last name
                if (--$nbScrollingBanner > 0){ echo "\t\t"; }
            }
        }

			?>
		</ul></div>
	</div>
	<button class="full" id="SB1" onclick="Show(this)">Envoyer</button>
	<button class="hidden" id="SB2" onclick="Hide(this)">OFF</button>
	<button class="full" id="SB0" onclick="Save(this)">Sauvegarder</button>
	<div class="half_spacer"></div>
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="AutoSB" type="checkbox" checked><span class="slider round"></span></label>Auto OFF (60s)</div>
	<div class="spacer"></div>
	<script>
	$("ul#predefined-scrolling-banner li").click(function(){
		cur_message=$(this).children('.banner-full').html();
		cur_class=$(this).children('.banner-class').text();
		$("#scrolling-banner-message:text").val(cur_message);
		//document.getElementById('scrolling-banner-class').setAttribute('data-value', cur_class);
	});
	</script>
</div>

<!-- Count Down -->
<div id="CountDown">
	<div class="subsection">Compte à Rebours (+24h max)</div>
	<input class="half" id="count-down-delai" type="time" step="2" value="00:00:00">
	<button class="half" id="CD1" onclick="SetCountDown(this)">Lancer le compte à rebours</button>
	<div class="spacer"></div>
</div>
</body>
</html>