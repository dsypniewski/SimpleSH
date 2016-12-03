<?php

class Terminal_Perl extends Terminal_Shell
{

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('perl');
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
		return "{$this->getInterpreterPath()} -e {$this->escapeShellArg($command)}" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'perl> ';
	}

}
