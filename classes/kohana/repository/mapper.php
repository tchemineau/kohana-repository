<?php defined('SYSPATH') or die('No direct script access.');

/**
 * A mapper allow to access to data in a generic way.
 * This abstract class defines generic functions that mapper have to implement.
 */
abstract class Kohana_Repository_Mapper
{

	/**
	 * Convert the current selected element from old element.
	 * @return Model_Mapper
	 */
	public function convert ()
	{
		$oldtype = Kohana::$config->load('repository')->get('mapper_from');
		if (!is_null($oldtype))
		{
			$oldmapper = self::factory($oldtype)
				->init($this->get_init_parameter())
				->select($this->get_select_parameter());
			if ($oldmapper->exists())
			{
				$this->set_from_array($oldmapper->get_as_array());
				if ($this->modify())
				{
					$oldmapper->delete();
				}
			}
		}
		return $this;
	}

	/**
	 * Delete the current selected element.
	 * @return boolean
	 */
	public abstract function delete ();

	/**
	 * Indicates if the current selected element exists.
	 * @return boolean
	 */
	public abstract function exists ();

	/**
	 * Get an instance of given registered mapper.
	 * If no type given, get it from global options.
	 * @param $type Type of the mapper.
	 * @return Model_Mapper
	 */
	public static function factory ( $type = null )
	{
		if (is_null($type))
		{
			$type = self::type();
		}
		$class = 'Repository_Mapper_' . $type;
		return new $class;
	}

	/**
	 * Return data as an array.
	 * @return Array
	 */
	public abstract function get_as_array ();

	/**
	 * Return data as a string.
	 * @return String
	 */
	public abstract function get_as_string ();

	/**
	 * Get initialization parameters.
	 * @return array
	 */
	public abstract function get_init_parameter ();

	/**
	 * List available mapper type.
	 */
	public static function get_list ()
	{
		if (is_null(self::$mappers))
		{
			$list = array();
			$dir = new DirectoryIterator(dirname(__FILE__).DIRECTORY_SEPARATOR.'mapper');
			foreach ($dir as $file)
			{
				$filename = $file->getFilename();
				if ($filename[0] === '.' || $file->isDir())
				{
					continue;
				}
				$basename = $file->getBasename();
				$list[$basename] = $basename;
			}
			self::$mappers = $list;
		}
		return self::$mappers;
	}

	/**
	 * Return a hash of the current query.
	 * @return String|null
	 */
	public abstract function get_query_hash ();

	/**
	 * Return latest select parameter.
	 * @return string
	 */
	public abstract function get_select_parameter ();

	/**
	 * Initialize the mapper and return it.
	 * @param $parameters Parameters
	 * @return Model_Mapper
	 */
	public abstract function init ( $parameters );

	/**
	 * Modify the current selected element.
	 * @return boolean
	 */
	public abstract function modify ();

	/**
	 * Rename the current selected element.
	 * @param $query A query string
	 * @return boolean
	 */
	public abstract function rename ( $query );

	/**
	 * Select an element through a query.
	 * @param $query A query string
	 * @return Model_Mapper
	 */
	public abstract function select ( $query );

	/**
	 * Set data for the current selected element.
	 * @param $data The new data array.
	 * @return boolean
	 */
	public abstract function set_from_array ( $data );

	/**
	 * Set data for the current selected element.
	 * @param $string The new data string.
	 * @return boolean
	 */
	public abstract function set_from_string ( $string );

	/**
	 * Return the default mapper type from global option.
	 * @return String
	 */
	public static function type ()
	{
		return Kohana::$config->load('repository')->get('mapper_default');
	}

}

