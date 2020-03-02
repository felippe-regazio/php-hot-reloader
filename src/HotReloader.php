<?php

namespace HotReloader;

class HotReloader {

    function __construct () { 
        $this->init();
    }

    public function init () {
        $this->addJSClient();
    }

    private function addJSClient () {
        ob_start(); ?>
            <script>
                (function () {

                    const EVENT_SOURCE_ENDPOINT = '//localhost/php-hot-reloader/src/HotReloaderSSE.php';
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
                        // ex: {hash: "2f4880a48d97b9e4b80f350ea25c5615", action: "reload", conn_status: true, timestamp: "0.09070800 1582824410"}
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