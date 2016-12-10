<?php

class Terminal_Python extends Terminal_Shell
{

	const COMMAND_SEPARATOR = "\n";

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('python', 'python3');
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
			'import os',
			'import sys',
			$command,
			"print('')",
			'print(os.getcwd())',
		);
		$this->beforePrepareCommandJoin($command);
		$command = join(self::COMMAND_SEPARATOR, $command);

		return "({$this->getInterpreterPath()} -c {$this->escapeShellArg($command)}) 2>&1" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'python> ';
	}

}
