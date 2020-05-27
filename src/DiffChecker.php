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
 * @copyright  Copyright (c) Felippe Regazio, and related wrapped files
 * @version    1.0.0
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */
class DiffChecker {

  /**
   * Simple constructor method containing the class params
   *
   * @param $options {Array} Options array following the defined constructor globals
   * @return void
  */
  function __construct ($options = []) {
    $this->ROOT    = $this->addSlash($options["ROOT"]);
    $this->WATCH   = $options["WATCH"];
    $this->IGNORE  = $options["IGNORE"];
  }

  /**
   * Receives a path like 'my/path/xyz' and returns it with
   * a trailling slash in case it doesnt exists
   * @param $path {String}
   * @return string
   */
  private function addSlash ($str) {
    return $str .= ( substr($str, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );
  }

  /**
   * Generates a hash list of all files added to watch
   *
   * @param void
   * @return array
  */
  private function hashAppFiles () {
    
    $hashes = [];
    
    if(!empty($this->WATCH)){
      
      $git_mode = ( gettype($this->WATCH) === 'string' && substr($this->WATCH, 0, 4) === "git:" );
      
      if ($git_mode) {
        // WATCH = git:repo/path in this case
        $this->ROOT = substr($this->WATCH, 4);
        $this->WATCH = $this->getGitFiles($this->ROOT);
      }

      foreach($this->WATCH as $add){
        $add = $this->ROOT . $add;
        if(is_dir($add)){
          if( !$this->willBeIgnored($add) ){
            $hashes[] = $this->hashDir($add);
          }
        } else {
          if( file_exists($add) && !$this->willBeIgnored($add) ){
            $hashes[] = md5_file($add);
          }
        }
      }
    }
    return $hashes;
  }

  /**
   * Get the current git Modified and Other files on git file tree
   * Set the git tracked files as the current phrwatcher $WATCH 
   * @param $repo_path {String} Repository abs path
   * @return array
   */
  private function getGitFiles ($repo_path) {
    $git_files = shell_exec('cd ' . $repo_path . '; git ls-files -m -o 2>&1');
    return explode(PHP_EOL, $git_files);
  }

  /**
   * Generates a unique hash for an entire directory
   *
   * Generates a hash array of each file and folder inside the given
   * directory, than transform this array in a unique md5 checksum
   *
   * @param $directory {String} Directory path
   * @return string
  */
  private function hashDir ($directory) {
    if (! is_dir($directory)) return false;
    $files = array();
    $dir = dir($directory);
    while (false !== ($file = $dir->read())){
      if ($file != '.' and $file != '..'){
        if (is_dir($directory . DIRECTORY_SEPARATOR . $file)){
          $files[] = $this->hashDir($directory . DIRECTORY_SEPARATOR . $file);
        }
        else{
          $curr_file = $directory.DIRECTORY_SEPARATOR.$file;
          if(!$this->willBeIgnored($curr_file)){
            $files[] = md5_file($curr_file);
          }
        }
      }
    }
    $dir->close();
    return md5(implode("",$files));
  }

  /**
   * Check if a given path (abs path) must be ignored by the Reloader
   * The given paths are always converted to be relative to the Root
   *
   * @param $file {String} File path
   * @return boolean
  */
  private function willBeIgnored ($file) {
    if( !empty(array_filter($this->IGNORE)) ){
      foreach( $this->IGNORE as $ignore ){
        $ignore = $this->ROOT . $ignore;
        if($file == $ignore || strpos(dirname($file),$ignore) !== false && strpos(dirname($file),$ignore) == 0){
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Collects all the hashes generated for the app and transforms it into
   * a unique md5 hash. this unique hash is your app state fingerprint.
   *
   * @param void
   * @return string
   */
  private function getAppStateHash () {
    $hashes = $this->hashAppFiles();
    $hashes = array_unique(array_filter($hashes));
    return md5(implode("",$hashes));
  }

  /**
   * Public API that returns the current app state hash
   *
   * @param void
   * @return $hash {String} The app hash
   */
  public function hash () {
    return $this->getAppStateHash();
  }
}
?>