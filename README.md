# PHP Hot Reloader V2

This is a class that adds a live reload feature to any php project. It allows you to see your page dynamically changing while coding, without have to keep refreshing the browser on every change.

This Reloader uses a SSE approach (server-sent events) to listen to changes and notify the browser. So, no built-in server, no custom port, no pain configuartions needed. The repo is in Version 2, if your trying to find information about the V1, please read the "Notes About V1" on this README.

# Installing

With composer:

```bash
composer require felippe-regazio/php-hot-reloader
```

Manually:

Clone this repository and copy its folder to somewhere on your project.

# Configuring

1. Copy the `phrwatcher.php` file on the repository root to somewhere on your app. This file must be available through some URL, and you can change its name if you prefer.

2. Open this file and configure the $variables according to your needings. Please, read the doc strings on the vars to properly configure them. Basically you will inform your app root dir, reloader root dir, files to watch and files to ignore.

# Starting

Now, lets imagine that you have configured your phrwatcher.php and its enable through url on http://localhost/your-project/phrwatcher.php. Now, you must activate the reloader by calling it on your application like this:

```php
new HotReloader\HotReloader('//localhost/your-project/phrwatcher.php');
```

# Notes About V1

This is a version 2 of this feature. On the V1 the approach was to listen changes and notify the browser using "etag" on a custom header. On client a XHR would polling the server till receive a flag about that change. That was a bad idea becase the custom headers constantly collides with already sent headers (so, it will be an error), the xhr polling is also bad for the performance and, finally, the JS and CSS was live applied on the page, cause a bug: DOM changes was causing page refresh. So i decided to rewrite the entire class.

Now, as already said, the feature uses a totaly different approach with SSE, no polling, no custom headers, etc. So, if you were using the V1, please consider to migrate to this version, it will be very simple. Anyway, there is a "V1" branch on this repository that holds the first version, feel free to use it (no support anymore).