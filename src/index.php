<?php require_once(__DIR__.'/lib/root/precms.php'); ?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type='text/css' href="config/layout.css" />
		<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Comfortaa' />
		<link rel="icon" type="image/png" href="img/logo/logo128.png">

		<script src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization', 'version':'1', 'packages':['corechart']}]}"></script>

		<script src="lib/js/jquery-2.1.4.js"></script>
		<script src="lib/js/playsound.js"></script>

		<script src="lib/js/library_main.js"></script>
		<script src="lib/js/foo.js"></script>
		<script src="lib/js/init.js"></script>

		<script src="lib/js/fb_foo.js"></script>
		<script src="lib/js/fb_init.js"></script>

	</head>
	<body>

		<div id="main">
			<div id="header">
				<div id="logo_top" style="display: inline; float: left;"><a href=""><img src="img/logo/logo128.png" class="img_logo_top" /> Agado.pl</a></div>
				<div id="signin" style="display: inline; float: right;"></div>
				<div id="userbox" style="display: inline; float: right;"></div>
			</div>

			<div id="playground"></div>

			<div id="loader">
				<p><img src="img/logo/logo128.png" /></p>
				<p class="maxitext bold">Agado.pl</p>
				<p>Shared property profits</p>
				<p><img src="img/anime/loading.gif" /></p>
			</div>
			<div id="hiw"></div>
			<div id="conditions"></div>
			<div id="starter"></div>
			<div id="shadow" onClick="windowHide();"></div>
			<div id="window"></div>
			<div id="error"></div>
		</div>

		<script>
			init();
		</script>
	</body>
</html>

<?php
/*

*/
?>