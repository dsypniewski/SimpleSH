<?php

class Terminal_PHPTest extends PHPUnit_Framework_TestCase
{

	/** @var Terminal_PHP */
	protected $_instance;

	protected function setUp()
	{
		$this->_instance = new Terminal_PHP();
		$this->_instance->setInterpreterPath('/usr/bin/php');
	}

	public function testCommand()
	{
		$result = $this->_instance->execute('echo("a")');
		$this->assertEquals('a', $result->getData('result'));
		$this->assertEquals(0, $result->getData('returnValue'));
	}
	
}
