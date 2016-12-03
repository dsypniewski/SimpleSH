<?php

class Terminal_Python extends Terminal_Shell
{

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
		$command = "import os\nimport sys\n{$command}\nprint\nprint(os.getcwd())\nprint(0)";

		return "{$this->getInterpreterPath()} -c {$this->escapeShellArg($command)}" . PlatformTools::getStatusCommandPart();
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return 'python> ';
	}

}
