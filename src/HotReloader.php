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
 * @copyright     Copyright (c) Felippe Regazio, and releated wrapped files
 * @version       1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
class HotReloader {

    /**
     * Simple constructor method containing the class params.
     * Automatically starts the Hot Reloader.
     *
     * @param $PHR_WATCHR {String} Url to the phrwatcher.php file
     * @return void
     */
    function __construct ($PHR_WATCHER) {
        $this->PHR_WATCHER = $PHR_WATCHER;
        $this->init();
    }

    /**
     * Public method that inits the Reloader. Useful to restart.
     * The init method adds the JS SSE client on the page.
     * 
     * @param void
     * @return void
     */
    public function init () {
        $this->addJSClient();
    }

    /**
     * Flush the JS SSE client to the page. This function is
     * why its better to starts the Reloader on the page footer.
     * 
     * @param void
     * @return void
     */
    private function addJSClient () {
        ob_start(); ?>
            <script>
                (function () {

                    const EVENT_SOURCE_ENDPOINT = '<?=$this->PHR_WATCHER?>?watch=true';
                    const ServerEvents = new EventSource(EVENT_SOURCE_ENDPOINT);

                    ServerEvents.addEventListener('message', e => {
                        const data = JSON.parse(e.data);
                        handleServerMessage(data);
                    });

                    ServerEvents.addEventListener('error', e => {
                        handleServerError(e);
                    });

                    // -------------------------------------

                    handleServerMessage = data => {
                        if (data && data.action && data.action === "reload") {
                            window.location.reload();
                        }
                    }

                    handleServerError = error => {
                        console.error(error);
                    }

                })();
            </script>
        <?php echo ob_get_clean();
    }
}