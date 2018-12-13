# PHP HOT RELOAD  
This single file add the hot reload feature to php projects  

This file is divided in two parts, the php and javascript part. In the
the script part are the livereload.js script raw code added, and this will assist
the current address which is running for 500-500ms (interval), checking for changes in headers.  

This file will trigger a browser reload if has any changed in html, css, js or scss files
or any change in the documment header. The check, as said, are made for 500/500 ms.  

But the same cant be done with server side extensions cause they`re most of time dynamic builded 
on server side. To solve it, we use the php part of this script. We create a hash from a given 
directory list, and sends it in the etag header. So, everytime this directories suffer any change,
the hash will change, so the etag on header will change, so the livereload.js will trigger a reload.  

As the livereload check for changes in the headers either. If the livereload compare a 
difference in etag(hash) on the doc headers, its because somehing has changed on some dir 
and the page will be automatically Reloaded. This script will must only run in development mode  
