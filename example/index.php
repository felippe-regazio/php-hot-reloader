<!DOCTYPE html>
<html>
<head>
	<title>Hot Reload Example</title>
	<link rel="stylesheet" type="text/css" href="example.css">
</head>
<body>
	<div>
		<?php require "example.php"; ?>
	</div>
	<script src="example.js" type="text/javascript"></script>
</body>
<?php
	require "../hotreloader.php";
	@$reloader = new HotReloader();
	@$reloader->init();
  print_r(get_included_files());
?>
</html>