<!DOCTYPE html>
<html>
<head>
	<title>Hot Reload Example</title>
	<link rel="stylesheet" type="text/css" href="example.css">
</head>
<body>
	<div>
		<?php 
			require "example.php";
			require "ignored.php"; 
		?>
	</div>
	<script src="example.js" type="text/javascript"></script>
	<script src="ignored.js" type="text/javascript" hidden></script>
</body>
<?php
	require "../hotreloader.php";
	$reloader = new HotReloader();
	// if dont dont set the root path, 
	// the hotreloader root will be used
	$reloader->setRoot(__DIR__);
	// you can add files or folders here
	$reloader->ignore([
		"ignored.php"
	]);
	// start the reloader with the given config
	$reloader->init();
?>
</html>