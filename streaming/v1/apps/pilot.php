<!DOCTYPE html>
<?php /*Get Link Key*/ if(!empty($_GET['key'])){ $key = $_GET['key']; } else{ $key = ''; } ?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="../src/css/controls.css">
	<script src="../src/js/jquery.js"></script>
	<script src="../src/js/controls.js"></script>
	<title>Dynamic Graphics Controls</title>
</head>

<body class="panel">

<!-- Global Parameters -->
<div id="GlobalParameters">
	<div class="subsection">Paramètres Globaux<?php  if($key != "") echo " (".strtoupper($key).")"; ?></div><input class="hidden" id="stream-key" value="<?php  echo $key; ?>">
	<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="Marianne" type="checkbox" checked><span class="slider round"></span></label>Afficher la Marianne</div>
	<div class="linked">
		<div class="lock" onDblClick="UnLink(this)">8</div>
		<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="ENPJJ" type="checkbox" checked><span class="slider round"></span></label>Afficher le logo de l'ENPJJ</div>
		<div class="parameter"><label class="switch" style="transform: scale(1);"><input onChange="Switch(this)" id="TopBanner" type="checkbox" checked><span class="slider round"></span></label>Afficher le bandeau de titre</div>
	</div>
</div>
<div class="spacer"></div>

<!-- Show Runner -->
<div id="ShowRunner">
	<div class="subsection">Déroulé de la Production</div>
	<button class="full" id="SR0" onclick="Run('welcome')">Accueil</button>
	<button class="full" id="SR1" onclick="Run('intro')">Introduction</button>
	<button class="full" id="SR2" onclick="Run('pause')">Pause</button>
	<button class="full" id="SR3" onclick="Run('outro')">Générique</button>
</div>
<div class="spacer"></div>
	
<!-- Title Collections -->
<div id="TitleCollections">
	<div class="subsection">Collections de Titres</div>
	<div class="dropdown full">
		<input autocomplete="off" onclick="DropDown(this)" onkeydown="return CorrectKeys(event)" onkeyup="this.value = this.value.toUpperCase()" id="collection-ref" value="ENPJJ">
		<div class="dropdown-content">
		<ul id="predefined-collections">
		<?php

		$dir = "../data/";
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
			if($CollectionRef != "ENPJJ"){ echo "\t\t\t".'<span style="cursor:pointer !important;" onclick="Unset(\''.$CollectionRef.'\')" class="delete">&#10006;</span></li>'."\r\n"; }
			
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
	$default_css = "";
	$default_style = "ENPJJ";

    //Get StyleFiles
    $dir = "../styles/";
    $folder = opendir ($dir);

    while ($file = readdir ($folder))
    {
        if (strpos($file, '.css') == true)
        {
        $StyleFiles[]=basename($file,"."."css");
        }
    }

    closedir ($folder);    
    unset($file);

    //Get StyleInfo
    foreach($StyleFiles as $FileRef)
    {
        $FilePath = $dir.$FileRef.".css";
        $OpenFilePath = fopen($FilePath, "r");
        while(!feof($OpenFilePath))
            {
            $Content = fgets($OpenFilePath);
            $Line[] = $Content;

            //Format Name
            $title = trim($Line[0]);
            $title = str_replace("/* ","",$title);
            $title = str_replace(" */","",$title);
            //Build Array "StyleInfo"
            $StyleInfo[$FileRef] = $title;
            }
        fclose($FilePath);
        unset($Line);
        unset($title);
    }
	
    //If no auto stream scheduled
	if($title_ref == '0000')
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
		$AutoMetaData = "../data/".$collection."/streams/".$title_ref.".txt";
		$AutoStream = fopen($AutoMetaData, "r");
		while(!feof($AutoStream))
            {
            $AutoContent = fgets($AutoStream);
			$AutoLine[] = $AutoContent;
            }
		fclose($AutoMetaData);

		//Prepare TITLE, MESSAGE, DATE and Style
		$stream_title = trim($AutoLine[0]);
		$stream_subtitle = trim($AutoLine[1]);
		$stream_date = trim($AutoLine[2]);
		$stream_css = trim($AutoLine[3]);
        
        if($StyleInfo[$stream_css]){ $stream_style = $StyleInfo[$stream_css]; }
        else{ $stream_css == ""; $stream_style = "ENPJJ"; }
	   }
?>
<div id="StreamInfo">
	<div class="subsection">Information Générales</div>
    <div class="dropdown full">
        <input onclick="DropDown(this)" id="stream-info-style" data-css="<?php echo $stream_css ?>" placeholder="Style : <?php echo $default_style ?>" value="Style : <?php echo $stream_style ?>" readonly>
		<div class="dropdown-content">
		<ul id="predefined-styles">
        <li><span class="style">ENPJJ</span><span class="file"></span>
		<?php            
		//Build List
		foreach($StyleInfo as $StyleRef => $StyleName)
            {
            echo '<li><span class="style">'.$StyleName.'</span><span class="css">'.$StyleRef.'</span>'."\r\n";
            //Stop Tab if last style
            if (--$nbStyle > 0){ echo "\t\t"; }
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
		$dir = "../data/".$collection."/streams/";
		$folder = opendir ($dir);

		while ($file = readdir ($folder))
		{
			if (strpos($file, '.txt') == true)
			{
			$DataFiles[]=basename($file,"."."txt");
			}
		}

		closedir ($folder);    
		unset($file);

		$nbData = count($DataFiles);

		//Get StreamInfo
		foreach($DataFiles as $FileRef)
		{
			$FilePath = $dir.$FileRef.".txt";
			$OpenFilePath = fopen($FilePath, "r");
			while(!feof($OpenFilePath))
				{
				$Content = fgets($OpenFilePath);
				$Line[] = $Content;
				//Build Multidimentional Array "StreamInfo"
				$title = trim($Line[0]);
				$subtitle = trim($Line[1]);
				$date = trim($Line[2]);
				$style = trim($Line[3]);
				$StreamInfo[$FileRef] = array('title' => $title, 'subtitle' => $subtitle, 'date' => $date, 'style' => $style);				
				}
			fclose($FilePath);
			unset($Line);
			unset($title);
			unset($subtitle);
			unset($date);
			unset($style);
		}

		//Sort Multidimensional Array according to NAME
		/*uasort($StreamInfo, function($a, $b) { return $a['date'] <=> $b['date']; }); //PHP 7 and above*/
		uasort($StreamInfo, function($a, $b) { if ($a['date'] == $b['date']) { return 0; } return ($a['date'] < $b['date']) ? -1 : 1;});
			
		//Build List
		foreach($StreamInfo as $FileRef => $Info)
		{
			//Prepare TITLE, SUBTITLE, DATE and STYLE
			$title = $Info['title'];
			$subtitle = $Info['subtitle'];
			$date = $Info['date'];
            $css = $Info['style'];
            $style = $StyleInfo[$css];
            
            if($style == ""){ $style = "ENPJJ"; }
			
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
			if (--$nbData > 0){ echo "\t\t"; }
			
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

		$dir = "../data/".$collection."/names/";
		$folder = opendir ($dir);

		while ($file = readdir ($folder))
		{
			if (strpos($file, '.txt') == true)
			{
			$NameFiles[]=basename($file,"."."txt");
			}
		}

		closedir ($folder);    
		unset($file);
		
		$nbName = count($NameFiles);

		//Sort by NAME
		foreach($NameFiles as $FileRef)
		{
			$FilePath = $dir.$FileRef.".txt";
			$OpenFilePath = fopen($FilePath, "r");
			while(!feof($OpenFilePath))
				{
				$Content = fgets($OpenFilePath);
				$Line[] = $Content;
				//Build Multidimentional Array "NameTag"
				$name = trim($Line[0]);
				$function = trim($Line[1]);
				$translation = trim($Line[2]);
				$NameTag[$FileRef] = array('name' => $name, 'function' => $function, 'translation' => $translation);
				}
			fclose($FilePath);
			unset($Line);
			unset($name);
			unset($function);
			unset($translation);
		}

		//Sort Multidimensional Array according to NAME
		/*uasort($NameTag, function($a, $b) { return $a['name'] <=> $b['name']; }); //PHP 7 and above*/
		uasort($NameTag, function($a, $b) { if ($a['name'] == $b['name']) { return 0; } return ($a['name'] < $b['name']) ? -1 : 1;});
			
		//Build List
		foreach($NameTag as $FileRef => $Participant)
		{
			//Prepare NAMES and FUNCTION
			$name = $Participant['name'];
			$function = $Participant['function'];
			$translation = $Participant['translation'];
			
			if(strlen($function)>20)
			{
				$cleanend = strrpos($function, " ", -(strlen($function) - 20));
				$shorten_function = substr($function,0,$cleanend);
				$shorten_function = "(".$shorten_function."...)";
			}
			elseif(strlen($function)!= 0) { $shorten_function = "(".$function.")"; }
			else { $shorten_function = $function; }
			
			//Echo list of names
			echo '<li><span class="name">'.$name.'</span>'."\r\n";
			echo "\t\t\t".'<span class="function-short">'.$shorten_function.'</span>'."\r\n";
			echo "\t\t\t".'<span class="function-full">'.$function.'</span>'."\r\n";
			echo "\t\t\t".'<span class="translation">'.$translation.'</span>'."\r\n";
			echo "\t\t\t".'<span class="file">'.$FileRef.'</span>'."\r\n";
			echo "\t\t\t".'<span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";
			
			//Stop Tab if last name
			if (--$nbName > 0){ echo "\t\t"; }
			
			//Unset reused variables
			unset($$FileRef);
			unset($Line);
		}
		?>
		</ul></div>
	</div>
	<button class="half" id="LT1" onclick="Show(this)">ON</button>
	<button class="half" id="LT2" onclick="Hide(this)">OFF</button>
	<button class="full" id="LT0" onclick="Save(this)">Sauvegarder</button>
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

		$dir = "../data/".$collection."/chapters/";
		$folder = opendir ($dir);

		while ($file = readdir ($folder))
		{
			if (strpos($file, '.txt') == true)
			{
			$PartFiles[]=$dir.$file;
			}
		}

		closedir ($folder);    
		unset($file);

		$nbPart = count($PartFiles);
				
		foreach($PartFiles as $PartRef)
			{
			$Information = fopen($PartRef, "r");
			while(!feof($Information))
				{
				$Content = fgets($Information);
				$Line[] = $Content;
				}
			//Build Multidimentional Array "ScrollingBanner"
			foreach($Line as $html){ $content.= $html; }
			$content = trim($content);
			$PartTitle[$PartRef] = array('content' => $content);
			fclose($PartRef);
			unset($Line);
			unset($content);
			}

		//Sort Multidimensional Array according to CONTENT
		/*uasort(PartTitle, function($a, $b) { return $a['content'] <=> $b['content']; }); //PHP 7 and above*/
		uasort($PartTitle, function($a, $b) { if ($a['content'] == $b['content']) { return 0; } return ($a['content'] < $b['content']) ? -1 : 1;});

		foreach($PartTitle as $PartRef => $Title)
			{
			//Prepare NAMES and FUNCTION
			$content = $Title['content'];
				
			//Setup Preview (shorten to 60 characters, find last space ans add '...')
			$shorten_content = str_replace("<br>", " ", trim($content));
			$shorten_content = trim(strip_tags($shorten_content));
			if($shorten_content == "") { $shorten_content = "[HTML]"; }
			
			if(strlen($shorten_content)>40)
				{
				$cleanend = strrpos($shorten_content, " ", -(strlen($shorten_content) - 40));
				$shorten_content = substr($shorten_content,0,$cleanend);
				$shorten_content = $shorten_content."...";
				}
			
			$file = basename($PartRef,"."."txt");;
			
			echo '<li><span class="chapter-short">'.$shorten_content.'</span>';
			echo '<span class="chapter-full">'.$content.'</span>';
			echo '<span class="file">'.$file.'</span><span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";
			//Stop Tab if last name
			if (--$nbPart > 0){ echo "\t\t"; }
			}

			?>
		</ul></div>
	</div>
	<button class="full" id="CT1" onclick="Transition(this)">Envoyer</button>
	<button class="full" id="CT0" onclick="Save(this)">Sauvegarder</button>
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
	<!-- Display None Starts -->
	<!--<div class="dropdown full" style="display: none">
		<div onclick="DropDown(this)" id="scrolling-banner-class" class="dropdown-menu" data-value="none"></div>
		<div class="dropdown-content">
		<ul id="predefined-banner-class">
			<li onclick="SetData('scrolling-banner-class', this)" data-value="chat"></li>
			<li onclick="SetData('scrolling-banner-class', this)" data-value="phone"></li>
			<li onclick="SetData('scrolling-banner-class', this)" data-value="emergency"></li>
		</ul>
		</div>
	</div>
	 Display None Ends -->
	<input class="full" id="scrolling-banner-message" placeholder="Message à faire défiler..." value="">
	<div class="dropdown full"><div onclick="DropDown(this)" class="dropdown-menu">Rappel de bandeaux :</div>
		<div class="dropdown-content">
		<ul id="predefined-scrolling-banner">
		<?php

		$dir = "../data/".$collection."/banners/";
		$folder = opendir ($dir);

		while ($file = readdir ($folder))
			{
			if (strpos($file, '.txt') == true)
				{
				$BannerFiles[]=$dir.$file;
				}
			}

		closedir ($folder);    
		unset($file);

		$nbBanners = count($BannerFiles);
					
		foreach($BannerFiles as $BannerRef)
			{
			$Information = fopen($BannerRef, "r");
			while(!feof($Information))
				{
				$Content = fgets($Information);
				$Line[] = $Content;
				}
			//Build Multidimentional Array "ScrollingBanner"
			$content = trim($Line[0]);
			$class = trim($Line[1]);
			$ScrollingBanner[$BannerRef] = array('content' => $content, 'class' => $class);
			fclose($BannerRef);
			unset($Line);
			unset($content);
			unset($class);
			}

		//Sort Multidimensional Array according to CONTENT
		/*uasort(ScrollingBanner, function($a, $b) { return $a['content'] <=> $b['content']; }); //PHP 7 and above*/
		uasort($ScrollingBanner, function($a, $b) { if ($a['content'] == $b['content']) { return 0; } return ($a['content'] < $b['content']) ? -1 : 1;});

		foreach($ScrollingBanner as $BannerRef => $Banner)
			{
			//Prepare NAMES and FUNCTION
			$content = $Banner['content'];
			$class = $Banner['class'];
			
			//Setup Preview (shorten to 50 characters, find last space ans add '...')
			$shorten_content = strip_tags($content);
			
			if(strlen($shorten_content)>60)
				{
				$cleanend = strrpos($shorten_content, " ", -(strlen($shorten_content) - 60));
				$shorten_content = substr($shorten_content,0,$cleanend);
				$shorten_content = $shorten_content."...";
				}
			
			$file = basename($BannerRef,"."."txt");;
			
			echo '<li><span class="banner-short">'.$shorten_content.'</span>'."\r\n";
			echo "\t\t\t".'<span class="banner-full">'.$content.'</span>'."\r\n";
			echo "\t\t\t".'<span class="banner-class">'.$class.'</span>'."\r\n";
			echo "\t\t\t".'<span class="file">'.$file.'</span>'."\r\n";
			echo "\t\t\t".'<span onclick="Delete(this)" class="delete">&#10006;</span></li>'."\r\n";
			//Stop Tab if last name
			if (--$nbBanners > 0){ echo "\t\t"; }
			}

			?>
		</ul></div>
	</div>
	<button class="half" id="SB1" onclick="Show(this)">ON</button>
	<button class="half" id="SB2" onclick="Hide(this)">OFF</button>
	<button class="full" id="SB0" onclick="Save(this)">Sauvegarder</button>
	<div class="spacer"></div>
	<script>
	$("ul#predefined-scrolling-banner li").click(function(){
		cur_message=$(this).children('.banner-full').html();
		cur_class=$(this).children('.banner-class').text();
		$("#scrolling-banner-message:text").val(cur_message);
		document.getElementById('scrolling-banner-class').setAttribute('data-value', cur_class);
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