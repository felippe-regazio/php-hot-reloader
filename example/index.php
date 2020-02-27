<!DOCTYPE html>
<html>
<head>
	<title>Hot Reload Example</title>
</head>
<body>
	<section class="live-example">
		<p>With this page opened in a browser, <br/>change this phrase, or any of its related files.</p>
	</section>
</body>
	<?php

		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		require "./../src/HotReloader.php";
		new HotReloader\HotReloader();

	?>
</html>