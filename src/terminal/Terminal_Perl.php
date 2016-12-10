<?php

class Terminal_Perl extends Terminal_Shell
{
	
	const COMMAND_SEPARATOR = '; ';

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
		$command = array(
			'use Cwd',
			rtrim($command, "\t\n\r\0\x0B; "),
			'print "\\n"',
			'print getcwd() . "\\n"',
		);
		$this->beforePrepareCommandJoin($command);
		$command = join(self::COMMAND_SEPARATOR, $command);
		
		return "({$this->getInterpreterPath()} -e {$this->escapeShellArg($command)}) 2>&1" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'perl> ';
	}

}
