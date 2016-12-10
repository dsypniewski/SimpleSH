<?php

class Terminal_PythonTest extends PHPUnit_Framework_TestCase
{

	/** @var Terminal_Python */
	protected $_instance;
	
	protected function setUp()
	{
		$this->_instance = new Terminal_Python();
		$this->_instance->setInterpreterPath('/usr/bin/python');
	}

	public function testCommand()
	{
		$result = $this->_instance->execute('print("a")');
		$this->assertEquals('a', $result->getData('result'));
		$this->assertEquals(0, $result->getData('returnValue'));
	}
	
}
