<?php

class Handler
{

	/** @var Module[] */
	protected $_modules = array();

	public function __construct()
	{
		foreach (get_declared_classes() as $className) {
			if (!is_subclass_of($className, 'Module')) {
				continue;
			}
			$moduleKey = constant("{$className}::MODULE_KEY");
			$this->_modules[$moduleKey] = $className;
		}
	}

	public function handleRequest()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}

		header('Content-Type: application/json');

		// Fix magic quotes
		if (function_exists('get_magic_quotes_gpc') and @get_magic_quotes_gpc()) {
			$_POST = array_map('stripslashes', $_POST);
		}

		$moduleInstance = $this->getModule($_POST['module']);
		if ($moduleInstance === false) {
			$result = self::error('Unknown module');
		} else {
			$result = $moduleInstance->handleRequest();
		}

		if (!$result instanceof Result) {
			$result = self::error('Invalid request');
		}

		die($result->toJson());
	}

	/**
	 * @param string $moduleKey
	 * @return bool|Module
	 */
	protected function getModule($moduleKey)
	{
		if (!array_key_exists($moduleKey, $this->_modules)) {
			return false;
		}

		if (is_string($this->_modules[$moduleKey])) {
			$className = $this->_modules[$moduleKey];
			$this->_modules[$moduleKey] = new $className();
		}

		return $this->_modules[$moduleKey];
	}

	public function initializeModules()
	{
		foreach (array_keys($this->_modules) as $moduleKey) {
			$this->getModule($moduleKey);
		}
	}

	/**
	 * @return array
	 */
	public function getModulesData()
	{
		$modulesData = array();
		/** @var Module $moduleInstance */
		foreach (array_keys($this->_modules) as $moduleKey) {
			$moduleInstance = $this->getModule($moduleKey);
			$modulesData[$moduleKey] = $moduleInstance->getModuleData();
		}

		return $modulesData;
	}

	/**
	 * @param string $message
	 * @return Result
	 */
	public static function error($message)
	{
		$result = new Result();
		$result->setData('error', $message);

		return $result;
	}

}
