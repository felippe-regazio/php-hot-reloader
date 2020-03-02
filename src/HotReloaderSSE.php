<?php
    
    namespace HotReloader;

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    
	function send_message ($message) {
        echo "data: " . json_encode($message) . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
    }
    
    // --------------------------------------------
    
    require __DIR__ . '/DiffChecker.php';
    
    $Differ = new HotReloaderDiffChecker();
    $app_hash = $Differ->hash();
    
    while (true) {

        $current_hash = $Differ->hash();

        if ($app_hash != $current_hash) {
            $app_hash = $current_hash;
            send_message([
                "hash" => $app_hash,
                "action" => "reload",
                "conn_status" => !connection_aborted (),
                "timestamp" => microtime()
            ]);
        }

        sleep(1);
        if (connection_aborted()) break;
    }