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
	$reloader->setRoot(__DIR__);
	$reloader->ignore([
		"ignored.php"
	]);
	$reloader->init();
?>
</html>