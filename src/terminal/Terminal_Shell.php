<?php

abstract class Terminal_Shell implements Terminal_ShellInterface
{

	const COMMAND_SEPARATOR = '';
	
	protected $_interpreterPath = null;
	protected $_terminalId = null;
	protected $_environmentFilePath = null;

	/**
	 * @param string $path
	 */
	final public function setInterpreterPath($path)
	{
		$this->_interpreterPath = $path;
	}

	/**
	 * @return null|string
	 */
	final public function getInterpreterPath()
	{
		return $this->_interpreterPath;
	}

	/**
	 * @param string $command
	 * @param null|string $cwd
	 * @return string
	 * @throws Exception
	 */
	final public static function _execute($command, $cwd = null)
	{
		if (is_string($cwd) and mb_strlen($cwd) > 0) {
			$_dir = getcwd();
			@chdir($cwd);
		}
		if (function_exists('shell_exec')) {
			$result = shell_exec($command);
		} else if (function_exists('exec')) {
			exec($command, $result);
			$result = join("\n", $result);
		} else if (function_exists('passthru')) {
			ob_start();
			passthru($command);
			$result = ob_get_clean();
		} else if (function_exists('system')) {
			ob_start();
			system($command);
			$result = ob_get_clean();
		} else if (function_exists('popen')) {
			$process = popen($command, 'r');
			$result = stream_get_contents($process);
			pclose($process);
		} else if (function_exists('proc_open')) {
			$process = proc_open($command, array(
				0 => array('pipe', 'r'), // STDIN
				1 => array('pipe', 'w'), // STDOUT
				2 => array('pipe', 'w'), // STDERR
			), $pipes);
			fclose($pipes[0]);
			$result = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);
		} else {
			throw new Exception('Command execution not possible');
		}
		if (isset($_dir)) {
			@chdir($_dir);
		}

		return $result;
	}

	/**
	 * @param string $terminalId
	 */
	public function setTerminalId($terminalId)
	{
		if (!is_string($terminalId) or strlen($terminalId) === 0) {
			return;
		}
		$this->_terminalId = $terminalId;

		$tmpDir = PlatformTools::getWritableTmpDir();
		if ($tmpDir === false) {
			return;
		}
		$this->_environmentFilePath = $tmpDir . DIRECTORY_SEPARATOR . "terminal_env_{$this->_terminalId}";
		if (!file_exists($this->_environmentFilePath)) {
			touch($this->_environmentFilePath);
		}
	}

	/**
	 * @param string $command
	 * @param null|string $cwd
	 * @return Result
	 */
	final public function execute($command, $cwd = null)
	{
		$preparedCommand = $this->prepareCommand($command);
		$output = self::_execute($preparedCommand, $cwd);
		$output = PlatformTools::splitLines(rtrim($output));
		$returnValue = array_pop($output);
		$directory = array_pop($output);

		// if last line is empty remove it,
		// this is side effect of prepareCommand protecting against missing new line after command output
		if (strlen(trim(end($output))) === 0) {
			unset($output[key($output)]);
		}
		$output = join("\n", $output);

		$result = new Result();
		$result->setResult($output);
		$result->setReturnValue($returnValue);
		$result->setData('command', $command);
		$result->setData('directory', $directory);
		$result->setData('prompt', $this->getPrompt());
		$result->setData('debug:real_command', $preparedCommand);

		return $result;
	}

	/**
	 * @param array $command
	 */
	protected function beforePrepareCommandJoin(&$command)
	{
	}

	/**
	 * @return array
	 */
	public function initTerminal()
	{
		return array(
			'terminal_id' => uniqid('', true),
		);
	}

	public function closeTerminal()
	{
		if ($this->_terminalId === null) {
			return;
		}
		@unlink($this->_environmentFilePath);
	}

	/**
	 * @param string $arg
	 * @return string
	 */
	public function escapeShellArg($arg)
	{
		return PlatformTools::escapeShellArg($arg);
	}

	/**
	 * Adds following things to command, each in new line:
	 * empty line to protect added data from missing new line at the end of command output
	 * working directory after command execution
	 * exit code of command
	 *
	 * @param string $command
	 * @return string
	 */
	abstract protected function prepareCommand($command);

	/**
	 * @return string
	 */
	abstract public function getPrompt();

}
