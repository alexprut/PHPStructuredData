<?php
/**
 * @copyright  Copyright (C) 2013 - 2014 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'parserPlugin.php';

/**
 * Test class for ParserPlugin
 */
class ParserPluginTest extends PHPUnit_Framework_TestCase
{
	protected $handler;

	/**
	 * Test setup
	 *
	 * @return  void
	 */
	public function setUp()
	{
		$this->handler = new ParserPlugin('Microdata');
	}

	/**
	 * Test the semantic() function
	 *
	 * @expectedException ErrorException
	 *
	 * @return  void
	 */
	public function testSemantic()
	{
		$this->handler = new ParserPlugin('doesNotExist');

		$this->handler = new ParserPlugin('Microdata');
		PHPUnit_Framework_Assert::assertAttributeInstanceOf('PHPMicrodata', 'handler', $this->handler);

		$this->handler = new ParserPlugin('RDFa');
		PHPUnit_Framework_Assert::assertAttributeInstanceOf('PHPRDFa', 'handler', $this->handler);
	}

	/**
	 * Test the getSemantic() function
	 *
	 * @return  void
	 */
	public function testGetSemantic()
	{
		$this->handler->semantic('Microdata');
		$this->assertEquals($this->handler->getSemantic(), 'microdata');

		$this->handler->semantic('RDFa');
		$this->assertEquals($this->handler->getSemantic(), 'rdfa');
	}

	/**
	 * Test the suffix() function
	 *
	 * @expectedException LengthException
	 *
	 * @return  void
	 */
	public function testSuffix()
	{
		/**
		 * The attribute name should not contain any uppercase letters,
		 * and must be at least one character long after the prefix "data-"
		 */
		$this->handler->suffix('');

		// Test string input, convert to lowercase if if necessary
		$this->handler->suffix('lowercaseSuffix');
		$this->assertContains('lowercasesuffix', $this->handler->getSuffix());

		// Test array input
		$this->handler->suffix(array('su', 'ff', 'ix'));
		$this->assertContains(array('su', 'ff', 'ix'), $this->handler->getSuffix());
	}

	/**
	 * Test the removeSuffix() function
	 *
	 * @return  void
	 */
	public function testRemoveSuffix()
	{
		$this->handler->suffix('anything');
		$this->handler->removeSuffix('anything');
		$this->assertNotContains('anything', $this->handler->getSuffix());
	}

	/**
	 * Test the getSuffix() function
	 *
	 * @return  void
	 */
	public function testGetSuffix()
	{
		$this->assertInternalType('array', $this->handler->getSuffix());
	}

	/**
	 * Test the parseParams() function
	 *
	 * @return  void
	 */
	public function testParseParams()
	{
		// Setup
		$method = self::getMethod('parseParams');

		// Test a complete params string
		$this->assertEquals(
			$method->invokeArgs(null, array('Type.property FallbackType.fallbackProperty')),
			array(
				'type' => 'Type',
				'property' => 'property',
				'fallbackType' => 'FallbackType',
				'fallbackProperty' => 'fallbackProperty'
			)
		);

		// Test with only the Type param
		$this->assertEquals(
			$method->invokeArgs(null, array(' Type')),
			array(
				'type' => 'Type',
				'property' => null,
				'fallbackType' => null,
				'fallbackProperty' => null
			)
		);

		// Test with only the property param
		$this->assertEquals(
			$method->invokeArgs(null, array('property')),
			array(
				'type' => null,
				'property' => 'property',
				'fallbackType' => null,
				'fallbackProperty' => null
			)
		);

		// Test with only the property and fallbacks params
		$this->assertEquals(
			$method->invokeArgs(null, array('property FallbackType.fallbackProperty')),
			array(
				'type' => null,
				'property' => 'property',
				'fallbackType' => 'FallbackType',
				'fallbackProperty' => 'fallbackProperty'
			)
		);

		// Test with only the Type and fallbacksProperty params
		$this->assertEquals(
			$method->invokeArgs(null, array('Type fallbackProperty')),
			array(
				'type' => 'Type',
				'property' => null,
				'fallbackType' => null,
				'fallbackProperty' => 'fallbackProperty'
			)
		);

		// Test a strange behaviour
		$this->assertEquals(
			$method->invokeArgs(null, array('.Type.property FallbackType. fallbackProperty')),
			array(
				'type' => null,
				'property' => null,
				'fallbackType' => 'FallbackType',
				'fallbackProperty' => null
			)
		);

		// Test with an empty string
		$this->assertEquals(
			$method->invokeArgs(null, array(' ')),
			array(
				'type' => null,
				'property' => null,
				'fallbackType' => null,
				'fallbackProperty' => null
			)
		);
	}

	/**
	 * Test the parse() function
	 *
	 * @return  void
	 */
	public function testParse()
	{
		// Setup
		$content = 'content';

		// Test tag parse: data-*="Article.author"
		$html = "<tag data-sd='Article.author'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author'>$content</tag>"
		);

		// Test tag parse: data-*="Article"
		$html = "<tag data-sd='Article'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemscope itemtype='https://schema.org/Article'>$content</tag>"
		);

		// Test tag parse: data-*="author"
		$html = "<tag data-sd='author'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author'>$content</tag>"
		);

		// Test self-closing tag parse: data-*="datePublished"
		$html = "<meta data-sd='datePublished' content='2014-01-01T00:00:00+00:00' />";
		$this->assertEquals(
			$this->handler->parse($html),
			"<meta itemprop='datePublished' content='2014-01-01T00:00:00+00:00' />"
		);

		// Test tag parse: data-*="Article.propertyDoesNotExist"
		$html = "<tag data-sd='Article.propertyDoesNotExist'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag >$content</tag>"
		);

		// Test tag parse: data-*="TypeDoesNotExist.propertyDoesNotExist"
		$html = "<tag data-sd='TypeDoesNotExist.propertyDoesNotExist'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag >$content</tag>"
		);

		// Test multiple suffix tag parse
		$this->handler->suffix('custom');
		$html = "<tag data-custom='Article.author'>$content</tag><tag data-sd='url'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author'>$content</tag><tag itemprop='url'>$content</tag>"
		);

		// Test multiple suffix tag parse, check that replaces only the first match
		$html = "<tag data-sd='Article.author' data-custom='Article.name'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author' data-custom='Article.name'>$content</tag>"
		);

		// Test that it doesn't parse an unregistered suffix
		$html = "<tag data-unregistered='Article.author'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag data-unregistered='Article.author'>$content</tag>"
		);
	}

	/**
	 * A function helper that allows to test protected functions
	 *
	 * @param   string  $name  The name of the method
	 *
	 * @return	object
	 */
	protected static function getMethod($name)
	{
		$class = new ReflectionClass('ParserPlugin');
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		return $method;
	}
}