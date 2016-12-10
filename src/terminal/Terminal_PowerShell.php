<?php

class Terminal_PowerShell extends Terminal_Shell
{

	const COMMAND_SEPARATOR = '; ';
	
	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('powershell.exe');
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
			$command,
			'Set-Variable status ([int]-not$?)',
			'Write-Host "`n"',
			'(Get-Item -Path ".\\" -Verbose).FullName',
			'echo $status',
		);
		$this->beforePrepareCommandJoin($command);
		$command = join(self::COMMAND_SEPARATOR, $command) . self::COMMAND_SEPARATOR;

		return "{$this->getInterpreterPath()} -NoLogo -NonInteractive -InputFormat text -OutputFormat text -Command {$this->escapeShellArg($command)}";
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return trim(self::_execute('Write-Host [ $env:UserName@$(hostname) ]')) . ' ';
	}

}
