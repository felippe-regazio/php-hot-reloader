# PHP HOT RELOADER

This is a tiny class which adds the live reload feature to any php project. Its just a php hash of all included files on your code script and a live.js script with a few modifications. When the hash or any of your assets has changed, the page will automatically reload.

# USAGE

This class accepts a few options, but the simplier and faster way to use it is require the class file (or add the class in your project somehow); then add the following code before your \</html> tag:

```php
<?php
require "../hotreloader.php";
@$reloader = new HotReloader();
@$reloader->init();
?>
```

Some notes: remember to add the code BEFORE your \</html>. Note that is your CLOSING script tag. If you add any include or require after the reloader->init, it wont be watched. The assets like js and css files are watched by live.js wrapped on this class.

# HOW IT WORKS

This script is divided in two parts. The PHP part and the JAVASCRIPT part. The Javascript uses the live.js reload script (http://livejs.com/), with a few modifications. The script uses the etag key on document headers to store a hash of all included files, so, if any file related to your current code changes, the hash will change. On the client side, the live.js will be watching the current document every 1 sec. looking for changes in the js, css, html files and in the document headers. So, when you change a file or when the etag sends a different hash, the live.js will trigger a page reload.  

By default, the class only assists the included files and its assets waiting for changes, and the hash of this contents and its  assets is created using an md5 of all related files timestamps. Its possible to change this behavior, and the class default behavior with a few options.

# OPTIONS

There are a few methods to get and set the reloader behavior:

- SetDiffMode( String $mode );

This method is used to change the way the reloader will generate a fingerprint of the files. 

