<?php

set_time_limit(0);

chdir('..');

// Autoloader for including parent classes and interfaces
/** @noinspection PhpIncludeInspection */
require_once 'dev/autoloader.php';

// Loading all php files because we use get_declared_classes()
/** @var DirectoryIterator $fileInfo */
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY) as $fileInfo) {
	if (!$fileInfo->isFile()) {
		continue;
	}
	$extension = pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION);
	if ($extension === 'php') {
		/** @noinspection PhpIncludeInspection */
		require_once $fileInfo->getPathname();
	}
}

$handler = new Handler();
$handler->handleRequest();
$handler->initializeModules();
?>
<html>
	<head>
		<title>SimpleSH</title>
		<meta charset="utf-8"/>
		<link rel="stylesheet" type="text/css" href="../build/build.css">
		<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
		<script src="../build/modules.js"></script>
		<link rel="icon" type="image/png" href="?favicon"/>
	</head>
	<body>
		<div id="content"></div>
		<script>
			"use strict";
			var windowManager;

			$(function () {
				windowManager = new WindowManager('#content', <?php echo json_encode($handler->getModulesData()); ?>);
			});
		</script>
	</body>
</html>
