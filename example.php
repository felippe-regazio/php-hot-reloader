<!DOCTYPE html>
<html>
<head>
	<title>PHP Hot Reload Example</title>
</head>
<body>
	<section class="live-example">
		<p><strong>With this page opened in a browser, <br/>change anything on its source content.</strong></p>
		<p>Last load: <?php echo date("H:i:s");?></p>
	</section>
</body>
	<?php

		require "src/HotReloader.php";
		new HotReloader\HotReloader('//localhost/php-hot-reloader/phrwatcher.php');

	?>
</html>
