<?php
    
    namespace HotReloader;

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);    
    
	function send_message ($message) {
        echo "data: " . json_encode($message) . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
    }
    
    // --------------------------------------------
    
    require './Differ.php';
	send_message(new PHPHotReloaderDiffer().diff());

