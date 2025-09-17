<?php
// Ensure no output before headers
ob_start();

// TODO:
// - read all files from the folder, not just the first one
// - look at this one, might demonstrate slightly better: https://shanetully.com/2014/09/a-dead-simple-webrtc-example/

// A unique identifier (not necessary when working with websockets)
if (!isset($_GET['unique'])) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: text/plain');
    die('no identifier');
}
$unique=$_GET['unique'];
if (strlen($unique)==0 /*|| ctype_digit($unique)===false*/) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: text/plain');
    die('not a correct identifier');
}

// Get room ID from URL parameter
if (!isset($_GET['room'])) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: text/plain');
    die('no room specified');
}
$room = $_GET['room'];
if (strlen($room)==0 || !preg_match('/^[a-zA-Z0-9_-]+$/', $room)) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: text/plain');
    die('invalid room identifier');
}

// Create cache directory for this room if it doesn't exist
$cacheDir = 'cache/' . $room;
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0755, true)) {
        ob_end_clean();
        http_response_code(500);
        header('Content-Type: text/plain');
        die('could not create room directory');
    }
}

// Clear any output buffer and set proper headers
ob_end_clean();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

function startsWith($haystack, $needle) {
    return (substr($haystack, 0, strlen($needle) ) === $needle);
}

// Get a list of all files that start with '_file_' except the file containing
// messages that this client has sended itsself ('_file_'.$unique).
$all = array ();
$handle = opendir ( $cacheDir );
if ($handle !== false) {
    while ( false !== ($filename = readdir ( $handle )) ) {
	if (startsWith($filename,'_file_')  && !(startsWith($filename,'_file_' .$unique) )) {
	    $all [] .= $cacheDir . '/' . $filename;
	}
    }
    closedir( $handle );
}

if (count($all)!=0) {

    // A main lock to ensure save safe writing/reading
    $lockFile = $cacheDir . '/room.lock';
    $mainlock = fopen($lockFile,'c+');
    if ($mainlock===false) {
	http_response_code(500);
	header('Content-Type: text/plain');
	die('could not create main lock');
    }
    flock($mainlock, LOCK_EX);

    // show and empty the first file that is not empty
    for ($x=0; $x<count($all); $x++) {
	$filename=$all[$x];

	// prevent sending empty files
	if (filesize($filename)==0) {
	    unlink($filename);
	    continue;
	}

	$file = fopen($filename, 'c+b');
	flock($file, LOCK_SH);
	$fileSize = filesize($filename);
	if ($fileSize > 0) {
		$data = fread($file, $fileSize);
		echo 'data: ', $data, PHP_EOL;
	}
	fclose($file);
	unlink($filename);
	break;
    }

    // Unlock main lock
    flock($mainlock, LOCK_UN);
    fclose($mainlock);
}

echo 'retry: 1000', PHP_EOL, PHP_EOL; // shorten the 3 seconds to 1 sec

?>
