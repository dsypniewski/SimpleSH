<?php

class Terminal_PowerShell extends Terminal_Shell
{

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
		$command = "{$command}; Set-Variable status ([int]-not$?); Write-Host \"`n\"; (Get-Item -Path \".\\\" -Verbose).FullName; echo \$status;";

		return "powershell.exe -NoLogo -NonInteractive -InputFormat text -OutputFormat text -Command {$this->escapeShellArg($command)}";
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return trim(self::_execute('Write-Host [ $env:UserName@$(hostname) ]')) . ' ';
	}

}
