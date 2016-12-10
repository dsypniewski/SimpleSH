<?php

class Terminal_BashTest extends PHPUnit_Framework_TestCase
{
	
	/** @var Terminal_Bash */
	protected $_instance;

	protected function setUp()
	{
		$this->_instance = new Terminal_Bash();
		$this->_instance->setInterpreterPath('/bin/bash');
	}

	public function testCommand()
	{
		$result = $this->_instance->execute('echo a');
		$this->assertEquals('a', $result->getData('result'));
		$this->assertEquals(0, $result->getData('returnValue'));
	}

	public function testAutocompletePlainCommand()
	{
		$result = $this->_instance->autocomplete('lsa', 3);
		$this->assertEquals('lsattr ', $result->getData('command'));
	}
	
	public function testAutocompleteInsideSubCommand()
	{
		$result = $this->_instance->autocomplete('$(lsa', 5);
		$this->assertEquals('$(lsattr ', $result->getData('command'));
	}
	
	public function testAutocompleteWithCursorInsideCommand()
	{
		$result = $this->_instance->autocomplete('$(lsa); pwd', 5);
		$this->assertEquals('$(lsattr ); pwd', $result->getData('command'));
	}
	
	public function testAutocompleteSingleFile()
	{
		$result = $this->_instance->autocomplete('cat ', 4, 'dev/test-env/terminal/autocomplete/test');
		$this->assertEquals('cat test.txt ', $result->getData('command'));
	}

	public function testAutocompleteSingleFileInsideDirectory()
	{
		$result = $this->_instance->autocomplete('cd test/', 8, 'dev/test-env/terminal/autocomplete');
		$this->assertEquals('cd test/test.txt ', $result->getData('command'));
	}
	
	public function testAutocompletePartialComplete()
	{
		$result = $this->_instance->autocomplete('cd t', 4, 'dev/test-env/terminal/autocomplete');
		$this->assertEquals('cd test', $result->getData('command'));
	}
	
	public function testAutocompleteSingleMatchFile()
	{
		$result = $this->_instance->autocomplete('cat test.', 9, 'dev/test-env/terminal/autocomplete');
		$this->assertEquals('cat test.txt ', $result->getData('command'));
	}
	
	public function testAutocompleteDirectoryContentsList()
	{
		$result = $this->_instance->autocomplete('cd test', 7, 'dev/test-env/terminal/autocomplete');
		$this->assertRegExp('#^test/\s+test\.txt\s*$#', $result->getData('result'));
	}
	
}
