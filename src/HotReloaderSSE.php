<?php

    namespace HotReloader;

    /**
     * HotReloader : Php Hot Reload - Simple live reload feature for PHP projects
     * HotReloader : Copyright (C) 2018 by Felippe Regazio
     * Licensed under The MIT License
     * For full copyright and license information, please see the LICENSE.txt
     * Redistributions of files must retain the above copyright notice.
     *
     * THis is the PHP HOT RELOADER SSE SERVER. It will start an unidirectional
     * connection with the client and will notify the client when a change occurs.
     *
     * @link       https://github.com/felippe-regazio/php-hot-reloader
     * @copyright  Copyright (c) Felippe Regazio, and releated wrapped files
     * @version    1.0.0
     * @license    https://opensource.org/licenses/mit-license.php MIT License
     */

	if (!$ENABLED) {
		exit("Not Enabled");	
	}

	if (!in_array($_SERVER['HTTP_HOST'], $ENABLED_HOSTS )) {
		exit(sprintf("%s does not seem to be your development server", $_SERVER['HTTP_HOST']));
    }
		
	if (empty(@$_REQUEST['watch'])) {
		echo "SSE_ADDRESS_OK | PROJECT ROOT: <br/>";
        echo "<b>" . $PROJECT_ROOT . "</b>";
        
		exit(0);
	}

	// Start script
	
    ob_end_clean();
    set_time_limit(0);

    ini_set('auto_detect_line_endings', 1);
    ini_set('mysql.connect_timeout','7200');
    ini_set('max_execution_time', '0');

    header('Cache-Control: no-cache');
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: text/event-stream');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Expose-Headers: X-Events');


	function send_message ($message) {
        echo "data: " . json_encode($message) . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
    }

    require __DIR__ . '/DiffChecker.php';

    // --------------------------------------------

    $Differ = new DiffChecker([
        'ROOT'     => $PROJECT_ROOT,
        'WATCH'    => $WATCH,
        'IGNORE'   => $IGNORE
    ]);

    $app_hash = $Differ->hash();

    // --------------------------------------------

    while (true) {

        if( connection_status() != CONNECTION_NORMAL or connection_aborted() ) {
            break;
        }

        $current_hash = $Differ->hash();

        if ($app_hash != $current_hash) {
            $app_hash = $current_hash;
            send_message([
                "hash" => $app_hash,
                "action" => "reload",
                "conn_status" => !connection_aborted (),
                "timestamp" => microtime()
            ]);
            break;
        }

        sleep(1);
    }

    exit;