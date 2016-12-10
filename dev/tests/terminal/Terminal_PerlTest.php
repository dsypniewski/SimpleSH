<?php

class Terminal_PerlTest extends PHPUnit_Framework_TestCase
{

	/** @var Terminal_Perl */
	protected $_instance;

	protected function setUp()
	{
		$this->_instance = new Terminal_Perl;
		$this->_instance->setInterpreterPath('/usr/bin/perl');
	}

	public function testCommand()
	{
		$result = $this->_instance->execute('print "a"');
		$this->assertEquals('a', $result->getData('result'));
		$this->assertEquals(0, $result->getData('returnValue'));
	}
	
}
