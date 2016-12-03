<?php

class PlatformTools
{

	const ALL = 1;
	const WINDOWS = 2;
	const LINUX = 3;

	protected static $_writableTmpDir = null;
	protected static $_isWindows = null;

	/**
	 * @return bool
	 */
	public static function isWindows()
	{
		if (self::$_isWindows === null) {
			self::$_isWindows = (strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN');
		}

		return self::$_isWindows;
	}

	/**
	 * @param string $arg
	 * @return string
	 */
	public static function escapeShellArg($arg)
	{
		return escapeshellarg($arg);
	}

	/**
	 * @param string $input
	 * @return string
	 */
	public static function fixNewLines($input)
	{
		return str_replace(array("\r\n", "\r"), "\n", $input);
	}

	/**
	 * @param string $input
	 * @param bool $fixNewLines
	 * @return array
	 */
	public static function splitLines($input, $fixNewLines = true)
	{
		if ((bool) $fixNewLines) {
			$input = self::fixNewLines($input);
		}
		if (mb_strlen($input) === 0) {
			return array();
		}

		return explode("\n", $input);
	}

	/**
	 * @return string
	 */
	public static function getStatusCommandPart()
	{
		if (self::isWindows()) {
			return ' & echo %errorlevel%';
		} else {
			return '; echo $?';
		}
	}

	/**
	 * @return bool|string
	 */
	public static function getWritableTmpDir()
	{
		if (self::$_writableTmpDir === null) {
			self::$_writableTmpDir = false;
			$dirs = array(
				sys_get_temp_dir(),
				ini_get('upload_tmp_dir'),
			);
			foreach ($dirs as $dir) {
				if (is_writable($dir)) {
					self::$_writableTmpDir = $dir;
					break;
				}
			}
		}

		return self::$_writableTmpDir;
	}

}
