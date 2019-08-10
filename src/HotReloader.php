<?php

namespace HotReloader;

/**
 * HotReloader : Php Hot Reload - Simple live reload feature in a single file
 * HotReloader : Copyright (C) 2018 by Felippe Regazio
 * HotReloader Watcher derives from Live.js by Martin Kool and Q42
 *
 * Licensed under The MIT License
 * Site: https://github.com/felippe-regazio/php-hot-reloader
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Felippe Regazio, and releated wrapped files
 * @version       BETA
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
class HotReloader {

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
   * Current Config
   *
   * This function prints relevant information about the reloader current
   * configuration and state. Its to debug and information purposes only.
   * The information will be printed on the browser console via javascript
   *
   * @return void
   */
  public function currentConfig(){
    $apphash   = $this->createStateHash();
    $root      = is_dir($this->ROOT) ? $this->ROOT." (OK)" : $this->ROOT." (NOT FOUND)";
    $watchmode = $this->WATCHMODE;
    $diffmode  = $this->DIFFMODE;
    $includes  = implode('\n', get_included_files());
    $ignore = implode( '\n', array_filter(array_unique($this->IGNORE)) );
    $added  = implode( '\n', array_filter(array_unique($this->ADDED)) );
    //
    ob_start();?>
    <script>
      (function(){
        // start to get the front end information
        var scripts = {
          list:document.getElementsByTagName("script"),
          watching:[],
          ignoring:[]
        }
        var links = {
          list:document.getElementsByTagName("link"),
          watching:[],
          ignoring:[]
        }
        // get the scripts and links watching info
        for (var i = 0; i < scripts.list.length; i++) {
          if(!scripts.list[i].getAttribute("src")) continue;
          if(!scripts.list[i].hidden){
            scripts.watching.push(scripts.list[i].src)
          } else {
            scripts.ignoring.push(scripts.list[i].src)
          }
        }
        for (var i = 0; i < links.list.length; i++) {
          if(!links.list[i].getAttribute("href")) continue;
          if(!links.list[i].hidden){
            links.watching.push(links.list[i].href)
          } else {
            links.ignoring.push(links.list[i].href)
          }
        }
        // print the output with information from the backend
        // and from the front end matters
        var output = "\n";
        output += "Hot Reloader Current State:\n\n"
        output += "# Application Hash: \n<?=$apphash?>\n\n";
        output += "# Root: \n<?=$root?>\n\n";
        output += "# Watch Mode: \n<?=$watchmode?>\n\n";
        output += "# Diff Mode: \n<?=$diffmode?>\n\n";
        output += "# Included/Required: \n<?=$includes?>\n\n";
        output += "# Ignoring (relative to Root const): \n<?=$ignore?>\n\n";
        output += "# Added (relative to Root const): \n<?=$added?>\n\n";
        output += "# Scripts watched (src): \n"+scripts.watching.join("\n")+"\n\n";
        output += "# Scripts ignored (src): \n"+scripts.ignoring.join("\n")+"\n\n";
        output += "# Links watched (href): \n"+links.watching.join("\n")+"\n\n";
        output += "# Links ignored (href): \n"+links.ignoring.join("\n")+"\n\n";
        console.log(output);
      })();
    </script>
    <?php echo ob_get_clean();
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
   * Add the application state hash on the Etag of the Headers
   *
   * This function will get a new state hash of the current code and its dependencies
   * an will treat this hash as a fingerprint of your script state. then will set this
   * hash as an etag on the current script headers, a hash change means a code change
   *
   * @return void
  */
  private function addEtagOnHeader(){
      $hash = $this->createStateHash();
      if( $hash ) header( "Etag: " . $hash ); return true;
      echo "Hot Reloader failed to generate Application State Hash";
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
    // get the includes mtime/hashlist
    if($this->WATCHMODE == "auto" || $this->WATCHMODE == "includes"){
      $hashes = array_merge($hashes, $this->getIncludesHashList());
    }
    // get the ADDED files/folders mtime/hashlist
    if($this->WATCHMODE == "auto" || $this->WATCHMODE == "added"){
      $hashes = array_merge($hashes, $this->getADDEDHashList());
    }
    // avoid duplicated or empty values
    $hashes = array_unique(array_filter($hashes));
    // transform all hashes into a unique md5 checksum
    return md5(implode("",$hashes));
  }

  /**
   * Generates a hash list of all included/required files of the running file
   *
   * This function gets all the included/required files on the current code
   * and return a list of timestamps or md5 checksums of each file
   *
   * @return Array
   */
  private function getIncludesHashList(){
    $hashes = [];
    // this will hash all includes/requires on current code
    if(!empty(get_included_files())){
      foreach(get_included_files() as $file){
        // check if the file is not setted on in a dir setted on ignore list
        if( !$this->willBeIgnored($file) ){
          $hashes[] = ($this->DIFFMODE == "mtime" ? stat($file)['mtime'] : md5_file($file));
        }
      }
    }
    return $hashes;
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
   * Add the modified Live.js script to the current page
   *
   * This is the HotReloader JS Watcher. This script derives from live.js, due
   * several modifications it turned into another script already. This script
   * will watch the page headers, scripts and links, based on the Reloader
   * configuration. When a change is catched a page reload will be triggered
   * or the changes will be directly applied.
   *
   * @return void
  */
  private function addJsWatcher(){
    ob_start(); ?>
      <script defer>
      (function () {
        // get the ignore list from php
        var ignoreList = [<?php foreach($this->IGNORE as $key){ echo "'$key',"; } ?>];
        // script only (live.js)
        var headers = { "Etag": 1, "Last-Modified": 1, "Content-Length": 1, "Content-Type": 1 },
            resources = {},
            pendingRequests = {},
            currentLinkElements = {},
            interval = 1000,
            loaded = false,
            phperror = false,
            active = { "html": 1, "js": 1, "css": 1 },
            currentHeartBeat = undefined;
        var Live = {
          // performs a cycle per interval
          heartbeat: function () {
            if (document.body) {
              // make sure all resources are loaded on first activation
              if (!loaded) Live.loadresources();
              Live.checkForChanges();
            }
            // if the checker is not active, activates it
            if (!currentHeartBeat) setTimeout(Live.heartbeat, interval);
          },
          // loads all local css and js resources upon first activation
          loadresources: function () {
            // helper method to assert if a given url is local
            function isLocal(url) {
              var loc = document.location,
                  reg = new RegExp("^\\.|^\/(?!\/)|^[\\w]((?!://).)*$|" + loc.protocol + "//" + loc.host);
              return url.match(reg);
            }
            // gather all resources
            var scripts = document.getElementsByTagName("script"),
                links = document.getElementsByTagName("link"),
                uris = [];
            // track local js urls
            for (var i = 0; i < scripts.length; i++) {
              var script = scripts[i], src = script.getAttribute("src");
              // check if the script folder are not in the ignore list or if
              // of if the script src are not in the ignore list or if the
              // script hasnt the hidden attribute. if true, ignore the tag
              if(script.hidden || ignoreList.includes(script.baseURI) || ignoreList.includes(src)) {
                continue;
              }
              // if the script wasnt ignored
              if (src && isLocal(src))
                uris.push(src);
              if (src && src.match(/\blive.js#/)) {
                for (var type in active)
                  active[type] = src.match("[#,|]" + type) != null
                if (src.match("notify"))
                  alert("Live.js is loaded.");
              }
            }
            if (!active.js) uris = [];
            if (active.html) uris.push(document.location.href);
            // track local css urls
            for (var i = 0; i < links.length && active.css; i++) {
              var link = links[i], rel = link.getAttribute("rel"), href = link.getAttribute("href", 2);
              // check if the link folder are not in the ignore list or if
              // of if the link src are not in the ignore list or if the
              // link hasnt the hidden attribute. if true, ignore the tag
              if(link.hidden || ignoreList.includes(link.baseURI) || ignoreList.includes(href)) {
                continue;
              }
              // if the link tag wasnt ignored
              if (href && isLocal(href)) {
                uris.push(href);
                currentLinkElements[href] = link;
              }
            }
            // initialize the resources info
            for (var i = 0; i < uris.length; i++) {
              // check if the script/link src/href are in ignore list
              // if not finally add the element to resources list
              if(!ignoreList.includes(uris[i])){
                var url = uris[i];
                Live.getHead(url, function (url, info) {
                  resources[url] = info;
                });
              }
            }
            // yep
            loaded = true;
          },
          getHTML: function ( url, callback ) {
            // Feature detection
            if ( !window.XMLHttpRequest ) return;
            // Create new request
            var xhr = new XMLHttpRequest();
            // Setup callback
            xhr.onload = function() {
              if ( callback && typeof( callback ) === 'function' ) {
                callback( this.responseXML );
              }
            }
            // Get the HTML
            xhr.open( 'GET', url );
            xhr.responseType = 'document';
            xhr.send();
          },
          checkBackEndFails: function(newInfo, oldInfo) {
            /*
              this little section try to catch errors
              from the backend that could break the watcher
              before the page reloads. if the newInfo key has
              sended and Etag, Last-Modified and Content-Length
              are null, and the Content-Type = "text/html", this
              Means a possible back end error on code, so we
              stop the reloadings a little and console the
              possible error. Then, we will get the current page
              content with a xhr request and dinamically print its
              content on screen, overwriting the current one.
              This will output the error, if has one withou break
              out script. This is an special situation of error.
            */
            if(newInfo['Content-Type'] == 'text/html'){
              if(newInfo['Etag'] == null && newInfo['Last-Modified'] == null && newInfo['Content-Length'] == null){
                Live.getHTML( window.location.href, function (response) {
                  if(document.documentElement.innerHTML != response.documentElement.innerHTML) {
                    document.documentElement.innerHTML = response.documentElement.innerHTML;
                    if(!phperror){
                      console.error("Hot Reloader tracked a possible error on your back end code");
                    }
                  }
                });
                phperror = true;
              }
            }
          },
          // check all tracking resources for changes
          checkForChanges: function () {
            for (var url in resources) {
              if (pendingRequests[url])
                continue;
              Live.getHead(url, function (url, newInfo) {
                var oldInfo = resources[url],
                    hasChanged = false;
                resources[url] = newInfo;
                // Check for back end fails
                Live.checkBackEndFails(newInfo, oldInfo);
                // If content exists, and is not totally empty:
                for (var header in oldInfo) {
                  // do verification based on the header type
                  var oldValue = oldInfo[header],
                      newValue = newInfo[header],
                      contentType = newInfo["Content-Type"];
                  switch (header.toLowerCase()) {
                    case "etag":
                      if (!newValue) break;
                      // fall through to default
                    default:
                      hasChanged = oldValue != newValue;
                      break;
                  }
                  // if changed, act
                  if (hasChanged) {
                    Live.refreshResource(url, contentType);
                    break;
                  }
                }
              });
            }
          },
          // act upon a changed url of certain content type
          refreshResource: function (url, type) {
            switch (type.toLowerCase()) {
              // css files can be reloaded dynamically by adding a hash on its href
              case "text/css":
                var link = currentLinkElements[url].setAttribute("href", url + "?now=" + new Date() * 1);
                break;
              // check if an html resource is our current url, then reload
              case "text/html":
                if (url != document.location.href)
                  return;
              // local javascript changes cause a reload as well
              case "text/javascript":
              case "application/javascript":
              case "application/x-javascript":
                document.location.reload();
            }
          },
          // performs a HEAD request and passes the header info to the given callback
          getHead: function (url, callback) {
            pendingRequests[url] = true;
            var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XmlHttp");
            xhr.open("HEAD", url, true);
            xhr.onreadystatechange = function () {
              // ignore the 404 requests
              if( xhr.readyState == 4 && xhr.status == "404") return;
              // if status != 404, proceed
              delete pendingRequests[url];
              if (xhr.readyState == 4 && xhr.status != 304) {
                xhr.getAllResponseHeaders();
                var info = {};
                for (var h in headers) {
                  var value = xhr.getResponseHeader(h);
                  // adjust the simple Etag variant to match on its significant part
                  if (h.toLowerCase() == "etag" && value) value = value.replace(/^W\//, '');
                  if (h.toLowerCase() == "content-type" && value) value = value.replace(/^(.*?);.*?$/i, "$1");
                  info[h] = value;
                }
                callback(url, info);
              }
            }
            xhr.send();
          }
        };
        // start listening
        if (document.location.protocol != "file:") {
          if (!window.liveJsLoaded)
            Live.heartbeat();
          window.liveJsLoaded = true;
        }
        else if (window.console)
          console.log("Php Hot Reloader: Live.js doesn't support the file protocol. It needs http.");
      })();
      </script>
    <!-- END AND PRINT OF LIVE.JS -->
    <?php echo ob_get_clean();
  }
}
?>