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
 * @version       BETA
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
class PHPHotReloaderDiffer {

  /**
   * Constructor
   *
   * Simple constructor method containing the class params
   *
   * @return void
  */
  function __construct() {
    $this->ROOT      = __DIR__; // the root of directories
    $this->DIFFMODE  = "mtime"; // mtime/md5
    $this->WATCHMODE = "auto";  // auto/includes/added/tags
    $this->IGNORE    = [];      // file or folders to ignore
    $this->ADDED     = [];      // extra files to be watched
  }

  /**
   * Set Root
   *
   * Set the Reloader root path for the add() and the ignore()
   * methods. If, when initialized, there is no Root path, the
   * location of the hotreloader.php file will be setted as root
   *
   * @param String
   * @return void
  */
  public function setRoot($root){
    $this->ROOT = $root;
  }

  /**
   * Set Diff Mode
   *
   * Set the way the Reloader must hash the files and
   * folders. It can be 'mtime' or 'md5'. The mtime is the
   * default mode. Anyway, final unique checksums will always
   * use md5
   *
   * @param String
   * @return void
  */
  public function setDiffMode($mode){
    $this->DIFFMODE = $mode;
  }

  /**
   * Set Watch Mode
   *
   * Set what things will be whatched by the reloader.
   * It haves 4 modes:
   *
   * 1. "auto" - will react to changes in included/required files
   * on the code, added files using add() method, and the script
   * and link tags that incudes js and css files on the code
   *
   * 2. "includes" - will react to changes only in the included/required
   * files on the code, and on the script and link tags
   *
   * 3. "added" - will react to changes only in the files setted from
   * the add() method, and on the script and link tags
   *
   * 4. "tags" - will react to changes only in the script and link tags
   * on the code
   *
   * @param String
   * @return void
   */
  public function setWatchMode($mode){
    $valid_modes = "auto, includes, added, tags";
    if(!in_array($mode, explode(", ", $valid_modes))){
      $mode .= " (Not a Valid Mode. You can use: $valid_modes)";
    }
    $this->WATCHMODE = $mode;
  }

  /**
   * Ignore
   *
   * Set an array of files or folder paths to be ignored by the reloader.
   * The paths and filenames must be relative to the setted ROOT
   *
   * @param Array
   * @return void
   */
  public function ignore($array){
    $this->IGNORE = array_filter(array_unique($array));
  }

  /**
   * Add
   *
   * Set an array of files or folder paths to be watched by the reloader.
   * The files or folders included with add() will trigger a page reload
   * when changed even if they have any link with the current code. Folders
   * are recursively added, so, a change in any file or subfolder will be
   * relevant
   *
   * @param Array
   * @return void
   */
  public function add($array){
    $this->ADDED = array_filter(array_unique($array));
  }

  /**
   * Set Shorthand Method
   *
   * This is a shorthand for all setters, it allows to set parameters to
   * the reloader with one function. The parameters can be:
   *
   * ROOT : set the Root Path
   * DIFFMODE : set the diff mode
   * WATCHMODE : set the watch mode
   * IGNORE : an array of files or folders to ignore
   * ADDED : an array of files or folders to watch
   *
   * @param Array
   * @return void
   */
  public function set($options){
    foreach($options as $key => $val){
     if(isset($this->$key)) $this->$key = $val;
    }
  }

  /**
   * Initiate the reloader
   *
   * Start the Reloader with the setted configurations or the default
   * for the non setted ones. This functions gets the application fingerprint
   * and sends on every current page request (Etag), than add the JS watcher.
   * When this fingerprint changes, means a change on the page, when it happens,
   * a reload will be triggered by the JS wich is always wathing this changes
   *
   * @return void
   */
  public function init(){
    // add the application state hash on the headers
    if(!headers_sent()) {
      $this->addEtagOnHeader();
    }
    // add the JS Watcher
    $this->addJsWatcher();
  }

  /**
   * Generates a hash list of all files added to watch with the method add()
   *
   * This function build the hash/timestamp list of the files and folders which
   * came from the added() method
   *
   * @return Array
  */
  private function getADDEDHashList(){
    $hashes = [];
    // this will hash all files and folders in ADDED array
    if(!empty($this->ADDED)){
      foreach($this->ADDED as $add){
        // create the added path relative to the ROOT const
        $DS = !strpos($this->ROOT, DIRECTORY_SEPARATOR) == strlen($this->ROOT) ? DIRECTORY_SEPARATOR : "";
        $add = $this->ROOT.$DS.$add;
        // do the hash
        if(is_dir($add)){
          if( !$this->willBeIgnored($add) ){
            // if is a dir, hash the entire directory (mtime or md5)
            // the directorie hash is an implode of hashes of all files there
            // the getDirectoryHash always return a md5 checksum of the directory
            $hashes[] = $this->getDirectoryHash($add);
          }
        } else {
          if( file_exists($add) && !$this->willBeIgnored($add) ){
            // if is a file, get the file hash mtime/md5
            $hashes[] = ($this->DIFFMODE == "mtime" ? stat($add)['mtime'] : md5_file($add));
          }
        }
      }
    }
    return $hashes;
  }

  /**
   * Generates a unique hash of an entire directory
   *
   * Generates a hash/timestamp array of all files and folders inside the
   * given directory, than transform this array in a unique md5 checksum
   *
   * @return String
  */
  private function getDirectoryHash($directory){
    $mode = $this->DIFFMODE;
    if (! is_dir($directory)) return false;
    $files = array();
    $dir = dir($directory);
    while (false !== ($file = $dir->read())){
      if ($file != '.' and $file != '..'){
        if (is_dir($directory . DIRECTORY_SEPARATOR . $file)){
          $files[] = $this->hashDirectory($directory . DIRECTORY_SEPARATOR . $file, $mode);
        }
        else{
          $curr_file = $directory.DIRECTORY_SEPARATOR.$file;
          if(!$this->willBeIgnored($curr_file)){
            $files[] = ($mode == "mtime" ? stat($curr_file)['mtime'] : md5_file($curr_file));
          }
        }
      }
    }
    $dir->close();
    return md5(implode("",$files));
  }

  /**
   * Will Be Ignored ?
   *
   * Check if a given path (abs path) must be ignored by the Reloader
   * The given paths are always converted to be relative to the Root
   *
   * @return Boolean
  */
  private function willBeIgnored($file){
    // if the ignore list is not empty
    if( !empty(array_filter($this->IGNORE)) ){
      // check if the file passed existis on the array
      foreach( $this->IGNORE as $ignore ){
        // get the absolute path os files to be ignored
        // the files in IGNORE are relative to $this->ROOT
        $DS = !strpos($this->ROOT, DIRECTORY_SEPARATOR) == strlen($this->ROOT) ? DIRECTORY_SEPARATOR : "";
        $ignore = $this->ROOT.$DS.$ignore;
        //check if must ignore the file (is in ignore or in a folder which is)
        if($file == $ignore || strpos(dirname($file),$ignore) !== false && strpos(dirname($file),$ignore) == 0){
          return true;
        }
      }
    }
    // if the file will not be ignored
    return false;
  }

  /**
   * Create the application state hash
   *
   * Collects all the timestamps/hashes from the included files and from the added()
   * method, and than transforms it into a unique md5 hash. this unique hash is your
   * app fingerprint.
   *
   * @return String
   */
  private function createStateHash(){
    $hashes = [];
    // when in 'tags' watch mode, we send a fake hash just to
    // satisfty the JS watch and not trigger changes by Etag
    if($this->WATCHMODE == "tags") return "HotReloaderTagsOnly";
    // get the ADDED files/folders mtime/hashlist
    if($this->WATCHMODE == "auto" || $this->WATCHMODE == "added"){
      $hashes = array_merge($hashes, $this->getADDEDHashList());
    }
    // avoid duplicated or empty values
    $hashes = array_unique(array_filter($hashes));
    // transform all hashes into a unique md5 checksum
    return md5(implode("",$hashes));
  }
  
  public function diff() {
    return $this->createStateHash();
  }
}
?>