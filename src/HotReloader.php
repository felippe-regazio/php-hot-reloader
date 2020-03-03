<?php

namespace HotReloader;

class HotReloader {

    function __construct ($PHR_WATCHER) {
        $this->PHR_WATCHER = $PHR_WATCHER;
        $this->init();
    }

    public function init () {
        $this->addJSClient();
    }

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