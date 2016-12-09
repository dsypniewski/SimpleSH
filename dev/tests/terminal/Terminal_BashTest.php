<?php

class Terminal_BashTest extends PHPUnit_Framework_TestCase
{
	
	/** @var Terminal_Bash */
	protected $_instance;
	
	public static function setUpBeforeClass()
	{
		require_once '../../../src/core/Result.php';
		require_once '../../../src/core/PlatformTools.php';
		require_once '../../../src/terminal/Terminal_ShellInterface.php';
		require_once '../../../src/terminal/Terminal_Shell.php';
		require_once '../../../src/terminal/Terminal_DynamicOutputShell.php';
		require_once '../../../src/terminal/Terminal_Sh.php';
		require_once '../../../src/terminal/Terminal_AutocompleteInterface.php';
		require_once '../../../src/terminal/Terminal_Bash.php';
	}

	protected function setUp()
	{
		$this->_instance = new Terminal_Bash();
		$this->_instance->setInterpreterPath('/bin/bash');
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
		$result = $this->_instance->autocomplete('cat ', 4, '../../test-env/terminal/autocomplete/test');
		$this->assertEquals('cat test.txt ', $result->getData('command'));
	}

	public function testAutocompleteSingleFileInsideDirectory()
	{
		$result = $this->_instance->autocomplete('cd test/', 8, '../../test-env/terminal/autocomplete');
		$this->assertEquals('cd test/test.txt ', $result->getData('command'));
	}
	
	public function testAutocompletePartialComplete()
	{
		$result = $this->_instance->autocomplete('cd t', 4, '../../test-env/terminal/autocomplete');
		$this->assertEquals('cd test', $result->getData('command'));
	}
	
	public function testAutocompleteSingleMatchFile()
	{
		$result = $this->_instance->autocomplete('cat test.', 9, '../../test-env/terminal/autocomplete');
		$this->assertEquals('cat test.txt ', $result->getData('command'));
	}
	
	public function testAutocompleteDirectoryContentsList()
	{
		$result = $this->_instance->autocomplete('cd test', 7, '../../test-env/terminal/autocomplete');
		$this->assertRegExp('#^test/\s+test\.txt\s*$#', $result->getData('result'));
	}
	
}
