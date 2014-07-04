<?php
/**
 * @copyright  Copyright (C) 2013 - 2014 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'structuredData.php';

/**
 * Test class for PHPStructuredData
 */
class PHPStructuredDataTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test the isTypeAvailable() function
	 *
	 * @return  void
	 */
	public function testIsTypeAvailable()
	{
		// Test if the function returns 'true' with an available $Type
		$this->assertTrue(
			PHPStructuredData::isTypeAvailable('Article')
		);

		// Test if the function returns 'false' with an unavailable $Type
		$this->assertFalse(
			PHPStructuredData::isTypeAvailable('SomethingThatDoesNotExist')
		);
	}

	/**
	 * Test the isPropertyInType() function
	 *
	 * @return  void
	 */
	public function testIsPropertyInType()
	{
		// Setup
		$type = 'Article';

		// Test a $Property that is available in the $Type
		$this->assertTrue(
			PHPStructuredData::isPropertyInType($type, 'articleBody')
		);

		// Test an inherit $Property that is available in the $Type
		$this->assertTrue(
			PHPStructuredData::isPropertyInType($type, 'about')
		);

		// Test a $Property that is unavailable in the $Type
		$this->assertFalse(
			PHPStructuredData::isPropertyInType($type, 'aPropertyThatDoesNotExist')
		);

		// Test a Property in an unanvailable Type
		$this->assertFalse(
			PHPStructuredData::isPropertyInType('aTypeThatDoesNotExist', 'aPropertyThatDoesNotExist')
		);
	}

	/**
	 * Test the getAvailableTypes() function
	 *
	 * @return  void
	 */
	public function testGetAvailableTypes()
	{
		$response = PHPStructuredData::getAvailableTypes();

		$this->assertGreaterThan(500, count($response));
		$this->assertNotEmpty($response);
		$this->assertTrue(in_array('Thing', $response));
	}

	/**
	 * Test the expectedDisplayType() function
	 *
	 * @return  void
	 */
	public function testExpectedDisplayType()
	{
		// Setup
		$type = 'Article';
		$method = self::getMethod('getExpectedDisplayType');

		// Test if Display Type is 'normal'
		$this->assertEquals(
			$method->invokeArgs(null, array($type, 'articleBody')),
			'normal'
		);

		// Test if Display Type is 'nested'
		$this->assertEquals(
			$method->invokeArgs(null, array($type, 'about')),
			'nested'
		);

		// Test if Display Type is 'meta'
		$this->assertEquals(
			$method->invokeArgs(null, array($type, 'datePublished')),
			'meta'
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
		$class = new ReflectionClass('PHPStructuredData');
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		return $method;
	}
}
