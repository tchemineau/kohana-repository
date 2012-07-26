<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This mapper stores data into JSON format into file.
 */
class Kohana_Repository_Mapper_Json extends Repository_Mapper
{

	/**
	 * Mapper type label.
	 *
	 * @var string
	 */
	public static $type = 'json';

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
	 * Delete the current selected element.
	 *
	 * @return boolean
	 */
	public function delete ()
	{
		$dir = $this->get_file(false);
		$file = $this->get_file();
		$status = true;
		if (is_file($file))
		{
			$status &= @unlink($file);
		}
		if (is_dir($dir))
		{
			$status &= @rmdir($dir);
		}
		return $status;
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
		return json_encode($this->get_as_array());
	}

	/**
	 * Return file.
	 *
	 * @param boolean ext With extension or not (default: true)
	 * @return string
	 */
	public function get_file ( $ext = true )
	{
		$file = $this->_file;
		if ($ext)
		{
			$file .= '.'.self::$type;
		}
		return $file;
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
	 * @return string.
	 */
	public function get_select_parameter ()
	{
		return $this->_file;
	}

	/**
	 * Initialize this mapper.
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
		$this->_data = json_decode($data, true);
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

}

