<?php

class Terminal_Cmd extends Terminal_Shell
{

	const COMMAND_SEPARATOR = ' & ';
	
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
		$command = array(
			"( $command )",
			'set return_value=%errorlevel%',
			'echo.',
			'cd',
			'echo %return_value%',
		);
		$this->beforePrepareCommandJoin($command);
		$command = '( ' . join(self::COMMAND_SEPARATOR, $command) . ' ) 2>&1';

		return "{$this->getInterpreterPath()} /Q /C {$this->escapeShellArg($command)}";
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return trim(self::_execute('@echo off && echo [ %username%@%computername% ] ^>')) . ' ';
	}

}
