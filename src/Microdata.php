<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

namespace PHPStructuredData;
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'StructuredData.php';

/**
 * PHP class for interacting with Microdata semantics.
 *
 * @since 1.0
 */
class Microdata extends StructuredData
{
	/**
	 * Return Microdata semantics in a <meta> tag with content for machines.
	 *
	 * @param   string   $content   The machine content to display
	 * @param   string   $property  The Property
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $invert    Optional, default = false, invert the $scope with the $property
	 *
	 * @return  string
	 */
	public static function htmlMeta($content, $property, $scope = '', $invert = false)
	{
		return static::htmlTag('meta', $content, $property, $scope, $invert);
	}

	/**
	 * Return Microdata semantics in a <span> tag.
	 *
	 * @param   string   $content   The human content
	 * @param   string   $property  Optional, the human content to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $invert    Optional, default = false, invert the $scope with the $property
	 *
	 * @return  string
	 */
	public static function htmlSpan($content, $property = '', $scope = '', $invert = false)
	{
		return static::htmlTag('span', $content, $property, $scope, $invert);
	}

	/**
	 * Return Microdata semantics in a <div> tag.
	 *
	 * @param   string   $content   The human content
	 * @param   string   $property  Optional, the human content to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $invert    Optional, default = false, invert the $scope with the $property
	 *
	 * @return  string
	 */
	public static function htmlDiv($content, $property = '', $scope = '', $invert = false)
	{
		return static::htmlTag('div', $content, $property, $scope, $invert);
	}

	/**
	 * Return Microdata semantics in a specified tag.
	 *
	 * @param   string   $tag       The HTML tag
	 * @param   string   $content   The human content
	 * @param   string   $property  Optional, the human content to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $invert    Optional, default = false, invert the $scope with the $property
	 *
	 * @return  string
	 */
	public static function htmlTag($tag, $content, $property = '', $scope = '', $invert = false)
	{
		// Control if the $Property has already the 'itemprop' prefix
		if (!empty($property) && stripos($property, 'itemprop') !== 0)
		{
			$property = static::htmlProperty($property);
		}

		// Control if the $Scope have already the 'itemscope' prefix
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = static::htmlScope($scope);
		}

		// Depending on the case, the $scope must precede the $property, or otherwise
		if ($invert)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);
		$tmp = ($tmp) ? ' ' . $tmp : '';

		// Control if it is an empty element without a closing tag
		if ($tag === 'meta')
		{
			return "<meta$tmp content='$content'/>";
		}

		return "<" . $tag . $tmp . ">" . $content . "</" . $tag . ">";
	}

	/**
	 * Return the HTML Scope
	 *
	 * @param   string  $scope  The Scope to process
	 *
	 * @return  string
	 */
	public static function htmlScope($scope)
	{
		return "itemscope itemtype='https://schema.org/" . static::sanitizeType($scope) . "'";
	}

	/**
	 * Return the HTML Property
	 *
	 * @param   string  $property  The Property to process
	 *
	 * @return  string
	 */
	public static function htmlProperty($property)
	{
		return "itemprop='$property'";
	}
}
