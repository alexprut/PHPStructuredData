<?php
/**
 * @copyright  Copyright (C) 2013 - 2014 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'microdata.php';

/**
 * Test class for PHPMicrodata
 */
class PHPMicrodataTest extends PHPUnit_Framework_TestCase
{
	/**
	 * The default fallback Type
	 *
	 * @var  string
	 */
	protected $defaultType = 'Thing';

	/**
	 * Tested class handler
	 *
	 * @var  object
	 */
	protected $handler;

	/**
	 * Test setup
	 *
	 * @return  void
	 */
	public function setUp()
	{
		$this->handler = new PHPMicrodata;
	}

	/**
	 * Test the default settings
	 *
	 * @return  void
	 */
	public function testDefaults()
	{
		$this->handler = new PHPMicrodata;

		// Test that the default Type is Thing
		$this->assertEquals($this->handler->getType(), $this->defaultType);

		$this->assertClassHasAttribute('types', 'PHPMicrodata');
	}

	/**
	 * Test the setType() function
	 *
	 * @return  void
	 */
	public function testSetType()
	{
		$this->handler->setType('Article');

		// Test if the current Type is Article
		$this->assertEquals($this->handler->getType(), 'Article');

		// Test if the Type fallbacks to Thing Type
		$this->handler->setType('TypeThatDoesNotExist');
		$this->assertEquals($this->handler->getType(), $this->defaultType);
	}

	/**
	 * Test the fallback() function
	 *
	 * @return  void
	 */
	public function testFallback()
	{
		// Test fallback values
		$this->handler->fallback('Article', 'articleBody');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertEquals($this->handler->getFallbackProperty(), 'articleBody');

		// Test if Fallback Property fallbacks isn't available in the Type
		$this->handler->fallback('Article', 'anUnanvailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertNull($this->handler->getFallbackProperty());

		// Test if Fallback Type fallbacks to Thing Type
		$this->handler->fallback('anUnanvailableType', 'anUnanvailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Thing');
		$this->assertNull($this->handler->getFallbackProperty());
	}

	/**
	 * Test the display() function
	 *
	 * @return  void
	 */
	public function testDisplay()
	{
		// Setup
		$content = 'anything';

		// Test display() with all null params
		$this->handler = new PHPMicrodata;

		$this->assertEquals($this->handler->display(), '');

		// Test if the params are reseted after display()
		$this->handler->setType('Article')
			->content($content)
			->property('name')
			->fallback('Thing', 'url')
			->display();

		$this->assertNull($this->handler->getFallbackProperty());
		$this->assertNull($this->handler->getFallbackType());
		$this->assertNull($this->handler->getProperty());
		$this->assertNull($this->handler->getContent());

		// Test for a simple display
		$responce = $this->handler
			->property('url')
			->display();

		$this->assertEquals($responce, "itemprop='url'");

		// Test for a simple display with $content
		$responce = $this->handler
			->property('url')
			->content($content)
			->display();

		$this->assertEquals($responce, "<span itemprop='url'>$content</span>");

		// Test for a simple display if the $content is empty ''
		$responce = $this->handler->enable(true)
			->content('')
			->property('name')
			->display();

		$this->assertEquals($responce, "<span itemprop='name'></span>");

		// Test for a simple nested display
		$responce = $this->handler
			->property('author')
			->display();

		$this->assertEquals(
			$responce,
			"itemprop='author' itemscope itemtype='https://schema.org/Organization'"
		);

		// Test for a nested display with $content
		$responce = $this->handler
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$responce,
			"<span itemprop='author' itemscope itemtype='https://schema.org/Organization'>$content</span>"
		);

		// Test for a nested display with content and Fallback
		$responce = $this->handler
			->fallback('Person', 'name')
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$responce,
			"<span itemprop='author' itemscope itemtype='https://schema.org/Person'><span itemprop='name'>$content</span></span>"
		);

		// Test for a nested display with $Fallback and without $content
		$responce = $this->handler
			->fallback('Person', 'name')
			->property('author')
			->display();

		$this->assertEquals(
			$responce,
			"itemprop='author' itemscope itemtype='https://schema.org/Person' itemprop='name'"
		);

		// Test for a meta display without $content
		$responce = $this->handler
			->property('datePublished')
			->display();

		$this->assertEquals(
			$responce,
			"itemprop='datePublished'"
		);

		// Test for a meta display with content
		$content = '01 January 2011';
		$responce = $this->handler
			->property('datePublished')
			->content($content)
			->display();

		$this->assertEquals(
			$responce,
			"<meta itemprop='datePublished' content='$content'/>$content"
		);

		// Test if the PHPMicrodata is disabled
		$responce = $this->handler->enable(false)
			->content($content)
			->fallback('Article', 'about')
			->property('datePublished')
			->display();

		$this->assertEquals($responce, $content);

		// Test if PHPMicrodata is disabled and have a $content it must return an empty string
		$responce = $this->handler->enable(false)
			->content('en-GB')
			->property('inLanguage')
			->fallback('Language', 'name')
			->display('meta', true);

		$this->assertEquals($responce, '');

		// Test if the params are reseted after display(), if the library is disabled
		$this->assertNull($this->handler->getFallbackProperty());
		$this->assertNull($this->handler->getFallbackType());
		$this->assertNull($this->handler->getProperty());
		$this->assertNull($this->handler->getContent());
	}

	/**
	 * Test the display() function when fallbacks
	 *
	 * @return  void
	 */
	public function testDisplayFallbacks()
	{
		// Setup
		$this->handler->enable(true)->setType('Article');
		$content = 'anything';

		// Test without $content if fallbacks, the Property isn't available in the current Type
		$responce = $this->handler
			->property('anUnanvailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$responce,
			"itemscope itemtype='https://schema.org/Article' itemprop='about'"
		);

		// Test with $content if fallbacks, the Property isn't available in the current Type
		$responce = $this->handler
			->content($content)
			->property('anUnanvailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$responce,
			"<span itemscope itemtype='https://schema.org/Article'><span itemprop='about'>$content</span></span>"
		);

		// Test if fallbacks, the Property isn't available in the current and fallback Type
		$responce = $this->handler
			->property('anUnanvailableProperty')
			->fallback('Article', 'anUnanvailableProperty')
			->display();

		$this->assertEquals(
			$responce,
			"itemscope itemtype='https://schema.org/Article'"
		);

		// Test with $content if fallbacks, the Property isn't available in the current Type
		$responce = $this->handler
			->content($content)
			->property('anUnanvailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$responce,
			"<meta itemscope itemtype='https://schema.org/Article' itemprop='datePublished' content='$content'/>"
		);

		// Test withtout $content if fallbacks, the Property isn't available in the current Type
		$responce = $this->handler
			->property('anUnanvailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$responce,
			"itemscope itemtype='https://schema.org/Article' itemprop='datePublished'"
		);
	}

	/**
	 * Test the display() function, all display types
	 *
	 * @return  void
	 */
	public function testDisplayTypes()
	{
		// Setup
		$type = 'Article';
		$content = 'microdata';
		$property = 'datePublished';

		$microdata = $this->handler;
		$microdata->enable(true)->setType($type);

		// Test Display Type: 'inline'
		$responce = $microdata->content($content)
			->property($property)
			->display('inline');

		$this->assertEquals(
			$responce,
			"itemprop='$property'"
		);

		// Test Display Type: 'div'
		$responce = $microdata->content($content)
			->property($property)
			->display('div');

		$this->assertEquals(
			$responce,
			"<div itemprop='$property'>$content</div>"
		);

		// Test Display Type: 'div' without $content
		$responce = $microdata->property($property)
			->display('div');

		$this->assertEquals(
			$responce,
			"<div itemprop='$property'></div>"
		);

		// Test Display Type: 'span'
		$responce = $microdata->content($content)
			->property($property)
			->display('span');

		$this->assertEquals(
			$responce,
			"<span itemprop='$property'>$content</span>"
		);

		// Test Display Type: 'span' without $content
		$responce = $microdata
			->property($property)
			->display('span');

		$this->assertEquals(
			$responce,
			"<span itemprop='$property'></span>"
		);

		// Test Display Type: 'meta'
		$responce = $microdata->content($content)
			->property($property)
			->display('meta');

		$this->assertEquals(
			$responce,
			"<meta itemprop='$property' content='$content'/>"
		);

		// Test Display Type: 'meta' without $content
		$responce = $microdata
		->property($property)
		->display('meta');

		$this->assertEquals(
			$responce,
			"<meta itemprop='$property' content=''/>"
		);
	}

	/**
	 * Test the isTypeAvailabe() function
	 *
	 * @return  void
	 */
	public function testIsTypeAvailable()
	{
		// Test if the method return true with an available Type
		$this->assertTrue(
			PHPMicrodata::isTypeAvailable('Article')
		);

		// Test if the method return false with an unavailable Type
		$this->assertFalse(
			PHPMicrodata::isTypeAvailable('SomethingThatDoesNotExist')
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

		// Test a Property that is available in the Type
		$this->assertTrue(
			PHPMicrodata::isPropertyInType($type, 'articleBody')
		);

		// Test an inherit Property that is available in the Type
		$this->assertTrue(
			PHPMicrodata::isPropertyInType($type, 'about')
		);

		// Test a Property that is unavailable in the Type
		$this->assertFalse(
			PHPMicrodata::isPropertyInType($type, 'aPropertyThatDoesNotExist')
		);

		// Test a Property in an unanvailable Type
		$this->assertFalse(
			PHPMicrodata::isPropertyInType('aTypeThatDoesNotExist', 'aPropertyThatDoesNotExist')
		);
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
		$obj = new PHPMicrodata;

		// Test if Display Type is 'normal'
		$this->assertEquals(
			$method->invokeArgs($obj, array($type, 'articleBody')),
			'normal'
		);

		// Test if Display Type is 'nested'
		$this->assertEquals(
			$method->invokeArgs($obj, array($type, 'about')),
			'nested'
		);

		// Test if Display Type is 'meta'
		$this->assertEquals(
			$method->invokeArgs($obj, array($type, 'datePublished')),
			'meta'
		);
	}

	/**
	 * Test the displayScope() function
	 *
	 * @return  void
	 */
	public function testDisplayScope()
	{
		// Setup
		$type = 'Article';
		$this->handler->enable(true)
			->setType($type);

		// Test a displayScope() when microdata are enabled
		$this->assertEquals(
			$this->handler->displayScope(),
			"itemscope itemtype='https://schema.org/$type'"
		);

		// Test a displayScope() when microdata are disabled
		$this->assertEquals(
			$this->handler->enable(false)->displayScope(),
			""
		);
	}

	/**
	 * Test the getAvailableTypes() function
	 *
	 * @return  void
	 */
	public function testGetAvailableTypes()
	{
		$responce = PHPMicrodata::getAvailableTypes();

		$this->assertGreaterThan(500, count($responce));
		$this->assertNotEmpty($responce);
		$this->assertTrue(in_array('Thing', $responce));
	}

	/**
	 * Test the static htmlMeta() function
	 *
	 * @return  void
	 */
	public function testHtmlMeta()
	{
		$scope = 'Article';
		$content = 'microdata';
		$property = 'datePublished';

		// Test with all params
		$this->assertEquals(
			PHPMicrodata::htmlMeta($content, $property, $scope),
			"<meta itemscope itemtype='https://schema.org/$scope' itemprop='$property' content='$content'/>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			PHPMicrodata::htmlMeta($content, $property, $scope, true),
			"<meta itemprop='$property' itemscope itemtype='https://schema.org/$scope' content='$content'/>"
		);

		// Test without the $scope
		$this->assertEquals(
			PHPMicrodata::htmlMeta($content, $property),
			"<meta itemprop='$property' content='$content'/>"
		);
	}

	/**
	 * Test the htmlDiv() function
	 *
	 * @return  void
	 */
	public function testHtmlDiv()
	{
		// Setup
		$scope = 'Article';
		$content = 'microdata';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			PHPMicrodata::htmlDiv($content, $property, $scope),
			"<div itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</div>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			PHPMicrodata::htmlDiv($content, $property, $scope, true),
			"<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>"
		);

		// Test without the $scope
		$this->assertEquals(
			PHPMicrodata::htmlDiv($content, $property),
			"<div itemprop='$property'>$content</div>"
		);

		// Test without the $property
		$this->assertEquals(
			PHPMicrodata::htmlDiv($content, $property, $scope, true),
			"<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>"
		);

		// Test withoud the $scope, $property
		$this->assertEquals(
			PHPMicrodata::htmlDiv($content),
			"<div>$content</div>"
		);
	}

	/**
	 * Test the htmlSpan() function
	 *
	 * @return  void
	 */
	public function testHtmlSpan()
	{
		// Setup
		$scope = 'Article';
		$content = 'microdata';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			PHPMicrodata::htmlSpan($content, $property, $scope),
			"<span itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</span>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			PHPMicrodata::htmlSpan($content, $property, $scope, true),
			"<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>"
		);

		// Test without the $scope
		$this->assertEquals(
			PHPMicrodata::htmlSpan($content, $property),
			"<span itemprop='$property'>$content</span>"
		);

		// Test without the $property
		$this->assertEquals(
			PHPMicrodata::htmlSpan($content, $property, $scope, true),
			"<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>"
		);

		// Test withoud the $scope, $property
		$this->assertEquals(
			PHPMicrodata::htmlSpan($content),
			"<span>$content</span>"
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
		$class = new ReflectionClass('PHPMicrodata');
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		return $method;
	}
}
