<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This mapper stores data into a LDAP directory
 */
class Kohana_Repository_Mapper_Ldap extends Repository_Mapper
{

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Data Query
	 *
	 * @var string
	 */
	protected $_dataquery = null;

	/**
	 * Query.
	 *
	 * @var string
	 */
	protected $_query = null;

	/**
	 * Ldap
	 *
	 * @var array
	 */
	protected $_ldap = array();

	/**
	 * Build a new Ldap mapper.
	 */
	public function __construct ()
	{
		$config = Kohana::$config->load('database')->as_array();

		if (!is_null($config) && is_array($config))
		{
			foreach ($config as $dbid => $dbdata)
			{
				if (!isset($dbdata['type']) || strcasecmp($dbdata['type'], 'ldap') != 0)
				{
					unset($config[$dbid]);
				}
			}

			$this->_ldap = $config;
		}
	}

	/**
	 * Delete the current element.
	 * Not yet supported, return always false.
	 *
	 * @return boolean
	 */
	public function delete ()
	{
		return false;
	}

	/**
	 * Indicates the current element exists.
	 * Not yet supported, return always false.
	 *
	 * @return boolean
	 */
	public function exists ()
	{
		return false;
	}

	/**
	 * Return the current query.
	 *
	 * @return string
	 */
	public function get_current_query ()
	{
		return $this->_query;
	}

	/**
	 * Return data as an array.
	 *
	 * @return array
	 */
	public function get_data_as_array ()
	{
		$query = $this->get_current_query();

		if (!is_null($this->_data) && !is_null($this->_dataquery) && $this->_dataquery == $query)
		{
			return $this->_data;
		}

		foreach ($this->_ldap as $serverid => $config)
		{
			$ldapdb = Database::instance($serverid, $config);

			// Prepare the query depending of the type
			switch ($this->get_type())
			{
				case 'user':
					$query = $ldapdb->prepare_query_user(array(), $query);
					break;
			}

			// Be carefull, here, $query might have been changed by the prepare function.
			$result = $ldapdb->query(Database::SELECT, $query);

			// If results found, format, set and stop loop
			if (is_array($result) && sizeof($result) > 0)
			{
				$data = $result;

				if (isset($query['attributes']))
				{
					$data = $ldapdb->parse_result($data, $query['attributes']);
					$data = $data[0];
				}

				$this->set_data_from_array($data);
				$this->_dataquery = $query;

				break;
			}
		}

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
	 * Modify the current selected element.
	 * Not yet supported, return always false.
	 *
	 * @return boolean
	 */
	public function modify ()
	{
		return false;
	}

	/**
	 * Rename the current selected element.
	 * Not yet supported, return always false.
	 *
	 * @param string query A query string
	 * @return boolean
	 */
	public function rename ( $query )
	{
		return false;
	}

	/**
	 * Select an element through a query.
	 *
	 * @param string query A query string
	 * @return Repository_Mapper
	 */
	public function select ( $query )
	{
		$this->_query = $query;
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

