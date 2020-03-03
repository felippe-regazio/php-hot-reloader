<?php

    namespace HotReloader;

    /**
     * HotReloader : Php Hot Reload - Simple live reload feature for PHP projects
     * HotReloader : Copyright (C) 2018 by Felippe Regazio
     * Licensed under The MIT License
     * Site: https://github.com/felippe-regazio/php-hot-reloader
     * For full copyright and license information, please see the LICENSE.txt
     * Redistributions of files must retain the above copyright notice.
     * 
     * THis is the PHP HOT RELOADER SSE SERVER. It will start an unidirectional
     * connection with the client and will notify the client when a change occurs.
     *
     * @copyright     Copyright (c) Felippe Regazio, and releated wrapped files
     * @version       1.0.0
     * @license       https://opensource.org/licenses/mit-license.php MIT License
     */    

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

    require __DIR__ . '/DiffChecker.php';

    // --------------------------------------------

    $Differ = new HotReloaderDiffChecker();

    $differ_cfg = [
        'ROOT'     => $PROJECT_ROOT,
        'WATCH'    => $WATCH,
        'IGNORE'   => $IGNORE
    ];

    $app_hash = $Differ->hash($differ_cfg);

    // --------------------------------------------

    while (true) {

        $current_hash = $Differ->hash($differ_cfg);

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