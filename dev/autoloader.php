<?php

// Autoloader for PHPUnit and dev/shell.php
spl_autoload_register(function($className) {
	/** @var DirectoryIterator $fileInfo */
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY) as $fileInfo) {
		if ($fileInfo->getFilename() !== $className . '.php') {
			continue;
		}
		/** @noinspection PhpIncludeInspection */
		require_once $fileInfo->getPathname();
	}
});
