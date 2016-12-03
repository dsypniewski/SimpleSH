<?php

class Terminal_PHP extends Terminal_Shell
{

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('php', 'php5');
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
		$command .= ' echo "\\n" . getcwd();';

		return "{$this->getInterpreterPath()} -r {$this->escapeShellArg($command)}" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'php> ';
	}

}
