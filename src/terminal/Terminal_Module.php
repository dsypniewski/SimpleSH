<?php

class Terminal_Module extends Module
{

	const MODULE_KEY = 'terminal';

	/** @var Terminal_Shell */
	protected $_shell;
	protected $_currentDirectory;
	protected $_dynamicOutputEnabled;

	public function __construct()
	{
		$this->initialize();
	}

	/**
	 * @return array
	 */
	public function getModuleData()
	{
		return array(
			'name'            => 'Terminal',
			'jsClass'         => 'Terminal',
			'moduleKey'       => self::MODULE_KEY,
			'prompt'          => $this->_shell->getPrompt(),
			'directory'       => $this->_currentDirectory,
			'shellClass'      => get_class($this->_shell),
			'interpreterPath' => $this->_shell->getInterpreterPath(),
			'shells'          => $this->getAvailableShells(),
		);
	}

	public function handleRequest()
	{
		if (isset($_POST['terminal_id'])) {
			$this->_shell->setTerminalId($_POST['terminal_id']);
		}
		if (isset($_POST['init'])) {
			$result = $this->handleInitRequest();
		} else if (isset($_POST['close']) and isset($_POST['terminal_id'])) {
			$result = $this->handleCloseRequest();
		} else if (isset($_POST['reference'])) {
			if (isset($_POST['kill'])) {
				$result = $this->handleKillDynamicOutputProcess($_POST['reference']);
			} else {
				$result = $this->handleDynamicOutputRequest($_POST['reference']);
			}
		} else if (isset($_POST['command'])) {
			if (isset($_POST['autocomplete'])) {
				$result = $this->handleAutocompleteRequest($_POST['command'], $_POST['cursorPosition']);
			} else if ($this->_dynamicOutputEnabled) {
				$result = $this->handleDynamicOutputCommandRequest($_POST['command']);
			} else {
				$result = $this->handleCommandRequest($_POST['command']);
			}
		}
		if (isset($result) and $result instanceof Result) {
			return $result;
		}

		return false;
	}

	protected function initialize()
	{
		setlocale(LC_ALL, 'en_US.utf8');
		putenv('LC_ALL=en_US.utf8');
		ini_set('mbstring.internal_encoding', 'UTF-8');
		ini_set('mbstring.http_input', 'UTF-8');
		ini_set('mbstring.http_output', 'UTF-8');
		ini_set('default_charset', 'UTF-8');

		if (isset($_POST['shellClass']) and isset($_POST['interpreterPath'])) {
			$shell = $this->getShell($_POST['shellClass'], $_POST['interpreterPath']);
		} else {
			$shells = $this->getAvailableShells();
			if (array_key_exists('bash', $shells)) {
				$shellBinary = $shells['bash'];
			} else if (array_key_exists('sh', $shells)) {
				$shellBinary = $shells['sh'];
			} else {
				$shellBinary = reset($shells);
			}
			$shell = $this->getShell($shellBinary['shellClass'], $shellBinary['interpreterPath']);
		}
		if (!($shell instanceof Terminal_Shell)) {
			throw new Exception('Invalid shell');
		}
		$this->_shell = $shell;

		$this->_dynamicOutputEnabled = false;
		if (isset($_POST['dynamicOutput']) and $this->_shell instanceof Terminal_DynamicOutputShell and $this->_shell->isDynamicOutputPossible()) {
			$this->_dynamicOutputEnabled = true;
		}

		if (isset($_POST['cwd']) and mb_strlen($_POST['cwd']) > 0) {
			$this->_currentDirectory = $_POST['cwd'];
		} else {
			$this->_currentDirectory = getcwd();
		}
	}

	/**
	 * @return array
	 */
	protected function getAvailableShells()
	{
		if (!isset($this->_availableShells)) {
			$this->_availableShells = array();
			foreach (get_declared_classes() as $className) {
				if (!is_subclass_of($className, 'Terminal_Shell') or $className === 'Terminal_Shell' or $className === 'Terminal_ShellInterface' or $className === 'Terminal_DynamicOutputShell') {
					continue;
				}
				$binaries = call_user_func(array($className, 'getBinaries'));
				foreach ($binaries as $binary) {
					$interpreterPath = $this->getInterpreterPath($binary);
					if ($interpreterPath !== false) {
						$this->_availableShells[$binary] = array(
							'shellClass'      => $className,
							'interpreterPath' => $interpreterPath,
						);
					}
				}
			}
		}

		return $this->_availableShells;
	}

	/**
	 * @param string $className
	 * @param string $interpreterPath
	 * @return bool|Terminal_Shell
	 */
	public function getShell($className, $interpreterPath)
	{
		$shell = false;
		if (class_exists($className)) {
			$reflection = new ReflectionClass($className);
			if ($reflection->isSubclassOf('Terminal_Shell') and !$reflection->isAbstract()) {
				/** @var Terminal_Shell $shell */
				$shell = new $className();
				$shell->setInterpreterPath($interpreterPath);
			}
		}

		return $shell;
	}

	/**
	 * @param string $binary
	 * @return bool|string
	 */
	public function getInterpreterPath($binary)
	{
		$escapedBinary = PlatformTools::escapeShellArg($binary);
		if (PlatformTools::isWindows()) {
			$binaryPath = Terminal_Shell::_execute("where {$escapedBinary} 2>/nul");
		} else {
			$binaryPath = Terminal_Shell::_execute("which {$escapedBinary} 2>/dev/null");
		}
		$binaryPath = trim($binaryPath);
		if (strlen($binaryPath) === 0) {
			return false;
		}

		return $binaryPath;
	}

	/**
	 * @param string $command
	 * @return Result
	 */
	protected function handleCommandRequest($command)
	{
		return $this->_shell->execute($command, $this->_currentDirectory);
	}

	/**
	 * @param string $command
	 * @param int $cursorPosition
	 * @return Result|bool
	 */
	protected function handleAutocompleteRequest($command, $cursorPosition)
	{
		if (!$this->_shell instanceof Terminal_AutocompleteInterface) {
			return false;
		}

		return $this->_shell->autocomplete($command, $cursorPosition, $this->_currentDirectory);
	}

	/**
	 * @param string $command
	 * @return Result|bool
	 */
	protected function handleDynamicOutputCommandRequest($command)
	{
		if (!$this->_shell instanceof Terminal_DynamicOutputShell) {
			return false;
		}

		return $this->_shell->executeDynamicOutputCommand($command, $this->_currentDirectory);
	}

	/**
	 * @param string $reference
	 * @return Result|bool
	 */
	protected function handleDynamicOutputRequest($reference)
	{
		if (!$this->_shell instanceof Terminal_DynamicOutputShell) {
			return false;
		}

		return $this->_shell->getDynamicOutputResult($reference);
	}

	/**
	 * @param string $reference
	 * @return Result|bool
	 */
	protected function handleKillDynamicOutputProcess($reference)
	{
		if (!$this->_shell instanceof Terminal_DynamicOutputShell) {
			return false;
		}

		return $this->_shell->killDynamicOutputProcess($reference);
	}

	/**
	 * @return Result
	 */
	protected function handleInitRequest()
	{
		$data = $this->_shell->initTerminal();
		
		return new Result($data);
	}

	/**
	 * @return Result
	 */
	protected function handleCloseRequest()
	{
		$this->_shell->closeTerminal();
		
		return new Result(array('success' => 0));
	}

}
