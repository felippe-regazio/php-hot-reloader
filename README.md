# PHP Hot Reloader

This is a class that adds a live reload feature to any php project. It allows you to see your page dynamically changing while coding, without have to keep refreshing the browser on every change.

This Reloader uses a SSE (server-sent events) to listen to changes and notify the browser. So, no built-in server, no custom port, no pain configuration needed. The repository is in Version 1.0, if your trying to find information about the Beta version, please read the "NOTES.md".

# Installing

Clone this repository and copy its folder to somewhere on your project.
Im working to release this repository via composer.

# Configuring

1. Copy the `phrwatcher.php` file on the repository root to somewhere on your app. This file must be available through some URL (you can change its name if you prefer).

2. Open this file and configure the $variables according to your needings. Please, read the doc strings on the vars to properly configure them. Basically you will inform your app root, files to watch and files to ignore.

# Starting

Now, lets imagine that you have configured your phrwatcher.php and its available through url on http://localhost/your-project/phrwatcher.php. Now, you must activate the reloader by calling it on your application like this:

```php
require_once "../php-hot-reloader/src/HotReloader.php";
new HotReloader\HotReloader('//localhost/your-project/phrwatcher.php');
```

So, you must pass the address that points to your `phrwatcher.php` file as the HotReloader() function param. You dont need to add the protocol, just add "//" as URL prefix. Is highly recommended to start the HotReloader on some front controller on your app. Also you should deactivate the reloader on production, never keep this feature running on production.

# Example

There is an example file on the root of this repository. The `example.php`. Open this file on your browser to see the Hot Reloader working.