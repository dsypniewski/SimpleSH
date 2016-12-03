<?php

class Terminal_Sh extends Terminal_DynamicOutputShell
{

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('sh');
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
		$command = rtrim($command, "\t\n\r\0\x0B; ");
		$command = "( {$command}; return_value=$?; echo; pwd; echo \$return_value ) 2>&1";

		return "{$this->getInterpreterPath()} -c {$this->escapeShellArg($command)}";
	}

	/**
	 * @return string
	 */
	public function getPrompt()
	{
		return trim(self::_execute('echo -n "[ $(whoami)@$(hostname) ] $([ $(id -u) -eq 0 ] && echo \# || echo \$)";')) . ' ';
	}

	/**
	 * @param string $command
	 * @param string $reference
	 * @return string
	 */
	protected function prepareDynamicOutputCommand($command, $reference)
	{
		$paths = $this->getReferencePaths($reference);

		return "(({$command}; echo \$status >{$paths['done']} 2>/dev/null; echo \$pwd >>{$paths['done']} 2>/dev/null) >{$paths['output']} 2>&1)& echo \$! >{$paths['pid']}";
	}

	/**
	 * @param string $reference
	 * @return Result
	 */
	public function getDynamicOutputResult($reference)
	{
		$paths = $this->getReferencePaths($reference);
		$result = new Result();

		// Check if pid exists
		if (!file_exists($paths['pid'])) {
			$result->setReturnValue(255);

			return $result;
		}

		// Get new output
		if (file_exists($paths['offset'])) {
			$skipLines = (int) file_get_contents($paths['offset']);
			$output = $this->_execute("tail -n+{$skipLines} '{$paths['output']}'");
			$output = PlatformTools::splitLines($output);
		} else {
			$skipLines = 1;
			$output = file($paths['output'], FILE_IGNORE_NEW_LINES);
		}

		$lastLine = count($output) - 1;
		if (file_exists($paths['done'])) {
			$status = file($paths['done'], FILE_IGNORE_NEW_LINES);
			$result->setData('directory', array_pop($status));
			$result->setReturnValue(array_pop($status));
			if ($lastLine >= 0 and mb_strlen(trim($output[$lastLine])) === 0) {
				unset($output[$lastLine]);
			}
			$this->clearReferenceCacheFiles($reference);
		} else {
			// Remove last line because it might not be complete yet
			if ($lastLine >= 0) {
				unset($output[$lastLine]);
			}
			if (count($output) > 0) {
				file_put_contents($paths['offset'], $skipLines + count($output));
			}
		}

		if (count($output) > 0) {
			array_walk($output, 'rtrim');
			$result->setResult(join("\n", $output));
		}

		return $result;
	}

	/**
	 * @param string $reference
	 * @return Result
	 */
	public function killDynamicOutputProcess($reference)
	{
		$paths = $this->getReferencePaths($reference);

		$result = new Result();
		if (file_exists($paths['pid'])) {
			$processPid = trim(file_get_contents($paths['pid']));
			$this->_execute("ps -o pid --ppid {$this->escapeShellArg($processPid)} --no-heading | xargs kill -9");
			$this->clearReferenceCacheFiles($reference);
			$result->setReturnValue(0);
			$result->setData('pid', $processPid);
		} else {
			$result->setReturnValue(255);
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getNewReference()
	{
		$path = PlatformTools::getWritableTmpDir();
		if ($path === false) {
			return false;
		}

		return $path . DIRECTORY_SEPARATOR . uniqid('reference_', true);
	}

	/**
	 * @param string $reference
	 */
	protected function clearReferenceCacheFiles($reference)
	{
		$paths = $this->getReferencePaths($reference);
		foreach ($paths as $path) {
			@unlink($path);
		}
	}

	/**
	 * @param string $reference
	 * @return array
	 */
	protected function getReferencePaths($reference)
	{
		return array(
			'done'   => $reference . '.done',
			'output' => $reference . '.output',
			'offset' => $reference . '.offset',
			'pid'    => $reference . '.pid',
		);
	}

}
