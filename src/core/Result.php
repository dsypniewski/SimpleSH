<?php

class Result
{

	protected $_data = array();

	/**
	 * Result constructor.
	 * @param array $data
	 */
	public function __construct($data = array())
	{
		$this->_data = $data;
	}

	/**
	 * @param string $result
	 */
	public function setResult($result)
	{
		$this->setData('result', PlatformTools::fixNewLines($result));
	}

	/**
	 * @return string
	 */
	public function getResult()
	{
		return $this->getData('result');
	}

	/**
	 * @param string|int $returnValue
	 */
	public function setReturnValue($returnValue)
	{
		if (!is_integer($returnValue)) {
			$returnValue = (int) $returnValue;
		}
		$this->setData('returnValue', $returnValue);
	}

	/**
	 * @return int|null
	 */
	public function getReturnValue()
	{
		return $this->getData('returnValue');
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setData($key, $value)
	{
		$this->_data[$key] = $value;
	}

	/**
	 * @param string $key
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public function getData($key, $default = null)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : $default;
	}

	/**
	 * @return string
	 */
	public function toJson()
	{
		return json_encode($this->_data);
	}

}
