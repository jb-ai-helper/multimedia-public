<?php
    $ref = $_GET["ref"];
    $file_url = "../../events/".$ref.".png";
    $png = file_get_contents('php://input');

    $file = fopen($file_url, "wb");
    $data = explode(',', $png);
if(fwrite($file, base64_decode($data[1]))) { echo $file_url;
} else { echo "Failure to save PNG";
}
    fclose($file);
?>
