<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This mapper stores data into XML file.
 */
class Kohana_Repository_Mapper_Xml extends Repository_Mapper_Json
{

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Array uses when parsing XML data.
	 *
	 * @var array
	 */
	public static $_xmlData = Array();

	/**
	 * The last element when parsing XML data.
	 *
	 * @var array
	 */
	public static $_xmlLastElement = null;

	/**
	 * The XML tag used when parsing XML data.
	 *
	 * @var string
	 */
	public static $_xmlTag = null;

	/**
	 * Return data as a string.
	 *
	 * @return string
	 */
	public function get_data_as_string ()
	{
		$xmlstr = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		$xmlstr .= '<' . $this->get_type() . 's>' . "\n";
		foreach ($this->get_as_array() as $index => $value)
		{
			$xmlstr .= '	<' . $this->get_type() . ' id="' . $index . '">' . "\n";
			$xmlstr .= '		' . bas64_encode($value) . "\n";
			$xmlstr .= '	</' . $this->get_type() . '>' . "\n";
		}
		$xmlstr .= '</' . $this->get_type() . 's>';
		return $xmlstr;
	}

	/**
	 * Set data for the current selected element.
	 *
	 * @param string data The new data string.
	 * @return Repository_Mapper
	 */
	public function set_data_from_string ( $data )
	{
		self::$_xmlTag = $this->get_type();
		$xmlp = xml_parser_create();
		xml_set_element_handler($xmlp, "self::xml_opened_tag_callback", "self::xml_closed_tag_callback");
		xml_set_character_data_handler($xmlp, "self::xml_value_callback");
		xml_parse($xmlp, $data);
		xml_parser_free($xmlp);
		$this->_data = self::$_xmlData;
		self::$_xmlData = Array();
		return $this;
	}

	/**
	 * Callback function to parse opened XML tag.
	 *
	 * @param $parser The XML parser.
	 * @param $value The value to parse.
	 */
	public static function xml_closed_tag_callback ( $parser, $value )
	{
		if (strcasecmp($value, self::$_xmlTag) == 0)
		{
			self::$_xmlLastElement = null;
		}
	}

	/**
	 * Callback function to parse closed XML tag.
	 *
	 * @param $parser The XML parser.
	 * @param $value The value to parse.
	 * @param $parameters Tag's parameters.
	 */
	public static function xml_opened_tag_callback ( $parser, $value, $parameters )
	{
		if (strcasecmp($value, self::$_xmlTag) == 0)
		{
			self::$_xmlLastElement = $parameters['ID'];
		}
	}

	/**
	 * Callback function to parse data string inside a XML tag.
	 *
	 * @param $paser The XML parser.
	 * @param $value The value to parse.
	 */
	public static function xml_value_callback ( $parser, $value )
	{
		$data = self::$_xmlData;
		$attr = self::$_xmlLastElement;
		if (is_null($attr))
		{
			return;
		}
		$data[$attr] = base64_decode($value);
		self::$_xmlData = $data;
	}

}

