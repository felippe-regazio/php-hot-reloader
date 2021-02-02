<?php

	/**
	 * PHP Hot Reloader Bootstrap File
	 *
	 * This is the PHP HOT RELOADER Bootstrap file. Change the
	 * variables above in according to your needings. This file
	 * must be available to the local application through some
	 * URL. You can test by accessing this file on your browser,
	 * if you see a message SSE_ADDRESS_OK, copy the address,
	 * you gonna need it. If you see an error, please, provide
	 * a URL route to this file.
	 *
	 * @version 1.0.0
	 * @link https://github.com/felippe-regazio/php-hot-reloader
	 */

	/**
	 * This variable tells if the Reloader is enabled or not.
	 * Remember to NEVER deploy or active this feature on prod.
	 */
	$ENABLED = true;


	/**
	 * For additional security, input your development site address 
	 * to recognize in case of accidental migration into production.
	 * By default, only locahost is enabled to run this script.
	 */
	$ENABLED_HOSTS = [
		'::1',
		'localhost',
		'127.0.0.1',
	];

	/**
	 * This variable must contain your project root absolute
	 * path with a trailing slash. The Watch and Ignore paths
	 * will be relative to this one.
	 */
	$PROJECT_ROOT  = __DIR__;

	/**
	 * This variable must contain the list of files/folders
	 * that you want to watch. The application will be reloaded
	 * when detected some change on those references. All the
	 * paths must be relative to $PROJECT_ROOT var.
	 */
	$WATCH = [
		"."
	];

	/**
	 * Here goes the folders/files that you want the Reloader
	 * to ignore. Add only folders/files that are connected to
	 * the paths you added to $WATCH, otherwise, there is no
	 * needing to specify them. All the paths must be relative
	 * to the $PROJECT_ROOT var.
	 */
	$IGNORE = [

	];

	// ---------------------- Dont Edit It ----------------------

	require_once @$_REQUEST["reloader_root"] . "/src/HotReloaderSSE.php"; 