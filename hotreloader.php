<?php
/**
 * HotReloader : Php Hot Reload - Simple live reload feature in a single file
 * HotReload : Copyright (C) 2018 by Felippe Regazio
 * Live.js : Copyright (C) 2011 by Martin Kool and Q42
 *
 * Licensed under The MIT License
 * Site: https://github.com/felippe-regazio/php-hot-reload
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Felippe Regazio, and releated wrapped files
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
class HotReloader {

  function __construct() {
    $this->DIFFMODE  = "mtime"; // mtime or md5
    $this->IGNORE    = [""];    // file/folders to ignore
    $this->WATCHMODE = "auto";  // auto or dirs
    $this->WATCHDIRS = [""];    // the directories to watch
    $this->ROOT      = __DIR__; // the root of those directories
  }

  // single setters

  public function setDiffMode( String $mode){
    $this->DIFFMODE = $mode;
  }

  public function setWatchMode( String $mode){
    $this->WATCHMODE = $mode;
  }

  public function setWatchDirs( Array $dirs ){
    $this->WATCHDIRS = $dirs;
  }
  
  public function setRoot( String $root ){
    $this->ROOT = $root;
  }

  public function ignore( Array $array ){
    $this->IGNORE = $array;
  }  

  // general setter

  public function set( Array $options ){
    foreach($options as $key => $val){
      $this->$key = $val;
    }
  }

  // getters

  public function currentConfig(){
    return [
      "ROOT"      => $this->ROOT,
      "DIFFMODE"  => $this->DIFFMODE,
      "WATCHMODE" => $this->WATCHMODE,
      "WATCHING"  => $this->WATCHDIRS,
      "IGNORING"  => $this->IGNORE,
      "STATEHASH" => $this->createStateHash($this->MODE)
    ];
  }

  // this is the main function. it sends the hash of watchings on doc headers
  // and starts the javascript watcher (see live.js documentation for more about)
  // this functions depends on all the other functions and vars to properly run
  public function init(){
    $this->addEtagOnHeader();
    $this->addJsWatcher();
  }   

  // PRIVATES ------------------------------------------------------------------

  // this function receives a directory and generates a hash based on its 
  // contents and subdirectories contents. the hash is created from md5
  // of all files, or a timestamp fingerprint of all files, in the passed ri. 
  // for details about mtime or md5 option, please see the README.md file 
  private function hashDirectory($directory, $mode){
    if (! is_dir($directory)) return false;
    $files = array();
    $dir = dir($directory);
    while (false !== ($file = $dir->read())){
      if ($file != '.' and $file != '..'){
        if (is_dir($directory . DIRECTORY_SEPARATOR . $file)){
          $files[] = $this->hashDirectory($directory . DIRECTORY_SEPARATOR . $file, $mode);
        }
        else{
          $curr_file = $directory . DIRECTORY_SEPARATOR . $file;
          $files[] = ($mode == "mtime" ? stat($curr_file)['mtime'] : md5_file($curr_file));
        }
      }
    }
    $dir->close();
    return md5(implode('', $files));
  }

  // this function check if a path exists in a given array of paths. the checking
  // will check if the searched path is a substring of any key in the given array
  private function willBeIgnored(String $searched){
    if( !empty(array_filter($this->IGNORE)) ){
      $array = $this->IGNORE;
      foreach( $array as $key ){
        // check if the ROOT was setted
        if( !empty($this->ROOT) ){
          $DS = !strpos($this->ROOT, DIRECTORY_SEPARATOR) == count($this->ROOT) ? DIRECTORY_SEPARATOR : "";
          $key = $this->ROOT.$DS.$key;
        }
        // check if must ignore
        if( ($searched == $key) || (strpos($searched, $key) !== false) && (strpos($searched, $key) == 0) ){
          return true;
        }
      }
    }
    // everything has failed
    return false;
  }

  // this funtion usedthe to create an hash based on the current script state
  // if you are running in "auto" mode, the script will check all included files
  // and generate a hash of them using mtime or md5 (depending of your choice).
  // if you are using "dirs" mode, the script will check the watchdirs and hash
  // the entire directories there (mtime or md5) depending of your choice, if
  // you dont passed any watchdirs, the current script directory will be used.
  // this generated hash will be sended in the doc headers as a unique fingerprint
  // the live.js will be watching the headers, if this fingerprint changes, the
  // script knows that something has changed and will trigger an automatic reload
  private function createStateHash($mode){
    $hashes = [];
    if( $this->WATCHMODE == "auto" ){
      // if watchmode = auto, we will hash only include files related to the
      // current file, this options is lighter than the 'dir' option for ex
      foreach( get_included_files() as $file ){
        // check if the file is not setted on in a dir setted on ignore list
        if( !$this->willBeIgnored($file) ){
          $hashes[] = ($mode == "mtime" ? stat($file)['mtime'] : md5_file($file));
        }
      }      
    } elseif( $this->WATCHMODE == "dirs" ) {
      // if the watchmode = dir, we will watch the entire directories setted in
      // this watch (if none, the script current dir will be taken) and hashe it
      foreach( $this->WATCHDIRS as $dir ){
        if( !$this->willBeIgnored($dir) ){
          $hashes[] = $this->hashDirectory($this->ROOT.DIRECTORY_SEPARATOR.$dir, $this->DIFFMODE);
        }
      }
    }
    // return the new hash or empty/false
    return md5(implode("",$hashes)); 
  }

  // this function will create a new state hash based on your configurations
  // this hash will be a fingerprint of your script related files state. then
  // this funciton will set this hash as an etag on the current script headers
  function addEtagOnHeader(){
      $hash = $this->createStateHash($this->DIFFMODE);
      if( $hash ) header( "Etag: " . $hash ); return true;
      echo "HotReloader: Failed to generate Etag Hash";
  }

  // this function adds the live.js on the page with several modifications. the script 
  // will keep watching the current address every 1 second. it will check changes in 
  // files with extension js, html and css, based on your page \<header>, and will 
  // check the page headers for changes in etag, last-modified, content lenght and type.
  // when the script traps a file or header info changing, the page will be auto reloaded
  private function addJsWatcher(){
    ob_start(); ?>
      <script>
      (function () {
        // get the ignore list from php
        var ignoreList = [<?php foreach($this->IGNORE as $key){ echo "'$key',"; } ?>];
        // script only (live.js)        
        var headers = { "Etag": 1, "Last-Modified": 1, "Content-Length": 1, "Content-Type": 1 },
            resources = {},
            pendingRequests = {},
            currentLinkElements = {},
            oldLinkElements = {},
            interval = 1000,
            loaded = false,
            phperror = false,
            active = { "html": 1, "js": 1, "css": 1 };
        var Live = {
          // performs a cycle per interval
          heartbeat: function () {      
            if (document.body) {        
              // make sure all resources are loaded on first activation
              if (!loaded) Live.loadresources();
              Live.checkForChanges();
            }
            setTimeout(Live.heartbeat, interval);
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
              // the script hasnt the hidden attribute. if so, ignore the tag
              if(script.hidden || ignoreList.includes(script.baseURI)) continue;
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
              // check if the link(css) folder are not in the ignore list or if
              // the link tag hasnt the hidden attribute. if so, ignore the tag
              if(link.hidden || ignoreList.includes(link.baseURI)) continue;
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
              sended an Etag, Last-Modified and Content-Length 
              are null, and the Content-Type = "text/html", this 
              Means a possible back end error on code, so we
              stop the reloadings a little and console the 
              possible error. Then, we will get the current page
              content with a xhr request and dinamically print its
              content on screen, overwriting the current one. 
              This is an special situation of error code. 
            */
            if(newInfo['Content-Type'] == 'text/html'){
              if(newInfo['Etag'] == null && newInfo['Last-Modified'] == null && newInfo['Content-Length'] == null){
                if(!phperror) console.error("Hot Reloader tracked a possible error on your back end code");
                phperror = true;
                Live.getHTML( window.location.href, function (response) {
                  document.documentElement.innerHTML = response.documentElement.innerHTML;
                });                
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
              // css files can be reloaded dynamically by replacing the link element                               
              case "text/css":
                var link = currentLinkElements[url],
                    html = document.body.parentNode,
                    head = link.parentNode,
                    next = link.nextSibling,
                    newLink = document.createElement("link");
                html.className = html.className.replace(/\s*livejs\-loading/gi, '') + ' livejs-loading';
                newLink.setAttribute("type", "text/css");
                newLink.setAttribute("rel", "stylesheet");
                newLink.setAttribute("href", url + "?now=" + new Date() * 1);
                next ? head.insertBefore(newLink, next) : head.appendChild(newLink);
                currentLinkElements[url] = newLink;
                oldLinkElements[url] = link;
                // schedule removal of the old link
                Live.removeoldLinkElements();
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
          // removes the old stylesheet rules only once the new one has finished loading
          removeoldLinkElements: function () {
            var pending = 0;
            for (var url in oldLinkElements) {
              // if this sheet has any cssRules, delete the old link
              try {
                var link = currentLinkElements[url],
                    oldLink = oldLinkElements[url],
                    html = document.body.parentNode,
                    sheet = link.sheet || link.styleSheet,
                    rules = sheet.rules || sheet.cssRules;
                if (rules.length >= 0) {
                  oldLink.parentNode.removeChild(oldLink);
                  delete oldLinkElements[url];
                  setTimeout(function () {
                    html.className = html.className.replace(/\s*livejs\-loading/gi, '');
                  }, 100);
                }
              } catch (e) {
                pending++;
              }
              if (pending) setTimeout(Live.removeoldLinkElements, 50);
            }
          },
          // performs a HEAD request and passes the header info to the given callback
          getHead: function (url, callback) {
            pendingRequests[url] = true;
            var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XmlHttp");
            xhr.open("HEAD", url, true);
            xhr.onreadystatechange = function () {
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
          console.log("Live.js doesn't support the file protocol. It needs http.");    
      })();
      </script>
    <!-- END AND PRINT OF LIVE.JS -->
    <?php echo ob_get_clean();
  }
}
?>