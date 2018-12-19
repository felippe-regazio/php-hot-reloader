# PHP HOT RELOADER

This is a tiny class which adds the live reload feature to any php project. It allows you to see your page dinamically changing while you code, without have to keep refreshing page on every change.

This script is written by Felippe Regazio.
The javascript part includes live.js script with several modifications.

# USAGE

This class accepts a few options, but the simplier and faster way to use it is require the class file (or add the class in your project somehow); then add the following code before your \</html> tag:

```php
require "../hotreloader.php";
@$reloader = new HotReloader();
@$reloader->init();
```

Some notes: remember to add the code BEFORE your \</html>. Note that is your CLOSING script tag. If you add any include or require after the reloader->init, it wont be watched. The assets like js and css files are watched by live.js wrapped on this class. Of course, the require path must be changed to your proper path.

# HOW IT WORKS

This script is divided in two parts. The PHP part and the JAVASCRIPT part. The Javascript uses the live.js reload script (http://livejs.com/), with a few modifications. The script uses the etag key on document headers to store a hash of all included files, so, if any file related to your current code changes, the hash will change. On the client side, the live.js will be watching the current document every 1 sec. looking for changes in the js, css, html files and in the document headers. So, when you change a file or when the etag sends a different hash, the live.js will trigger a page reload.  

By default, the class only assists the included files and its assets waiting for changes, and the hash of this contents and its  assets is created using an md5 of all related files timestamps. Its possible to change this behavior, and the class default behavior with a few options.

# THE setRoot() METHOD

- setRoot( String $root );

$root must be the path which contains all dirs setted to watch (see setWatchDirs());

This will define the root path to the watcher. If you didnt used a setRoot() method to define your project root, the paths will be relative to where the hotreloader.php is. A good tip is to use the setRoot as setRoot(__DIR__), so your paths will be always relative to your current file.

You only need to set this options when using setWatchDirs() or to set a Root path to ignore() files and folders. When setted, all the directories passed to setWatchDirs or ignore() must be relative to this root. Use an absolute path as $root. By default the $root is the __DIR__ php const.

# THE ignore() METHOD

Use this method to unwatch a folder, a file, or a src/href paths. The hot reloader will ignore all folders and files setted in the ignore. Maybe you can find some diferences in ignoring server side or client side matters.

To ignore front end (src/links added), please include the element src/href on the ignore. This happen cause, due htaccess and other technologies, sometimes the src path is not really the real location path of a style or css file. You can add folders relative to the script/link src/href to ignore all files inside a given path. 

To ignore back end matters, just add the file/folder path to the ignore array. If you didnt set a Root (see setRoot method), you must use absolute paths, otherwise, the paths must be relative to the setted Root. The path setted with setRoot() must be absolute.

Example:

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setRoot(__DIR__);
$reloader->ignore([
  "project/path/to/custom/directory/file.php",
  "project/path/to/first/directory",
  "assets/file/css/mycss.css",
  "assets/file/js/debugger/"
  // here we will ignore a php file, a folder called directory
  // a mycss.css file (added the css href) and all js files inside
  // the folder assets/file/js/debugger. note that the ignorings
  // related to the front end assets point to its src/href attrs,
  // and the files related to the backend points to its real paths
]);
$reloader->init();
```

Obs: if you add a js folder in your ignore list like '/myjs' hopping to ignore all js files there, but in the script tags the src attributes are like "project/myjs/filename.js", you must add 'project/myjs' in your ignore, beside the Root. remember: front end src/href ignorigins are relative to these attributes, not its real paths. But if you have a folder 'debugger/files' for example, with include some php files in your code and you want to ignore them, add the folder to ignore respecting the root config. Anyway, if your php files and assets dont pass thought a path mod (htaccess or something like), and share the same Root path, you can add a single key to ignore both front end and back end matters.

# GENERAL METHODS

There are a few methods to get and set the reloader behavior:

- setDiffMode( String $mode );

$mode can be "mtime" (default) or "md5";

This method is used to change the way the reloader generates the files fingerprint. The live.js look to your current file every 1 second. The php will look all the included files used to build your script (including the script itself) and by default will get the timestamp of all them and use md5 to generate a hash. So, if you change a file, the timestamp will change, your md5 will change generating a hash change on your headers, this hash changing will be catched by live.js and the page will be reloaded. If you change the diff mode to md5, using setDiffMode("md5"), insted of looking into files timestamps, the script will generate a file_md5 hash of all included files, and then will generate a new hash of this information.

- setWatchMode( String $mode );

$mode can be 'auto' or 'dirs';

By defaut, this script catch all the included files used to build your code, and generates a hash of it to watch changes, as already said. But you can watch an intire directory, or many. When you set the watch mode to 'dirs', using setWatchMode("dirs"), the script will generate a hash of the entire directory (by default the current file directory) files. This means that a single change in any file of this directory will cause a page reload.

- setWatchDirs( Array $dirs );

$dirs must be an array of directories paths

This method must be only used when using "dirs" as watch mode. The setWatchDirs method tells the reloader which directories watch. If you dont set the directories to the watcher, your file current directory will be used. All the subdirectories will be considered. So, any change in the directory will trigger a page reload.

- currentConfig();

Use the currentConfig method to check the current configuration of your reloader.

- Methods Usage Example:

```php
// in this example we start the reloader with some overrided options
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setDiffMode('md5'); // hash the changes using md5
$reloader->setWatchMode('dirs'); // watch the current entire directory
$reloader->ignore(["/projectfolder/currentdir/file1.php"]); // the reloader will ignore changes in file1.php
$reloader->init();
```

# THE set() METHOD SHORTHAND

The set() method is a shorthand to all the reloader setters. You can pass a set of options in an array, the usage must be:

```php
$reloader->set([
    'DIFFMODE'  => "mtime", // mtime or md5
    'WATCHMODE' => "auto",  // auto or dirs
    'WATCHDIRS' => [""],    // the directories to watch
    'IGNORE' 	=> [""],
    'ROOT'      => __DIR__  // the root of those directories	
]);
```

# LIVE.JS

The Php Hot Reloader uses the live.js script to handle the watching and page reloads. The hashes are sended from the php to the etag on the document headers, and the live.js will consider this as a hash to diff too. Thats the trick. We made a few modifications in the script, so, maybe you will have no success including the live.js in an external way, so, we wrapped the script in the class. If you want to know more about, please visit: http://livejs.com/.

# CONSIDERATIONS

The diff mode 'md5' with watch mode 'dirs' will be really accurated. Even a space change in a file inside any subfolder of you document will trigger a page reload. But this method is really expansive, and turn your application slowest if you are dealing with more of 20.000 files in a folder. Can be really bad to hash all those files. If you have no very expecific needing, prefer to use the reloader default config, which will watch only the files releated to your current page, turning everything lighter.

