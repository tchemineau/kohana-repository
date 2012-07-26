<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This mapper stores data into XML file.
 */
class Kohana_Repository_Mapper_Xml extends Repository_Mapper
{

	/**
	 * Mapper type label.
	 *
	 * @var string
	 */
	public static $type = 'xml';

	/**
	 * Mapper data.
	 *
	 * @var array
	 */
	private $_data = null;

	/**
	 * Mapper file.
	 *
	 * @var string
	 */
	private $_file = null;

	/**
	 * Init parameters.
	 *
	 * @var array
	 */
	private $_init = null;

	/**
	 * XML tag.
	 *
	 * @var string
	 */
	private $_tag = null;

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
	 * Delete the current selected element.
	 *
	 * @return boolean
	 */
	public function delete ()
	{
		$file = $this->get_file();
		if (is_file($file))
		{
			return @unlink($file);
		}
		return true;
	}

	/**
	 * Indicates if this mapper as a corresponding file.
	 *
	 * @return boolean
	 */
	public function exists ()
	{
		return is_file($this->get_file());
	}

	/**
	 * Return data as an array.
	 *
	 * @return array
	 */
	public function get_as_array ()
	{
		if (!is_null($this->_data))
		{
			return $this->_data;
		}
		$file = $this->get_file();
		if (!is_file($file))
		{
			return Array();
		}
		$this->set_from_string(file_get_contents($file));
		return $this->_data;
	}

	/**
	 * Return data as a string.
	 *
	 * @return string
	 */
	public function get_as_string ()
	{
		$xmlstr = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		$xmlstr .= '<' . $this->_tag . 's>' . "\n";
		foreach ($this->get_as_array() as $index => $value)
		{
			$xmlstr .= '	<' . $this->_tag . ' id="' . $index . '">' . "\n";
			$xmlstr .= '		' . Helper::factory('Encrypt')->encode($value) . "\n";
			$xmlstr .= '	</' . $this->_tag . '>' . "\n";
		}
		$xmlstr .= '</' . $this->_tag . 's>';
		return $xmlstr;
	}

	/**
	 * Get file.
	 *
	 * @return string
	 */
	public function get_file ()
	{
		return $this->_file.'.'.self::$type;
	}

	/**
	 * Get initialization parameters.
	 *
	 * @return array
	 */
	public function get_init_parameter ()
	{
		return $this->_init;
	}

	/**
	 * Return a hash of the current query.
	 *
	 * @return string
	 */
	public function get_query_hash ()
	{
		return $this->_file;
	}

	/**
	 * Return select parameter.
	 *
	 * @return string
	 */
	public function get_select_parameter ()
	{
		return $this->_file;
	}

	/**
	 * Initialize this xml mapper.
	 *
	 * @param array parameters An array of parameters.
	 * @return Repository_Mapper
	 */
	public function init ( $parameters )
	{
		$this->_data = null;
		$this->_init = $parameters;
		if (isset($parameters['file']))
		{
			$this->set_file($parameters['file']);
		}
		if (isset($parameters['tag']))
		{
			$this->set_tag($parameters['tag']);
		}
		return $this;
	}

	/**
	 * Modify the current selected element.
	 *
	 * @return boolean
	 */
	public function modify ()
	{
		$file = $this->get_file();
		$dir = dirname($file);
		if (is_dir($dir) || mkdir($dir, 0777, true))
		{
			$fp = fopen($file, 'w');
			if ($fp !== false)
			{
				fwrite($fp, $this->get_as_string());
				fclose($fp);
				return true;
			}
		}
		return false;
	}

	/**
	 * Rename the current selected element.
	 *
	 * @param string query A query string
	 * @return boolean
	 */
	public function rename ( $query )
	{
		$file = $this->get_file();
		if (strcmp($file, $query) != 0)
		{
			$dir = dirname($query);
			if (!is_dir($dir))
			{
				mkdir($dir, 0777, true);
			}
			if (is_file($query) || !rename($file, $query.'.'.self::$type))
			{
				return false;
			}
			$this->select($query);
			return true;
		}
		return true;
	}

	/**
	 * Select an element through a query.
	 * This function is an alias of set_file.
	 *
	 * @param string query A query string
	 * @return Repository_Mapper
	 */
	public function select ( $query )
	{
		$this->set_file($query);
		return $this;
	}

	/**
	 * Set data for the current selected element.
	 *
	 * @param array data The new data array.
	 * @return Repository_Mapper
	 */
	public function set_from_array ( $data )
	{
		$this->_data = $data;
		return $this;
	}

	/**
	 * Set data for the current selected element.
	 *
	 * @param string data The new data string.
	 * @return Repository_Mapper
	 */
	public function set_from_string ( $data )
	{
		self::$_xmlTag = $this->_tag;
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
	 * Set file.
	 *
	 * @param string file A file
	 * @return Repository_Mapper
	 */
	public function set_file ( $file )
	{
		$this->_file = $file;
		return $this;
	}

	/**
	 * Set tag.
	 *
	 * @param string tag A tag
	 * @return Repository_Mapper
	 */
	public function set_tag ( $tag )
	{
		$this->_tag = $tag;
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
		$data[$attr] = Helper::factory('Encrypt')->decode($value);
		self::$_xmlData = $data;
	}

}

