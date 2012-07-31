<?php defined('SYSPATH') or die('No direct script access.');

/**
 * A mapper allow to access to data in a generic way.
 * This abstract class defines generic functions that mapper have to implement.
 */
abstract class Kohana_Repository_Mapper
{

	/**
	 * Convert data of an old repository mapper to the current element
	 *
	 * @return Model_Mapper
	 */
	public function convert ()
	{
		$oldtype = Kohana::$config->load('repository')->get('mapper_from');

		if (!is_null($oldtype))
		{
			$oldmapper = self::factory($oldtype)
				->initialize($this->get_initialization())
				->select($this->get_current_query);

			if ($oldmapper->exists())
			{
				$this->set_data_from_array($oldmapper->get_data_as_array());

				if ($this->modify())
				{
					$oldmapper->delete();
				}
			}
		}

		return $this;
	}

	/**
	 * Delete the current element.
	 *
	 * @return boolean
	 */
	public abstract function delete ();

	/**
	 * Indicates if the current element exists.
	 *
	 * @return boolean
	 */
	public abstract function exists ();

	/**
	 * Get an instance of given registered mapper.
	 * If no type given, get it from global options.
	 *
	 * @param $type Type of the mapper.
	 * @return Model_Mapper
	 */
	public static function factory ( $type = null )
	{
		if (is_null($type))
		{
			$type = self::get_default_type();
		}

		$class = 'Repository_Mapper_' . $type;
		return new $class;
	}

	/**
	 * Return data as an array.
	 *
	 * @return Array
	 */
	public abstract function get_data_as_array ();

	/**
	 * Return data as a string.
	 *
	 * @return String
	 */
	public abstract function get_data_as_string ();

	/**
	 * Return a the current query.
	 *
	 * @return mixed
	 */
	public abstract function get_current_query ();

	/**
	 * Get the default type.
	 *
	 * @return string
	 */
	public static function get_default_type ()
	{
		return Kohana::$config->load('repository')->get('mapper_default');
	}

	/**
	 * Get initialization parameters.
	 *
	 * @return mixed
	 */
	public abstract function get_initialization ();

	/**
	 * Initialize the mapper and return it.
	 *
	 * @param $initialization Init parameters
	 * @return Model_Mapper
	 */
	public abstract function initialize ( $initialization );

	/**
	 * Modify the current selected element.
	 *
	 * @return boolean
	 */
	public abstract function modify ();

	/**
	 * Rename the current selected element.
	 *
	 * @param $query A new query
	 * @return boolean
	 */
	public abstract function rename ( $query );

	/**
	 * Select an element through a query.
	 *
	 * @param $query A query
	 * @return Model_Mapper
	 */
	public abstract function select ( $query );

	/**
	 * Set data for the current selected element.
	 *
	 * @param $data The new data array.
	 * @return boolean
	 */
	public abstract function set_data_from_array ( $data );

	/**
	 * Set data for the current selected element.
	 *
	 * @param $string The new data string.
	 * @return boolean
	 */
	public abstract function set_data_from_string ( $string );

}

