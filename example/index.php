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
	<!-- note that ignored.js has the hidden attr,
	so will be ignore by reloader -->
	<script src="ignored.js" type="text/javascript" hidden></script>
</body>
<!-- Here we start the reloader, read the
	REAMDE.ms for further details -->
<?php
	require "../hotreloader.php";
	$reloader = new HotReloader();
	$reloader->setRoot(__DIR__);
	$reloader->ignore([
		"ignored.php"
	]);
	$reloader->currentConfig();
	$reloader->init();
?>
</html>