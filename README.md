# PHP HOT RELOAD  

This single file adds the hot reload feature to php projects
Please configure the proper consts in php-hot-reload.php file
Include this file in your page header, and be happy.  

This file is divided in two parts, the php and javascript part. 
The javascript part holds the livereload.js raw code, which will assist
the current address every 500ms(interval), checking for changes in headers
Or in the given files extensions (html, css, scss, js, php) by default.

This file will trigger a browser reload if has any change in the setted extensions
or any change in the documment header. The check, as said, are made for 500/500 ms.  

But the same cant be done with server side code cause they`re most dynamically builded 
on server side, cause a file check always remains the same, event the final code is different. 
To solve this problem, we use the php part of this script. We create a hash from a given 
directory list, and sends it in the etag header. So, everytime this directories suffer any change,
the hash will change, so the etag on header will change and the livereload.js will trigger a reload.  

You can change the interval of the checkings in the var "interval" on <script> part, if your
application suffer. You must be careful with the folder list to assist on your $watch array.

USE THIS SCRIPT ONLY FOR DEVELOPMENT PROCESS. BE CAREFUL.