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
/*
    // Get the STR value from the query string
    $stream = $_GET['str'];

    // Check if the received STR is not empty
    if (empty($stream)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied');
    }

    //Connect to the database
    $servername = "https://multimedia.enpjj.fr/";
    $username = "multimedia";
    $password = "YWyyjpGrshdYN8Uq";
    $dbname = "communications";
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
    }
    else{
        // Handle incoming POST requests from the Control Page
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get data from POST request and convert it to JSON
            parse_str(file_get_contents("php://input"), $data);
            $message = json_encode($data);
            // prepare and bind
            $stmt = $conn->prepare("INSERT INTO communications (StreamKey, Log) VALUES (?, ?)");
            $stmt->bind_param("ss", $stream, $message);
            $stmt->close();
            $conn->close();
            }

        // Handle SSE connections to the Receiver Page
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['str'])) {
            // Set appropriate SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            
            //Get data from database
            $sql = "SELECT StreamKey, Log FROM MyGuests";
            $result = $conn->query($sql);
            
            //Select Comunication Channel
            if ($result->num_rows > 0){
                // output data of each row
                while($row = $result->fetch_assoc()) {
                if($row["StreamKey"] == $stream) $message = $row["Log"];
                }
                echo "data: $message\n\n";
            }
            else { echo "0 results"; }

            // Send the message to the SSE client
            ob_flush();
            flush();
        }
    }
*/
?>

<?php
/*
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    parse_str(file_get_contents("php://input"), $data);
    $ram_file = '../../../ram/'.$_GET['str'].'.txt';
    $message = json_encode($data);

    //Update the RAM File if POST content
    if(count($data)>0){
        //Save in File
        $open = fopen($ram_file, "w");
        fwrite($open,$message);
        fclose($open);
        }
    //ELSE send SSE message
    else{
        //Read RAM File
        $open = fopen($ram_file, "r");
        $message = fgets($open);
        fclose($open);
        echo "data: $message\n\n";
        ob_flush();
        flush();
    }
*/
?>

<?php
    /*
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    session_start(); // Start a session to store the data

    // Get the STR value from the query string
    $stream = $_GET['str'];

    // Check if the received STR is not empty
    if (empty($stream)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied');
    }

    // Check if POST data is available
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get data from POST request and convert it to JSON
        parse_str(file_get_contents("php://input"), $data);
        $message = json_encode($data);

        // Store the message in a session variable
        $_SESSION[$stream] = $message;
    }

    // Check if there is a stored message
    if (isset($_SESSION[$stream])) {
        // Send the last stored message via SSE
        echo "data: {$_SESSION[$stream]}\n\n";
        ob_flush();
        flush();
    } else {
        // If no data is available, you can send a placeholder or empty message
        echo "data: {}\n\n";
        ob_flush();
        flush();
    }
*/
?>
