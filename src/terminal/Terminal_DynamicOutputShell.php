<?php

abstract class Terminal_DynamicOutputShell extends Terminal_Shell
{

	/**
	 * @return bool
	 */
	public function isDynamicOutputPossible()
	{
		return (PlatformTools::getWritableTmpDir() !== false);
	}

	/**
	 * @param string $command
	 * @param null|string $cwd
	 * @return Result
	 */
	final public function executeDynamicOutputCommand($command, $cwd = null)
	{
		$reference = $this->getNewReference();
		$preparedCommand = $this->prepareDynamicOutputCommand($command, $reference);
		Terminal_Shell::_execute($preparedCommand, $cwd);

		$result = new Result();
		$result->setData('reference', $reference);
		$result->setData('debug:real_command', $preparedCommand);

		return $result;
	}

	/**
	 * @return string
	 */
	abstract protected function getNewReference();

	/**
	 * @param string $command
	 * @param string $reference
	 * @return string
	 */
	abstract protected function prepareDynamicOutputCommand($command, $reference);

	/**
	 * @param string $reference
	 * @return Result
	 */
	abstract public function getDynamicOutputResult($reference);

	/**
	 * @param string $reference
	 * @return Result
	 */
	abstract public function killDynamicOutputProcess($reference);

}
