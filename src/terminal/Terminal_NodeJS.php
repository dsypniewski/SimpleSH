<?php

class Terminal_NodeJS extends Terminal_Shell
{

	const COMMAND_SEPARATOR = '; ';

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
		$command = array(
			rtrim($command, "\t\n\r\0\x0B; "),
			'process.stdout.write("\n")',
			'process.stdout.write(process.cwd() + "\n")',
		);
		$this->beforePrepareCommandJoin($command);
		$command = join(self::COMMAND_SEPARATOR, $command) . self::COMMAND_SEPARATOR;

		return "({$this->getInterpreterPath()} -e {$this->escapeShellArg($command)}) 2>&1" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'node> ';
	}

}
