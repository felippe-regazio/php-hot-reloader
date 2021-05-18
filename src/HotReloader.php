<?php

namespace HotReloader;

/**
 * HotReloader : Php Hot Reload - Simple live reload feature for PHP projects
 * HotReloader : Copyright (C) 2018 by Felippe Regazio
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @link       https://github.com/felippe-regazio/php-hot-reloader
 * @copyright  Copyright (c) Felippe Regazio, and releated wrapped files
 * @version    1.0.0
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */
class HotReloader {

    /**
     * Simple constructor method containing the class params.
     * Automatically starts the Hot Reloader.
     *
     * @param $PHR_WATCHR {String} Url to the phrwatcher.php file
     * @return void
     */
    function __construct ($WATCHER_FILE_URL, $PROJECT_DIR='') {
        $this->WATCHER_FILE_URL = $WATCHER_FILE_URL;
        $this->PROJECT_DIR      = $this->standartize_path( realpath( $PROJECT_DIR ) );
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
		 
		register_shutdown_function( function(){ 
			$this->addJSClient();
		});
		
		// if project-dir was set, then user expressed the wish to use the "auto-detected" files list
		if ( !empty ( $this->PROJECT_DIR ) )
		{
			// check if session existed already, not to re-start
			if( session_status() == PHP_SESSION_NONE ){
				$this->initiated=true;
				session_start();
			}
		}
    }

    /**
     * Get included files list for this current execution/page.
	 * However, be noted, that in rare cases, the files list might be large (i.e. Wordpress or other CMS)
	 * and each file-path might be long, then the query might become large and fail
	 * with many web-server solutions (i.e. over Apache's default 8177 char-limit or Nginx defaults).
	 * So, to avoid such occasion, we store the information into "session".
     *
     * @param void
     * @return FilesList {String} Query for inluded files list
     */
    private function includedFilesQuery( ) {
		// if project_dir was empty, we shouldn't continue.
		if ( empty ( $this->PROJECT_DIR ) )
			return '';
		
		$included_files = get_included_files();
		// if fatal/other error stops page execution, then include that file too (as those files are not included in "get_included_files")
		$last_error = error_get_last();
		if ( ! empty( $last_error ) && ! empty( $last_error['file'] ) ){
			$included_files[] = $last_error['file'];
		}
		
		$included_files = array_map( function($filePath) {
			$standartized_path = $this->standartize_path( $filePath ); 
			// We only looking for the files which are inside the project_dir (removing the pre-path)
			if ( stripos( $filePath, $this->PROJECT_DIR) !== false ){
				$relative_path = '.' . substr($standartized_path, strlen($this->PROJECT_DIR) );
				return $relative_path; 
			}
			else {
				return null;
			}
		}, $included_files );
		$included_files = array_filter( $included_files ); //remove empty entries
		$final_string = urlencode ( $this->standartize_path( implode( ',', $included_files ) ) );
		
		$this->random_session_id = rand(1,9999999999) ."_". rand(1, 9999999999);
		$_SESSION[ $this->random_session_id ] = $final_string;
		// if we started session, then end it
		if ( isset ( $this->initiated ) ) {
			session_destroy();
		}
		return "&fileslist=" . $this->random_session_id;
    }
	
	/**
	 * Correct the path completely into native OS path. 
	 * (Sometimes needed when using Windows/Linux modules together).
	 */
	private function standartize_path( $path ){
		return str_replace( ['/','\\'], DIRECTORY_SEPARATOR, $path );
	}
	
 
	
	
    /**
     * Builds the watcher file url (phrwatcher.php by the docs), and
     * add the proper parameters as GET query strings
     *
     * @param void
     * @return URL {String} Url to phrwatcher.php file with params
     */
    private function getWatcherFileURL() {
        return $this->WATCHER_FILE_URL . "?watch=true&reloader_root=" . addslashes(dirname(__DIR__)) . $this->includedFilesQuery();
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

                    const EVENT_SOURCE_ENDPOINT = '<?php echo $this->getWatcherFileURL(); ?>';
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
                        // console.error(error);
                    }

                })();
            </script>
        <?php echo ob_get_clean();
    }
}