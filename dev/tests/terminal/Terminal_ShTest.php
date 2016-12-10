<?php

class Terminal_ShTest extends PHPUnit_Framework_TestCase
{

	/** @var Terminal_Sh */
	protected $_instance;
	
	protected function setUp()
	{
		$this->_instance = new Terminal_Sh;
		$this->_instance->setInterpreterPath('/bin/sh');
	}

	public function testCommand()
	{
		$result = $this->_instance->execute('echo a');
		$this->assertEquals('a', $result->getData('result'));
		$this->assertEquals(0, $result->getData('returnValue'));
	}
	
}
