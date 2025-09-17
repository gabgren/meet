<?php

// A unique identifier (not necessary when working with websockets)
if (!isset($_GET['unique'])) {
    die('no identifier');
}
$unique=$_GET['unique'];
if (strlen($unique)==0 /*|| ctype_digit($unique)===false*/) {
    die('not a correct identifier');
}

// Get room ID from URL parameter
if (!isset($_GET['room'])) {
    die('no room specified');
}
$room = $_GET['room'];
if (strlen($room)==0 || !preg_match('/^[a-zA-Z0-9_-]+$/', $room)) {
    die('invalid room identifier');
}

// Create cache directory for this room if it doesn't exist
$cacheDir = 'cache/' . $room;
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0755, true)) {
        die('could not create room directory');
    }
}

    
// A main lock to ensure save safe writing/reading
$lockFile = $cacheDir . '/room.lock';
$mainlock = fopen($lockFile,'c+');
if ($mainlock===false) {
    die('could not create main lock');
}
flock($mainlock, LOCK_EX);
   
// Add the new message to file
$filename = $cacheDir . '/_file_' . $unique;
$file = fopen($filename,'ab');
if (filesize($filename)!=0) {
    fwrite($file,'_MULTIPLEVENTS_');
}
$posted = file_get_contents('php://input');
if ($posted !== false && strlen($posted) > 0) {
    fwrite($file, $posted);
} else {
    error_log("No data received or empty input");
}
fclose($file);

// Unlock main lock
flock($mainlock,LOCK_UN);
fclose($mainlock);

?>
