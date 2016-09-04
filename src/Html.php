<?php
/*
 * This file is part of the Youthweb\BBCodeParser package.
 *
 * (c) Youthweb e.V. <info@youthweb.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Youthweb\BBCodeParser;

/**
 * HTML Generation class
 *
 * Inspiried by Fuel\Core\Html
 */

class Html
{

	/**
	 * Generates a html un-ordered list tag
	 *
	 * @param	array			list items, may be nested
	 * @param	array|string	outer list attributes
	 * @return	string
	 */
	public static function ul(array $list = array(), $attr = false)
	{
		return static::build_list('ul', $list, $attr);
	}

	/**
	 * Generates a html ordered list tag
	 *
	 * @param	array			list items, may be nested
	 * @param	array|string	outer list attributes
	 * @return	string
	 */
	public static function ol(array $list = array(), $attr = false)
	{
		return static::build_list('ol', $list, $attr);
	}

	/**
	 * Creates a mailto link.
	 *
	 * @param	string	The email address
	 * @param	string	The text value
	 * @param	string	The subject
	 * @return	string	The mailto link
	 */
	public static function mail_to($email, $text = null, $subject = null, $attr = array())
	{
		$text or $text = $email;

		$subject and $subject = '?subject='.$subject;

		return static::html_tag('a', array(
			'href' => 'mailto:'.$email.$subject,
		) + $attr, $text);
	}

	/**
	 * Creates a mailto link with Javascript to prevent bots from picking up the
	 * email address.
	 *
	 * @param	string	the email address
	 * @param	string	the text value
	 * @param	string	the subject
	 * @param	array	attributes for the tag
	 * @return	string	the javascript code containg email
	 */
	public static function mail_to_safe($email, $text = null, $subject = null, $attr = array())
	{
		$text or $text = str_replace('@', '[at]', $email);

		$email = explode("@", $email);

		$subject and $subject = '?subject='.$subject;

		$attr = static::array_to_attr($attr);
		$attr = ($attr == '' ? '' : ' ').$attr;

		$output = '<script type="text/javascript">';
		$output .= '(function() {';
		$output .= 'var user = "'.$email[0].'";';
		$output .= 'var at = "@";';
		$output .= 'var server = "'.$email[1].'";';
		$output .= "document.write('<a href=\"' + 'mail' + 'to:' + user + at + server + '$subject\"$attr>$text</a>');";
		$output .= '})();';
		$output .= '</script>';
		return $output;
	}

	/**
	 * Creates a html link
	 *
	 * @param	string	the url
	 * @param	string	the text value
	 * @param	array	the attributes array
	 * @param	bool	true to force https, false to force http
	 * @return	string	the html link
	 */
	public static function anchor($href, $text = null, $attr = array(), $secure = null)
	{
		if ( ! preg_match('#^(javascript:|\#)# i', $href) )
		{
			$href = static::http_build_url($href);

			// Trim the trailing slash
			$href = rtrim($href, '/');
		}

		// Create and display a URL hyperlink
		is_null($text) and $text = $href;

		$attr['href'] = htmlspecialchars($href);

		return static::html_tag('a', $attr, $text);
	}

	/**
	 * Creates a html image tag
	 *
	 * Sets the alt atribute to filename of it is not supplied.
	 *
	 * @param	string	the source
	 * @param	array	the attributes array
	 * @return	string	the image tag
	 */
	public static function img($src, $attr = array())
	{
		if ( ! preg_match('#^(\w+://)# i', $src) )
		{
			$src = static::http_build_url($src);
		}

		$attr['src'] = $src;
		$attr['alt'] = (isset($attr['alt'])) ? $attr['alt'] : pathinfo($src, PATHINFO_FILENAME);
		return static::html_tag('img', $attr);
	}

	/**
	 * Creates a html span tag
	 *
	 * @param	string	the content
	 * @param	array	the attributes array
	 * @return	string	the span tag
	 */
	public static function span($content, $attr = array())
	{
		return static::html_tag('span', $attr, $content);
	}

	/**
	 * Generates the html for the list methods
	 *
	 * @param	string	list type (ol or ul)
	 * @param	array	list items, may be nested
	 * @param	array	tag attributes
	 * @param	string	indentation
	 * @return	string
	 */
	protected static function build_list($type = 'ul', array $list = array(), $attr = false, $indent = '')
	{
		if ( ! is_array($list))
		{
			$result = false;
		}

		$out = '';
		foreach ( $list as $key => $val )
		{
			if ( ! is_array($val))
			{
				$out .= $indent."\t".static::html_tag('li', false, $val).PHP_EOL;
			}
			else
			{
				$out .= $indent."\t".static::html_tag('li', false, $key.PHP_EOL.static::build_list($type, $val, '', $indent."\t\t").$indent."\t").PHP_EOL;
			}
		}
		$result = $indent.static::html_tag($type, $attr, PHP_EOL.$out.$indent).PHP_EOL;
		return $result;
	}

	/**
	 * Create a XHTML tag
	 *
	 * @param	string			The tag name
	 * @param	array|string	The tag attributes
	 * @param	string|bool		The content to place in the tag, or false for no closing tag
	 * @return	string
	 */
	protected static function html_tag($tag, $attr = array(), $content = false)
	{
		// list of void elements (tags that can not have content)
		static $void_elements = array(
			// html4
			"area","base","br","col","hr","img","input","link","meta","param",
			// html5
			"command","embed","keygen","source","track","wbr",
			// html5.1
			"menuitem",
		);

		// construct the HTML
		$html = '<'.$tag;
		$html .= ( ! empty($attr)) ? ' '.(is_array($attr) ? static::array_to_attr($attr) : $attr) : '';

		// a void element?
		if (in_array(strtolower($tag), $void_elements))
		{
			// these can not have content
			$html .= ' />';
		}
		else
		{
			// add the content and close the tag
			$html .= '>'.$content.'</'.$tag.'>';
		}

		return $html;
	}

	/**
	 * Takes an array of attributes and turns it into a string for an html tag
	 *
	 * @param	array	$attr
	 * @return	string
	 */
	protected static function array_to_attr($attr)
	{
		$attr_str = '';

		foreach ( (array) $attr as $property => $value )
		{
			// Ignore null/false
			if ($value === null or $value === false)
			{
				continue;
			}

			// If the key is numeric then it must be something like selected="selected"
			if (is_numeric($property))
			{
				$property = $value;
			}

			$attr_str .= $property.'="'.str_replace('"', '&quot;', $value).'" ';
		}

		// We strip off the last space for return
		return trim($attr_str);
	}

	/**
	 * Takes an associative array in the layout of parse_url, and constructs a URL from it
	 *
	 * see http://www.php.net/manual/en/function.http-build-url.php#96335
	 *
	 * @param   mixed   (Part(s) of) an URL in form of a string or associative array like parse_url() returns
	 * @param   mixed   Same as the first argument
	 * @param   int     A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	 * @param   array   If set, it will be filled with the parts of the composed url like parse_url() would return
	 *
	 * @return  string  constructed URL
	 */
	protected static function http_build_url($url, $parts = array(), $flags = null, &$new_url = false)
	{
		return http_build_url($url, $parts, $flags, $new_url);
	}

}
