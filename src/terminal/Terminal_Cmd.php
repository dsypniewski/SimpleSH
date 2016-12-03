<?php

class Terminal_Cmd extends Terminal_Shell
{

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('cmd.exe');
	}

	/**
	 * @return array
	 */
	public static function getPlatforms()
	{
		return array();
	}

	/**
	 * @param string $command
	 * @return string
	 */
	protected function prepareCommand($command)
	{
		return "( ( {$command} ) & set return_value=%errorlevel% & echo. & cd & echo %return_value% ) 2>&1";
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return trim(self::_execute('@echo off && echo [ %username%@%computername% ] ^>')) . ' ';
	}

}
