<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set RAM file
$file = '/var/www/multimedia.enpj.fr/www/signage/location/'.$_GET['site'].'/json/'.basename($_GET['file']).'.json';

// Handle incoming POST requests from the Control Page
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Check Data Type
    $content_type = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    // Get data from POST request depending on content type
    if ($content_type === 'application/x-www-form-urlencoded'){
        parse_str(file_get_contents("php://input"), $queries);
        $data = json_encode($queries);
    } else {
        $data = file_get_contents("php://input");
    }
    
    // Save RAM File (if not empty)
    if (!empty($data)){
        $open = fopen($file, "w");
        if ($open === false) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to open file for writing"]);
            exit();
        }
        fwrite($open, $data);
        fclose($open);
    }
    
    // Return a success message
    header('Content-Type: application/json');
    echo json_encode(["status" => "success"]);
    exit();
}

// Handle SSE connections to the Receiver Page
if ($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Set appropriate SSE headers
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    // Read RAM File
    $open = fopen($file, "r");
    if ($open === false) {
        echo "data: {\"error\": \"Failed to open file\"}\n\n";
        ob_flush();
        flush();
        exit();
    }
    $message = fgets($open);
    fclose($open);
    
    // Send the message to the SSE client
    $LastModified = filemtime($file);
    if($message !== false){
        $message = substr($message, 0, strlen($message) - 1) . ',"lm":' . $LastModified . '}';
        echo "data: $message\n\n";
        ob_flush();
        flush();
    }
}
?>