<?php

interface Terminal_ShellInterface
{

	/**
	 * @return array
	 */
	public static function getBinaries();

	/**
	 * @return array
	 */
	public static function getPlatforms();

}
