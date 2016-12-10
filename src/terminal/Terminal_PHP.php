<?php

class Terminal_PHP extends Terminal_Shell
{

	const COMMAND_SEPARATOR = '; ';
	
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
		$command = array(
			rtrim($command, "\t\n\r\0\x0B; "),
			'echo "\\n\\n" . getcwd()',
		);
		$this->beforePrepareCommandJoin($command);
		$command = join(self::COMMAND_SEPARATOR, $command) . self::COMMAND_SEPARATOR;

		return "({$this->getInterpreterPath()} -r {$this->escapeShellArg($command)}) 2>&1" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'php> ';
	}

}
