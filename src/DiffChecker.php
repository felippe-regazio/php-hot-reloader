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
class HotReloaderDiffChecker {

  /**
   * Constructor
   *
   * Simple constructor method containing the class params
   *
   * @return void
  */
  function __construct ($options = []) {
    $this->ROOT      = __DIR__; // the root of directories
    $this->DIFFMODE  = "md5";   // mtime/md5
    $this->WATCH     = ["../"];   // extra files to be watched
    $this->IGNORE    = [''];    // file or folders to ignore
  }

  /**
   * Generates a hash list of all files added to watch with the method add()
   *
   * This function build the hash/timestamp list of the files and folders which
   * came from the added() method
   *
   * @return Array
  */
  private function hashAppFiles () {
    $hashes = [];
    // this will hash all files and folders in ADDED array
    if(!empty($this->WATCH)){
      foreach($this->WATCH as $add){
        // create the added path relative to the ROOT const
        $DS = !strpos($this->ROOT, DIRECTORY_SEPARATOR) == strlen($this->ROOT) ? DIRECTORY_SEPARATOR : "";
        $add = $this->ROOT.$DS.$add;
        // do the hash
        if(is_dir($add)){
          if( !$this->willBeIgnored($add) ){
            // if is a dir, hash the entire directory (mtime or md5)
            // the directorie hash is an implode of hashes of all files there
            // the hashDir always return a md5 checksum of the directory
            $hashes[] = $this->hashDir($add);
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
  private function hashDir ($directory) {
    $mode = $this->DIFFMODE;
    if (! is_dir($directory)) return false;
    $files = array();
    $dir = dir($directory);
    while (false !== ($file = $dir->read())){
      if ($file != '.' and $file != '..'){
        if (is_dir($directory . DIRECTORY_SEPARATOR . $file)){
          $files[] = $this->hashDir($directory . DIRECTORY_SEPARATOR . $file, $mode);
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
  private function willBeIgnored ($file) {
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
  private function getAppStateHash () {
    $hashes = $this->hashAppFiles();
    // avoid duplicated or empty values
    $hashes = array_unique(array_filter($hashes));
    // transform all hashes into a unique md5 checksum
    return md5(implode("",$hashes));
  }

  public function hash () {
    return $this->getAppStateHash();
  }
}
?>