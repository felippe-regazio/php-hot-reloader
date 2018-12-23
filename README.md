# PHP Hot Reloader

This class adds a live reload feature to any php project. It allows you to see your page dinamically changing while coding, without have to keep refreshing the browser on every change. By default, the script will turn your project tab reactive to changes in included/required files, css and js files releated to the tab opened. Every browser, every project, one single file.

Php Hot Reloader is written by Felippe Regazio and wrapps the live.js script with several modifications.
The Original Live.js was written by Martin Kool (http://livejs.com/).

# Usage

The simplest way to start is: require the class, instantiate it, call the init method.
Now, keep the page opened while coding, and just code.

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->init();
```

By default, the Php Hot Reloader will watch all included/required files, scripts and link tags related to your current page. If there is any change, the page will reload, or the changes will be dinamically added (in css files for example). Anyway, the Reloader accepts many options as ignore or add files to the watcher, change the watch or diff mode, etc.

# Setting the Root Path

If you intend to ignore or add new files to the watcher, its better start setting the Root Path. You can do it using the method setRoot() as showed below. All the paths passed to your HotReloader instance must be relative this Root Path. If you do not set any Root Path, the hotreloader.php file path you be used as default.

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setRoot(__DIR__);
$reloader->init();
```

# Ignoring files or directories

By default, the Reloader reacts to any change on included/required files and script/links src/href tags. But its possible to unwatch expecific files or directories. So, changes in these unwatched files will not trigger a page reload. To ignore a file or a folder, use the ignore() method.

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setRoot(__DIR__);
$reloader->ignore([
  "filetoignore.php",
  "filetoignore.js",
  "path/folder/ignore"
]);
$reloader->init();
```

In the above example, we setted the root as the path of current file, and added 2 files and a folder to be ignored. The paths in the ignore() array will be relative to the Root.

WARNING: 

Its important to diferentiate the back end paths from the front end paths. The ignore() method is relative to the application front and back end, the way to referentiate the files and assets its different dependend on where your are, and how its running. So, lets assume you have a script tag in your application, and you want to ignore it. You must add its src content to the ignore() array. The same with the link href. You can add folder paths relative to src or hrefs contents to be ignored too.

TIP: 

However, the fastest way to ignore a script or link tag is simply add a "hidden" attribute on the tag. If you do that, the Reloader will not react to the changes on these files.

# Adding new files or folders to the Watcher

If you have files that are not included or directly related to your code, but you'd like them to autoreload your page, you can use the add() method. You can add new files or folders to the add() array, and they will trigger a page reload when changed. The path rules are same as the ignore() method.

If you have a folder or file in the add() array and the same on the ignore() array, the file will be ignored as well. Directories are recursivelly added, so changing in files on the subdirs are relevant too.


```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setRoot(__DIR__);
$reloader->add([
  "filetoadd.php",
  "filetoadd.js",
  "path/folder/add"
]);
$reloader->init();
```

# Changing Reloader Watcher Behavior

You can choice a set of things to watch. To do it, use the setWatchMode(String $mode) method. The $mode can be:

1. 'auto' : is the default mode. In this mode the reloader reacts to modifications on the current page, its included/required/ files and its srcript/link tags.

2. 'includes' : set the reloader to react only to the page code and its included/required files.

3. 'added' : set the reloader to react only to the files and directories setted on the add() method array.

4. 'tags' : set the reloader to react only to the script and link tags on the html code.

In any mode, the html script/link tags will be relevant to the Reloader. If you want to ignore some of them anyway, read the 'Ignoring files or directories' part of this documentation.

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setRoot(__DIR__);
$reloader->setWatchMode('includes');
// $reloader->setWatchMode('added');
// $reloader->setWatchMode('tags');
$reloader->init();
```

# Changing the Reloader Diff Behavior

By default, the PHP Hot Reloader will create a list of all files related to your code, or added via add(), remove those setted via ignore(), and create a list of modified date/time of each file. This list will be hashed with md5 to create your application fingerprint. a unique checksum of the files state. When this fingerprint changes, it means that the page must be reloaded because something has changed.

You can change the way this list is created with the setDiffMode(String $mode) method. The $mode can be 'mtime', which is the default, or 'md5'. In the md5 mode, all files will be hashed using md5 insted modification date/time.

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->setRoot(__DIR__);
$reloader->setDiffMode('md5');
$reloader->init();
```

# The currentConfig() method

Use the current config to see a resume of your Php Hot Reloader on your browser console. This function is to information and debug purposes only. Tip: You can you currentConfig() method without init() the Reloader.

```php
require "../hotreloader.php";
$reloader = new HotReloader();
$reloader->currentConfig();
$reloader->init();
```

# The set() shortcut method

The set method can be used as a configuration shortcut to initialize the hotRealoder.

# How it Works

This class is divided in two parts: PHP and the Javascript part. The php part will build a list of all files related to your current code (which calls the reloader) and use the datetime or md5_file to create a unique application checksum, this will be the current state of your code. This class adds this checksum (hash) on the headers Etag field of every request. The javascript part keep watching your application headers, scripts and link tags every 1 sec. When something has changed (the etag checksum or any tag related file), the JS will perceive the diff and will reload the page. Of course, many features are included like the ignore lists, watcher increment, error handling, etc. To know more about, please read the source code self documentation on hotreloader.php file.

# Live.js

This class uses the live.js script to handle the watching and page reloads. We modified it a lot, but its of course really fair to give the proper credits. Its really an awesome and simple script. To know more: http://livejs.com/.

# License

Both Php Hot Reloader and Live.js are under MIT license.
See the LICENSE.txt file to know more.
