<?php
// Unified server for handling both GET (EventSource) and POST requests

function error($message, $code = 400) {
    ob_end_clean();
    http_response_code($code);
    header('Content-Type: text/plain');
    die($message);
}

function withLock($cacheDir, $callback) {
    $lockFile = $cacheDir . '/room.lock';
    $lock = fopen($lockFile, 'c+');
    if (!$lock) error('could not create main lock', 500);
    
    flock($lock, LOCK_EX);
    $result = $callback($lock);
    flock($lock, LOCK_UN);
    fclose($lock);
    return $result;
}

// Validate parameters
$unique = $_GET['unique'] ?? error('no identifier');
$room = $_GET['room'] ?? error('no room specified');

if (empty($unique)) error('not a correct identifier');
if (empty($room) || !preg_match('/^[a-zA-Z0-9_-]+$/', $room)) error('invalid room identifier');

// Create cache directory
$cacheDir = 'cache/' . $room;
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true)) {
    error('could not create room directory', 500);
}

// Handle request methods
ob_start();
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': handleGet($unique, $cacheDir); break;
    case 'POST': handlePost($unique, $cacheDir); break;
    default: error('method not allowed', 405);
}

function handleGet($unique, $cacheDir) {
    ob_end_clean();
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Cache-Control');

    // Get message files (excluding own messages)
    $files = array_filter(scandir($cacheDir), function($f) use ($unique) {
        return str_starts_with($f, '_file_') && !str_starts_with($f, '_file_' . $unique);
    });

    if (!empty($files)) {
        withLock($cacheDir, function() use ($files, $cacheDir) {
            foreach ($files as $file) {
                $path = $cacheDir . '/' . $file;
                if (filesize($path) > 0) {
                    $data = file_get_contents($path);
                    echo 'data: ', $data, PHP_EOL;
                    unlink($path);
                    break;
                }
                unlink($path); // Remove empty files
            }
        });
    }

    echo 'retry: 1000', PHP_EOL, PHP_EOL;
}

function handlePost($unique, $cacheDir) {
    withLock($cacheDir, function() use ($unique, $cacheDir) {
        $file = $cacheDir . '/_file_' . $unique;
        $data = file_get_contents('php://input');
        
        if ($data) {
            if (file_exists($file)) fwrite(fopen($file, 'a'), '_MULTIPLEVENTS_');
            file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
        }
    });

    ob_end_clean();
    http_response_code(200);
    header('Content-Type: text/plain');
    echo 'OK';
}
?>
