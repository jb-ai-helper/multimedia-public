<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="src/css/catalogue.css" />
    <link rel="icon" href="events/favicon.ico" />
    <title>Catalogue CSV Cleaner</title>
</head>
<body>
    <div id="UploadForm">
        <form action="src/php/generate.php" target="_blank" method="post" enctype="multipart/form-data">
            <label class="button" for="fileToUpload">S&eacute;lectionner un fichier CSV</label><input type="file" name="fileToUpload" id="fileToUpload" />
            <input class="button" type="number" min="2020" max="<?php echo date("Y")+1; ?>" step="1" value="<?php echo date("Y")+1; ?>" name="year" />
            <input class="button" type="submit" value="Convertir" name="submit" />
        </form>
    </div>
</body>
</html>
