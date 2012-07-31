<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This mapper stores data into JSON format into file.
 */
class Kohana_Repository_Mapper_Json extends Repository_Mapper
{

	/**
	 * Mapper data.
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Mapper file.
	 *
	 * @var string
	 */
	protected $_file = null;

	/**
	 * Initialization data.
	 *
	 * @var mixed
	 */
	protected $_init = null;

	/**
	 * Delete the current element.
	 *
	 * @return boolean
	 */
	public function delete ()
	{
		$status = true;

		// If the current element is a file, delete the file
		$file = $this->get_file();
		if (is_file($file))
		{
			$status &= @unlink($file);
		}

		// If the current element has a directory, delete the directory
		$dir = $this->get_file(false);
		if (is_dir($dir))
		{
			$status &= @rmdir($dir);
		}

		return $status;
	}

	/**
	 * Indicates the current element exists.
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
	public function get_data_as_array ()
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
		$this->set_data_from_string(file_get_contents($file));
		return $this->_data;
	}

	/**
	 * Return data as a string.
	 *
	 * @return string
	 */
	public function get_data_as_string ()
	{
		return json_encode($this->get_data_as_array());
	}

	/**
	 * Return file.
	 *
	 * @param boolean ext With extension or not (default: true)
	 * @return string
	 */
	protected function get_file ( $ext = true )
	{
		$file = $this->_file;
		if ($ext)
		{
			$file .= '.'.self::$type;
		}
		return $file;
	}

	/**
	 * Return the current query.
	 *
	 * @return string
	 */
	public function get_current_query ()
	{
		return $this->get_file(false);
	}

	/**
	 * Initialize this mapper.
	 *
	 * @param array initialization An array of parameters.
	 * @return Repository_Mapper
	 */
	public function initialize ( $initialization )
	{
		$this->_data = null;
		$this->_init = $initialization;

		if (isset($initialization['query']))
		{
			$this->select($initialization['query']);
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
				fwrite($fp, $this->get_data_as_string());
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
		// If it's the same name, then rename return true
		$file = $this->get_current_query();
		if (strcmp($file, $query) == 0)
		{
			return TRUE;
		}

		// Error if the query file already exists
		if (is_file($query))
		{
			return FALSE;
		}

		// Check if the new parent directory exists
		$dir = dirname($query);
		if (!is_dir($dir) && !@mkdir($dir, 0777, true))
		{
			return FALSE;
		}

		// Rename the current element
		if (!@rename($file, $query . '.' . self::$type))
		{
			return FALSE;
		}

		// The current element has been renamed, select it
		$this->select($query);
		return TRUE;
	}

	/**
	 * Select an element through a query.
	 *
	 * @param string query A query string
	 * @return Repository_Mapper
	 */
	public function select ( $query )
	{
		$this->_file = $query;
		return $this;
	}

	/**
	 * Set data for the current selected element.
	 *
	 * @param array data The new data array.
	 * @return Repository_Mapper
	 */
	public function set_data_from_array ( $data )
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
	public function set_data_from_string ( $data )
	{
		$this->_data = json_decode($data, true);
		return $this;
	}

}

