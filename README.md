# ABOUT THE PHP HOT RELOAD USAGE 

This single file adds the hot reload feature to php projects. Please configure the variables on the script. 
After properly configured, this file must be added on the \<header> of the pages you want to hot reload.

# GETTING STARTED

Configure your $ROOT and your $watch List on vars in this script. Add this script on the php
files you want to hot reload. You must call this file in your \<head> to a better effect.

# PHP HOT RELOAD CONFIGURATION

Please, open the php file and configure the following vars:

$ROOT  = Your $watch dir root folders
$watch = Folders that will trigger a page reload when content changes // relative to $ROOT
You dont need ad subfolders of your watch folders. But your folders on $watch must be subfolders of $ROOT.

Example:

$ROOT  = "root_folder";
$watch = [
  "folder_a",
  "folder_b"
]; 

The script will assist root_folder/folder_a and root_folder/folder_b.

# THE VARIABLE $MODE

The $MODE can be 'md5' or 'mtime'. There are a few differences between them. In 'md5' mode,
the script will make a md5 hash of every entire directory configured to $watch, is exapansive,
but is more exact too. When a directory changes its checksum, the page will automatically reload.

In the 'mtime' (mode), the watch will check for every file modification timestamp in every folder
configured in $watch. If some modification time has changed, the script will trigger a change. This
have a core difference to the first method. In this method you will get a page refresh everytime you
save a file (timestamp changes), in the first method you will get a refresh only if a file really changes.

# HOW IT WORKS

This script is divided in two parts. The PHP part and the JAVASCRIPT part. The Javascript uses the
live.js reload script (http://livejs.com/), with a few modifications. So, how it works? Well, when
you call this file in your header, two things will happen - 1. Your file will always send an 'etag'
on headers, and a javascript will run and keep assisting this address and some files extensions for 
changes every 1 second. The javascript assists changes on html, css, scss, php, js files, and check
the headers for new informations. So, when you change a file, or change the etag checksum, the script
will trigger an automatic reload on the page.

# ABOUT LIVE.JS

Live.js - One script closer to Designing in the Browser
Written for Handcraft.com by Martin Kool (@mrtnkl).

Version 4.
Recent change: Made stylesheet and mimetype checks case insensitive.

http://livejs.com
http://livejs.com/license (MIT)  
@livejs

Include live.js#css to monitor css changes only.
Include live.js#js to monitor js changes only.
Include live.js#html to monitor html changes only.
Mix and match to monitor a preferred combination such as live.js#html,css  

By default, just include live.js to monitor all css, js and html changes.

Live.js can also be loaded as a bookmarklet. It is best to only use it for CSS then,
as a page reload due to a change in html or css would not re-include the bookmarklet.
To monitor CSS and be notified that it has loaded, include it as: live.js#css,notify