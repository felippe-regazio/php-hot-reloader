# PHP Hot Reloader

[UNMAINTAINED]: This repository is currently unmaintained, I've been very busy in the last months and im struggling to find some time to take care of this repo but with no success. I'm sorry for those who are using it. If you want to maintain this repo, please open an issue and let me know.

This is a class that adds a live reload feature to any php project. It allows you to see your page dynamically changing while coding, without have to keep refreshing the browser on every change.

This Reloader uses a SSE (server-sent events) to listen to changes and notify the browser. So, no built-in server, no custom port, no pain configuration needed.

# Installing

### Using Composer

```
composer require felippe-regazio/php-hot-reloader:dev-master --prefer-source
```

### Manually

Clone this repository and copy its folder to somewhere on your project.

# Configuring

1. Copy the `phrwatcher.php` file on the repository root to somewhere on your app. This file must be available through some URL (you can change its name if you prefer), and if you visit this file you must see the message "SSE_ADDRESS_OK | PROJECT ROOT:" informing your project root. Copy this URL, you'll gonna need it. 

2. Open this file and configure the $variables according to your needings. Please, read the doc strings on the vars to properly configure them. Basically you will inform your app root on $PROJECT_ROOT, files to watch and files to ignore relative to the app root on $WATCH and $IGNORE.

3. Now, lets imagine that you have configured your `phrwatcher.php` and it is available on the URL http://localhost/your-project/phrwatcher.php (the URL that you copied on the first step). Now, you must activate the reloader by calling it on your application and pointing it to this URL like this:

```php
require_once "/php-hot-reloader/src/HotReloader.php";
new HotReloader\HotReloader('//localhost/your-project/phrwatcher.php');
```

If you installed this repository using composer, you dont need the require_once, just add the "autoloader" on your app and call the reloader.

So, you must pass the address that points to your `phrwatcher.php` file as the HotReloader() function param. You dont need to add the protocol, just add "//" as URL prefix. Is highly recommended to start the HotReloader on some front controller on your app. Also you should deactivate the reloader on production, never keep this feature active on production.

# Git Integration

The $WATCH variable on the `phrwatcher.php` file defines the files and folders to track. So, instead of set a list of files and folder and observe them, you can Observe your repository. The Hot Reloader will use git to track the files currently being changed and watch them. This saves a lot of time and processing, since the files being tracked are updated automatically, and reserved only to the files that you are working on the moment. To track using GIT you must set your $WATCH variable like this:

```php
$WATCH = "git:your/absolute/repository/path/";
```

If your `phrfile.php` is in your project root, you can use an automatic and dynamic version:

```php
$WATCH = "git:" . __DIR__;
```

Now the Hot Reloader will automatically observe the files using your Git Status.

# Tips

When working with larger projects, the diff algorithm can take some time to calc the modified files. To avoid the "input lag", you can follow this tips:

1. On the `$WATCH` array of your phrwatcher.php try to keep only the necessary. If your project is huge, avoid using ".", and especify path by path, avoiding unecessary huge folders as vendors, tmp, log. Or consider use the Git Integration.

2. The `$IGNORE` array is related only to what you are watching. If any `vendor` folder on the paths that you are watching, you must add them to your ignore list. Paths such as vendor, node_modules, sass_cache, tmp, log, keep them ignored.

3. If your project is really big, is recomended to add the 'git' mode to your $WATCH list. So, your list will be always the result of a git status, consequently, you will be watching always the files that you are currently working on.

# Example

There is an example file on the root of this repository. The `example.php`. Open this file on your browser to see the Hot Reloader working.
