<?php

abstract class Module
{

	const MODULE_KEY = '';

	/**
	 * @return array
	 */
	abstract public function getModuleData();

	/**
	 * @return Result|bool
	 */
	abstract public function handleRequest();

}
