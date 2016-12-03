<?php

class Terminal_NodeJS extends Terminal_Shell
{

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('node');
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
		return "{$this->getInterpreterPath()} -p {$this->escapeShellArg($command)}" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'node> ';
	}

}
