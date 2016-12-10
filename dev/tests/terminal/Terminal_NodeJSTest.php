<?php

class Terminal_NodeJSTest extends PHPUnit_Framework_TestCase
{

	/** @var Terminal_NodeJS */
	protected $_instance;
	
	protected function setUp()
	{
		$this->_instance = new Terminal_NodeJS();
		$this->_instance->setInterpreterPath('/usr/bin/node');
	}

	public function testCommand()
	{
		$result = $this->_instance->execute('process.stdout.write("a")');
		$this->assertEquals('a', $result->getData('result'));
		$this->assertEquals(0, $result->getData('returnValue'));
	}
	
}
