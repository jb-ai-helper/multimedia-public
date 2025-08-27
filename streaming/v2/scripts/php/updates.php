<?php

    // Get the STR value from the query string
    $stream = $_GET['str'];

    // Check if the received STR is not empty
if (empty($stream)) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

    //Set RAM file
    $ram_file = '../../../ram/'.$_GET['str'].'.txt';

    // Handle incoming POST requests from the Control Page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request and convert it to JSON
    parse_str(file_get_contents("php://input"), $data);
    $message = json_encode($data);

    //Save RAM File (if not empty)
    if (count($data)>0) {
        $open = fopen($ram_file, "w");
        fwrite($open, $message);
        fclose($open);
    }
}

    // Handle SSE connections to the Receiver Page
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['str'])) {
    // Set appropriate SSE headers
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    //Read RAM File
    $open = fopen($ram_file, "r");
    $message = fgets($open);
    fclose($open);
    // Send the message to the SSE client
    $LastModified = filemtime($ram_file);
    //$message = '{"lm":'.$LastModified.','.substr($message,1);
    $message = substr($message, 0, strlen($message)-1).',"lm":'.$LastModified.'}';
    echo "data: $message\n\n";
    ob_flush();
    flush();
}
?>

<?php
