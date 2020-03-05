# PHP Hot Reloader

This is a class that adds a live reload feature to any php project. It allows you to see your page dynamically changing while coding, without have to keep refreshing the browser on every change.

This Reloader uses a SSE (server-sent events) to listen to changes and notify the browser. So, no built-in server, no custom port, no pain configuration needed. The repository is in Version 1.0, if your trying to find information about the Beta version, please read the "Notes About Beta" on this README.

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

So, you must pass the address that points to your `phrwatcher.php` file as the HotReloader() function param. Is highly recommended to start the HotReloader on some front controller on your app. Also you should deactivate the reloader on production, never keep this feature running on production.

# Example

There is an example file on the root of this repository. The `example.php`. Open this file on your browser to see the Hot Reloader working.

# Notes About the Beta Version X This Version

This is the v1.0 of this Reloader, but there was a Beta version before. On the Beta the approach was to listen changes and notify the browser using "etag" on a custom header. On client a XHR would polling the server till receive a flag about that change. That was a bad idea because the custom headers constantly collides with already sent headers (raising an error), the XHR polling is also bad for the performance and, finally, the JS and CSS was live applied on the page, causing a bug: DOM changes was causing page refresh. So i decided to rewrite the entire class.

Now, as already said, the feature uses a totally different approach with SSE, no polling, no custom headers, etc. So, if you were using the Beta Version, please consider to migrate to this version, it will be very simple. The approach on this version is:

You release an SSE service on some endpoint (Your phrwatcher.php). Then you start the reloader and points to this SSE. A JS will be dropped on the page and will connect to you Server Sent Events Endpoint. This endpoint will be watching your files and notifying the JS about that. The SSE is far better than polling, and far simpler than sockets. So, no custom server, no huge configuration, no pain.