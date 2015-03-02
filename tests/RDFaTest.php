<?php
/**
 * @copyright  Copyright (C) 2013 - 2014 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

namespace PHPStructuredDataTest;
use PHPStructuredData\RDFa as RDFa;

/**
 * Test class for RDFa
 *
 * @since  1.1
 */
class RDFaTest extends \PHPUnit_Framework_TestCase
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
		$this->handler = new RDFa;
	}

	/**
	 * Test the default settings
	 *
	 * @return  void
	 */
	public function testDefaults()
	{
		$this->handler = new RDFa;

		// Test that the default Type is 'Thing'
		$this->assertEquals($this->handler->getType(), $this->defaultType);

		$this->assertClassHasAttribute('types', 'PHPStructuredData\RDFa');
	}

	/**
	 * Test the setType() function
	 *
	 * @return  void
	 */
	public function testSetType()
	{
		$this->handler->setType('Article');

		// Test if the current Type is 'Article'
		$this->assertEquals($this->handler->getType(), 'Article');

		// Test if the Type fallbacks to 'Thing' Type
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

		// Test if the Fallback Property fallbacks when it isn't available in the $Type
		$this->handler->fallback('Article', 'anUnavailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertNull($this->handler->getFallbackProperty());

		// Test if the Fallback Type fallbacks to the 'Thing' Type
		$this->handler->fallback('anUnavailableType', 'anUnavailableProperty');
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
		$this->handler = new RDFa;

		$this->assertEquals($this->handler->display(), '');

		// Test if the params are reseted after the display() function
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
		$response = $this->handler
			->property('url')
			->display();

		$this->assertEquals($response, "property='url'");

		// Test for a simple display with $content
		$response = $this->handler
			->property('url')
			->content($content)
			->display();

		$this->assertEquals($response, "<span property='url'>$content</span>");

		// Test for a simple display if the $content is empty ''
		$response = $this->handler->enable(true)
			->content('')
			->property('name')
			->display();

		$this->assertEquals($response, "<span property='name'></span>");

		// Test for a simple 'nested' display
		$response = $this->handler
			->property('author')
			->display();

		$this->assertEquals(
			$response,
			"property='author' vocab='https://schema.org' typeof='Organization'"
		);

		// Test for a 'nested' display with $content
		$response = $this->handler
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$response,
			"<span property='author' vocab='https://schema.org' typeof='Organization'>$content</span>"
		);

		// Test for a 'nested' display with $content and $Fallback
		$response = $this->handler
			->fallback('Person', 'name')
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$response,
			"<span property='author' vocab='https://schema.org' typeof='Person'><span property='name'>$content</span></span>"
		);

		// Test for a 'nested' display with $Fallback and without $content
		$response = $this->handler
			->fallback('Person', 'name')
			->property('author')
			->display();

		$this->assertEquals(
			$response,
			"property='author' vocab='https://schema.org' typeof='Person' property='name'"
		);

		// Test for a 'meta' display without $content
		$response = $this->handler
			->property('datePublished')
			->display();

		$this->assertEquals(
			$response,
			"property='datePublished'"
		);

		// Test for a 'meta' display with $content
		$content = '01 January 2011';
		$response = $this->handler
			->property('datePublished')
			->content($content)
			->display();

		$this->assertEquals(
			$response,
			"<meta property='datePublished' content='$content'/>$content"
		);

		// Test for a 'meta' display with human $content and $machineContent
		$machineContent = "2011-01-01T00:00:00+00:00";
		$response = $this->handler
			->property('datePublished')
			->content($content, $machineContent)
			->display();

		$this->assertEquals(
			$response,
			"<meta property='datePublished' content='$machineContent'/>$content"
		);

		// Test when if fallbacks that the library returns an empty string as specified
		$response = $this->handler
			->content('en-GB')
			->property('doesNotExist')
			->display('meta', true);

		$this->assertEquals($response, '');

		// Test if the library is disabled
		$response = $this->handler->enable(false)
			->content($content)
			->fallback('Article', 'about')
			->property('datePublished')
			->display();

		$this->assertEquals($response, $content);

		// Test if the library is disabled and if it have a $content it must return an empty string
		$response = $this->handler->enable(false)
			->content('en-GB')
			->property('inLanguage')
			->fallback('Language', 'name')
			->display('meta', true);

		$this->assertEquals($response, '');

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

		// Test without $content if fallbacks, the $Property isn't available in the current Type
		$response = $this->handler
			->property('anUnavailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$response,
			"vocab='https://schema.org' typeof='Article' property='about'"
		);

		// Test with $content if fallbacks, the $Property isn't available in the current Type
		$response = $this->handler
			->content($content)
			->property('anUnavailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$response,
			"<span vocab='https://schema.org' typeof='Article'><span property='about'>$content</span></span>"
		);

		// Test if fallbacks, the $Property isn't available in the current and the fallback Type
		$response = $this->handler
			->property('anUnavailableProperty')
			->fallback('Article', 'anUnavailableProperty')
			->display();

		$this->assertEquals(
			$response,
			"vocab='https://schema.org' typeof='Article'"
		);

		// Test with $content if fallbacks, the $Property isn't available in the current $Type
		$response = $this->handler
			->content($content)
			->property('anUnavailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$response,
			"<meta vocab='https://schema.org' typeof='Article' property='datePublished' content='$content'/>"
		);

		// Test without $content if fallbacks, the Property isn't available in the current Type
		$response = $this->handler
			->property('anUnavailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$response,
			"vocab='https://schema.org' typeof='Article' property='datePublished'"
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
		$type      = 'Article';
		$content   = 'anything';
		$property  = 'datePublished';
		$rdfa      = $this->handler;
		$rdfa->enable(true)->setType($type);

		// Test Display Type: 'inline'
		$response = $rdfa->content($content)
			->property($property)
			->display('inline');

		$this->assertEquals(
			$response,
			"property='$property'"
		);

		// Test Display Type: 'div'
		$response = $rdfa->content($content)
			->property($property)
			->display('div');

		$this->assertEquals(
			$response,
			"<div property='$property'>$content</div>"
		);

		// Test Display Type: 'div' without $content
		$response = $rdfa->property($property)
			->display('div');

		$this->assertEquals(
			$response,
			"<div property='$property'></div>"
		);

		// Test Display Type: 'span'
		$response = $rdfa->content($content)
			->property($property)
			->display('span');

		$this->assertEquals(
			$response,
			"<span property='$property'>$content</span>"
		);

		// Test Display Type: 'span' without $content
		$response = $rdfa
			->property($property)
			->display('span');

		$this->assertEquals(
			$response,
			"<span property='$property'></span>"
		);

		// Test Display Type: 'meta'
		$response = $rdfa->content($content)
			->property($property)
			->display('meta');

		$this->assertEquals(
			$response,
			"<meta property='$property' content='$content'/>"
		);

		// Test Display Type: 'meta' without $content
		$response = $rdfa
			->property($property)
			->display('meta');

		$this->assertEquals(
			$response,
			"<meta property='$property' content=''/>"
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

		// Test the displayScope() function when the library is enabled
		$this->assertEquals(
			$this->handler->displayScope(),
			"vocab='https://schema.org' typeof='$type'"
		);

		// Test the displayScope() function when the library is disabled
		$this->assertEquals(
			$this->handler->enable(false)->displayScope(),
			""
		);
	}

	/**
	 * Test the static htmlMeta() function
	 *
	 * @return  void
	 */
	public function testHtmlMeta()
	{
		$scope    = 'Article';
		$content  = 'anything';
		$property = 'datePublished';

		// Test with all params
		$this->assertEquals(
			RDFa::htmlMeta($content, $property, $scope),
			"<meta vocab='https://schema.org' typeof='$scope' property='$property' content='$content'/>"
		);

		// Test with the $inverse mode
		$this->assertEquals(
			RDFa::htmlMeta($content, $property, $scope, true),
			"<meta property='$property' vocab='https://schema.org' typeof='$scope' content='$content'/>"
		);

		// Test without the $scope
		$this->assertEquals(
			RDFa::htmlMeta($content, $property),
			"<meta property='$property' content='$content'/>"
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
		$scope    = 'Article';
		$content  = 'anything';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			RDFa::htmlDiv($content, $property, $scope),
			"<div vocab='https://schema.org' typeof='$scope' property='$property'>$content</div>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			RDFa::htmlDiv($content, $property, $scope, true),
			"<div property='$property' vocab='https://schema.org' typeof='$scope'>$content</div>"
		);

		// Test without the $scope
		$this->assertEquals(
			RDFa::htmlDiv($content, $property),
			"<div property='$property'>$content</div>"
		);

		// Test without the $property
		$this->assertEquals(
			RDFa::htmlDiv($content, $property, $scope, true),
			"<div property='$property' vocab='https://schema.org' typeof='$scope'>$content</div>"
		);

		// Test without the $scope, $property
		$this->assertEquals(
			RDFa::htmlDiv($content),
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
		$scope    = 'Article';
		$content  = 'anything';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			RDFa::htmlSpan($content, $property, $scope),
			"<span vocab='https://schema.org' typeof='$scope' property='$property'>$content</span>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			RDFa::htmlSpan($content, $property, $scope, true),
			"<span property='$property' vocab='https://schema.org' typeof='$scope'>$content</span>"
		);

		// Test without the $scope
		$this->assertEquals(
			RDFa::htmlSpan($content, $property),
			"<span property='$property'>$content</span>"
		);

		// Test without the $property
		$this->assertEquals(
			RDFa::htmlSpan($content, $property, $scope, true),
			"<span property='$property' vocab='https://schema.org' typeof='$scope'>$content</span>"
		);

		// Test without the $scope, $property
		$this->assertEquals(
			RDFa::htmlSpan($content),
			"<span>$content</span>"
		);
	}
}
