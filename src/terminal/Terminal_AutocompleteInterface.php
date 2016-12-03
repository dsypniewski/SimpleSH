<?php

interface Terminal_AutocompleteInterface
{

	/**
	 * @param string $command
	 * @param int $cursorPosition
	 * @param null|string $cwd
	 * @return Result
	 */
	public function autocomplete($command, $cursorPosition, $cwd = null);

}
