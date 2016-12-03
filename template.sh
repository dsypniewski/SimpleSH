#!/bin/sh

PHP_BLOCK=$(cat $1);
JS_BLOCK=$(cat $2);
CSS_BLOCK=$(cat $3);

cat << EOF
$PHP_BLOCK
<?php
\$handler = new Handler();
\$handler->handleRequest();
\$handler->initializeModules();
?>
<html>
	<head>
		<title>SimpleSH</title>
		<meta charset="utf-8"/>
		<style>
$CSS_BLOCK
		</style>
		<script>
$JS_BLOCK
		</script>
		<link rel="icon" type="image/png" href="?favicon"/>
	</head>
	<body>
		<div id="content"></div>
		<script>
			"use strict";
			\$(function () {
				new WindowManager('#content', <?php echo json_encode(\$handler->getModulesData()); ?>);
			});
		</script>
	</body>
</html>
EOF
