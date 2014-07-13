<?php
/**
 * @copyright  Copyright (C) 2013 - 2014 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rdfa.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'microdata.php';

/**
 * PHP class for parsing the HTML markup and
 * convert the data-* HTML5 attributes in Microdata or RDFa Lite 1.1 semantics
 */
class ParserPlugin
{
	/**
	 * The type of semantic, will be an instance of PHPMicrodata or PHPRDFa
	 *
	 * @var null
	 */
	protected $handler = null;

	/**
	 * The suffix to search for when parsing the data-* HTML5 attribute
	 *
	 * @var string
	 */
	protected $suffix = 'sd';

	/**
	 * Initialize the class and setup the default $semantic, Microdata or RDFa
	 *
	 * @param   string  $semantic  The type of semantic to output, Microdata or RDFa
	 * @param   null    $suffix    The suffix to search for when parsing the data-* HTML5 attribute
	 */
	public function __construct($semantic, $suffix = null)
	{
		$this->semantic($semantic);

		if ($suffix)
		{
			$this->suffix($suffix);
		}
	}

	/**
	 * Return the $handler, which is an instance of PHPMicrodata or PHPRDFa
	 *
	 * @return PHPStructuredData
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Setup the semantic to output, accepted types are 'Microdata' or 'RDFa'
	 *
	 * @param   string  $type  The type of semantic to output, accepted types are 'Microdata' or 'RDFa'
	 *
	 * @throws ErrorException
	 * @return void
	 */
	public function semantic($type)
	{
		// Sanitize the $type
		$type = trim(strtolower($type));

		// Available only 2 possible types of semantic, 'Microdata' or 'RDFa', otherwise throw an Exception
		switch ($type)
		{
			case 'microdata':
				$this->handler = new PHPMicrodata;
				break;
			case 'rdfa':
				$this->handler = new PHPRDFa;
				break;
			default:
				throw new ErrorException('There is no ' . $type . ' library available');
				break;
		}
	}

	/**
	 * Return the current type of semantic
	 *
	 * @return string
	 */
	public function getSemantic()
	{
		if ($this->handler instanceof PHPMicrodata)
		{
			return 'microdata';
		}

		return 'rdfa';
	}

	/**
	 * Setup the $suffix to search for when parsing the data-* HTML5 attribute
	 *
	 * @param   string  $string  The suffix
	 *
	 * @throws LengthException
	 * @return void
	 */
	public function suffix($string)
	{
		$string = strtolower((string) $string);

		// The suffix must be at least one character long
		if (empty($string))
		{
			throw new LengthException('The suffix must be at least one character long');
		}

		$this->suffix = $string;
	}

	/**
	 * Return the current $suffix
	 *
	 * @return string
	 */
	public function getSuffix()
	{
		return $this->suffix;
	}

	/**
	 * Parse the params that will be used to setup the PHPStructuredData class,
	 * will parse the current string: 'Type.property FallbackType.fallbackProperty'
	 *
	 * @param   string  $string  The string to parse
	 *
	 * @return  array
	 */
	protected static function parseParams($string)
	{
		// Sanitize the $string
		$string = trim((string) $string);

		// Break the strings in two parts, the default params, and the fallback params
		$string = explode(' ', $string);

		// The default array
		$params = array(
			'type' => null,
			'property' => null,
			'fallbackType' => null,
			'fallbackProperty' => null
		);

		// Parse the default params
		$tmp = explode('.', $string[0]);

		// If it's not an empty string
		if (!empty($tmp[0]))
		{
			// If the first letter is uppercase, then it should be the Type, otherwise it should be the property
			if (ctype_upper($tmp[0]{0}))
			{
				$params['type'] = $tmp[0];
			}
			else
			{
				$params['property'] = $tmp[0];
			}

			// If there is a string after the 'dot', and it is lowercase, then it should be the property
			if (count($tmp) > 1 && !empty($tmp[1]) && !ctype_upper($tmp[1]{0}))
			{
				$params['property'] = $tmp[1];
			}
		}

		// If no fallback params are found, then return the array
		if (count($string) <= 1)
		{
			return $params;
		}

		// Parse the fallback params
		$tmp = explode('.', $string[1]);

		// If it's not an empty string
		if (!empty($tmp[0]))
		{
			// If the first letter is uppercase, then it should be the FallbackType, otherwise it should be the fallbackProperty
			if (ctype_upper($tmp[0]{0}))
			{
				$params['fallbackType'] = $tmp[0];
			}
			else
			{
				$params['fallbackProperty'] = $tmp[0];
			}

			// If there is a string after the 'dot', and it is lowercase, then it should be the fallbackProperty
			if (count($tmp) > 1 && !empty($tmp[1]) && !ctype_upper($tmp[1]{0}))
			{
				$params['fallbackProperty'] = $tmp[1];
			}
		}

		return $params;
	}

	/**
	 * Generate the Microdata or RDFa semantics
	 *
	 * @param   array  $params  The params used to setup the PHPStructuredData library
	 *
	 * @return string
	 */
	protected function display($params)
	{
		$html = "";

		// Setup the current $type
		if ($params['type'])
		{
			$this->handler->setType($params['type']);

			// Display the scope
			$html = $this->handler->displayScope();
		}

		// Setup the $property
		if ($params['property'])
		{
			// Display the scope
			$html = $this->handler->property($params['property'])->display('inline');
		}

		return $html;
	}

	/**
	 * Parse the HTML and replace the data-* HTML5 attributes with Microdata or RDFa semantics
	 *
	 * @param   string  $html  The HTML to parse
	 *
	 * @return  string
	 */
	public function parse($html)
	{
		// Disable frontend error reporting
		libxml_use_internal_errors(true);

		// Create a new DOMDocument
		$doc = new DOMDocument;
		$doc->loadHTML($html);

		// Create a new DOMXPath, to make XPath queries
		$xpath = new DOMXPath($doc);

		// Search for the data-* HTML5 attributes
		$nodeList = $xpath->query("//*[@data-" . $this->suffix . "]");

		foreach ($nodeList as $node)
		{
			// Retrieve the params used to setup the PHPStructuredData library
			$attribute  = $node->getAttribute('data-' . $this->suffix);
			$params     = $this->parseParams($attribute);

			// Generate the Microdata or RDFa semantic
			$semantic   = $this->display($params);

			// Replace the data-* HTML5 attributes with Microdata or RDFa semantics
			$pattern    = '/data-' . $this->suffix . "=." . $attribute . "./";
			$html       = preg_replace($pattern, $semantic, $html, 1);
		}

		return $html;
	}
}
