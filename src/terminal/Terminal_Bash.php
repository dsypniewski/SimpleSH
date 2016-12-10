<?php

class Terminal_Bash extends Terminal_Sh implements Terminal_AutocompleteInterface
{

	/**
	 * @return array
	 */
	public static function getBinaries()
	{
		return array('bash');
	}

	/**
	 * @return array
	 */
	public static function getPlatforms()
	{
		return array();
	}

	/**
	 * @param array $command
	 */
	protected function beforePrepareCommandJoin(&$command)
	{
		if ($this->_environmentFilePath !== null) {
			$escapedEnvironmentFilePath = $this->escapeShellArg($this->_environmentFilePath);
			array_unshift($command, "source {$escapedEnvironmentFilePath}");
			$command[] = "(declare -p | grep -v '^declare -[^\\s]*r') >{$escapedEnvironmentFilePath} 2>/dev/null";
		}
	}

	/**
	 * @param string $command
	 * @param int $cursorPosition
	 * @param null|string $cwd
	 * @return Result
	 */
	public function autocomplete($command, $cursorPosition, $cwd = null)
	{
		$commandPart = mb_substr($command, 0, $cursorPosition);

		// Check if single tab has results
		$output = $this->getAutocompleteResult($commandPart, $cwd, false);
		if (count($output) === 1 and trim($output[0]) !== $commandPart) {
			// Complete command
			$completedPart = $output[0];
		} else {
			// Double tab is required
			$output = $this->getAutocompleteResult($commandPart, $cwd, true);
			if (count($output) === 1) {
				// Complete command
				$completedPart = preg_replace('#(y(\[K|\s+)?)$#u', '', reset($output));
			} else if (count($output) > 1) {
				// Possible completions
				$firstLine = array_shift($output);
				$lastLine = end($output);
				if (mb_strpos($lastLine, $firstLine) === 0 and preg_match('#^(y?(\[K|\s+)?)$#u', mb_substr($lastLine, mb_strlen($firstLine)))) {
					array_pop($output);
				}
				$list = join("\n", $output);
			}
		}

		$result = new Result();
		if (isset($completedPart)) {
			$result->setData('command', $completedPart . mb_substr($command, $cursorPosition));
			$result->setData('caretPosition', mb_strlen($completedPart));
			$result->setReturnValue(0);
		} else if (isset($list)) {
			$result->setResult($list);
			$result->setReturnValue(0);
		} else {
			$result->setReturnValue(1);
		}

		return $result;
	}

	/**
	 * @param string $command
	 * @param string|null $cwd
	 * @param bool $doubleTab
	 * @return string[]
	 */
	protected function getAutocompleteResult($command, $cwd = null, $doubleTab = false)
	{
		$completionPart = $this->escapeShellArg("#($command");
		if ($doubleTab) {
			$completionPart .= '$\'\\t\\ty\'';
		} else {
			$completionPart .= '$\'\\t\'';
		}
		$autocompleteCommand = "echo {$completionPart} | TERM=dumb PS1= COLUMNS=200 {$this->getInterpreterPath()} -O interactive_comments -i -n 2>&1 | head -n-1";
		$autocompleteCommand = "{$this->getInterpreterPath()} -c {$this->escapeShellArg($autocompleteCommand)}";
		$output = self::_execute($autocompleteCommand, $cwd);
		$output = str_replace(array("\x07", "\x00", "\x08", "\x1B"), '', rtrim($output, "\n"));
		$output = PlatformTools::splitLines($output);
		while (count($output) > 0 and mb_substr($output[0], 0, 5) == 'bash:') {
			array_shift($output);
		}
		while (count($output) > 0 and strlen(trim(end($output))) === 0) {
			array_pop($output);
		}
		if (count($output) > 0 and mb_strpos($output[0], '#(') === 0) {
			$output[0] = mb_substr($output[0], 2);
		}
		if (count($output) > 1 and mb_strpos(end($output), '#(') === 0) {
			$output[count($output) - 1] = mb_substr(end($output), 2);
		}

		return $output;
	}

}
