<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Repository class
 */
abstract class Kohana_Repository
{

	/**
	 * Repository data.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Repository mapper.
	 *
	 * @var Repository_Mapper
	 */
	protected $_mapper = null;

	/**
	 * Create a new repository instance
	 *
	 *     $repository = Repository::factory($name);
	 *
	 * @param string $name Repository name
	 * @return Repository
	 */
	public static function factory ( $name )
	{
		$class = 'Repository_' . $name;
		return new $class;
	}

	/**
	 * Set or get data.
	 *
	 * @return array
	 * @return boolean
	 */
	public function data ( $data = null )
	{
		if (is_null($data))
		{
			return $this->get_data();
		}
		return $this->set_data($data);
	}

	/**
	 * Delete the current element into the repository.
	 *
	 * @return boolean
	 */
	public function delete ()
	{
		return $this->mapper()->delete();
	}

	/**
	 * Get data from the repository.
	 *
	 * @param boolean default The default value to return
	 * @return array
	 * @return boolean
	 */
	public function get_data ( $default = false )
	{
		// Calculate the hash
		$hash = self::get_hash($this->mapper()->get_current_query());

		// Load data if not previously loaded
		if (!isset($this->_data[$hash]))
		{
			$this->_load_data();
		}

		// Return the appropriate value
		if (!isset($this->_data[$hash]) || is_null($this->_data[$hash]))
		{
			return $default;
		}
		return $this->_data[$hash];
	}

	/**
	 * Calculate a hash from a key.
	 *
	 * @param string key Key
	 * @param string salt Salt
	 * @return string
	 */
	protected static function get_hash ( $key, $salt = null )
	{
		if (is_null($salt))
		{
			$salt = Kohana::$config->load('repository')->get('cache_salt');
		}
		return sha1($salt.$key);
	}

	/**
	 * Get the repository mapper.
	 * If you want to overload it, use _get_mapper() method instead.
	 *
	 * @param boolean $force Force to rebuild the mapper.
	 * @return Repository_Mapper
	 */
	protected function get_mapper ( $force = false )
	{
		if ($force || is_null($this->_mapper))
		{
			$this->_mapper = $this->_get_mapper();
		}
		return $this->_mapper;
	}

	/**
	 * A alias of get_mapper.
	 * Instead of returning null, it throws an exception.
	 *
	 * @param boolean $force Force to rebuild the mapper.
	 * @return Repository_Mapper
	 */
	public function mapper ( $force = false )
	{
		$mapper = $this->get_mapper($force);
		if (is_null($mapper))
		{
			throw new Kohana_Exception('Impossible to get mapper for '.get_class());
		}
		return $mapper;
	}

	/**
	 * Select an object into the repository through a query.
	 *
	 * @param mixed $query
	 * @return mixed
	 */
	public function select ( $query )
	{
		$this->mapper()->select($query);
		return $this;
	}

	/**
	 * Set data and save it.
	 *
	 * @param array $data New data
	 * @param boolean $save If data have to be saved now
	 * @return boolean
	 */
	protected function set_data ( $data, $save = true )
	{
		// Calculate the hash
		$hash = self::get_hash($this->mapper()->get_current_query());

		// Here, we check that some values should be removed or not.
		if (isset($this->_data[$hash]) && !is_null($this->_data[$hash]) && !is_null($data))
		{
			$cdata = $this->_data[$hash];

			foreach ($data as $key => $value)
			{
				$cdata[$key] = $value;

				// If value is null, it means that we want to remove it.
				if (is_null($value) || (!is_array($value) && strcasecmp($value, 'null') == 0))
				{
					unset($cdata[$key]);
				}
			}

			$data = $cdata;
		}

		// Set data into the internal data array
		$this->_data[$hash] = $data;

		// By default, data is saved instantly to the repository
		if ($save)
		{
			return $this->_save_data();
		}

		// In all other case, set_data could not be into error
		return TRUE;
	}

	/**
	 * Get the mapper.
	 *
	 * @return Repository_Mapper
	 */
	protected abstract function _get_mapper ();

	/**
	 * Load data from the repository and store it into $_data.
	 * 
	 * @return boolean
	 */
	private function _load_data ()
	{
		// Load the repository mapper
		$mapper = $this->mapper();

		// Calculate the hash of the current query
		$hash = self::get_hash($mapper->get_current_query());

		// Store the cache status.
		$cache = Kohana::$config->load('repository')->get('cache_query');

		// Load from cache if enable
		if ($cache && !is_null($hash))
		{
			$this->_data[$hash] = Cache::instance()->get($hash);

			if (!is_null($this->_data[$hash]))
			{
				return TRUE;
			}
		}

		// If not found into cache, then load it
		$data = $mapper->get_data_as_array();

		// If it could not be retrieved, return false
		if (!is_array($data))
		{
			return FALSE;
		}

		// Keep it in memory
		ksort($data);
		$this->_data[$hash] = $data;

		// Save into cache if enable
		if ($cache)
		{
			$expire = Kohana::$config->load('repository')->get('cache_maxage');
			Cache::instance()->set($hash, $this->_data[$hash], $expire);
		}

		// Data load could not be into error
		return TRUE;
	}

	/**
	 * Save data into the file.
	 *
	 * @return boolean
	 */
	private function _save_data ()
	{
		// Store result
		$result = FALSE;

		// Load the mapper
		$mapper = $this->mapper();

		// Save data as array into the repository
		$data = $this->get_data();
		if ($mapper->set_data_from_array($data))
		{
			$result = $mapper->modify();
		}

		// Remove data into cache if enable and if the modification is successful
		if ($result && Kohana::$config->load('repository')->get('cache_query'))
		{
			$hash = $mapper->get_hash($mapper->get_current_query());

			if (!is_null($hash))
			{
				$expire = Kohana::$config->load('repository')->get('cache_maxage');

				if (is_null($data))
				{
					$expire = '-1';
				}
				Cache::instance()->set($hash, $data, $expire);
			}
		}

		// Finaly return the modification state
		return $result;
	}

}

