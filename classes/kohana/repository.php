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
	protected $_data = null;

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
	 * Get data from the repository.
	 *
	 * @return boolean default The default value to return
	 * @return array
	 * @return boolean
	 * @return mixed
	 */
	public function get_data ( $default = false )
	{
		if (!is_null($this->_data) || $this->_load_data())
		{
			return $this->_data;
		}
		return $default;
	}

	/**
	 * Calculate a hash from a key.
	 *
	 * @param string key Key
	 * @param string salt Salt
	 * @return string
	 */
	public static function get_hash ( $key, $salt = null )
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
	public function get_mapper ( $force = false )
	{
		if ($force || is_null($this->_mapper))
		{
			$this->_mapper = $this->_get_mapper();
		}
		return $this->_mapper;
	}

	/**
	 * Set data and save it.
	 *
	 * @param array $data New data
	 * @param boolean $save If data have to be saved now
	 * @return boolean
	 */
	public function set_data ( $data, $save = true )
	{
		// Here, we check that some values should be removed or not.
		if (!is_null($this->_data) && !is_null($data))
		{
			foreach ($data as $key => $value)
			{
				$this->_data[$key] = $value;

				// If value is null, it means that we want to remove it.
				if (is_null($value) || (!is_array($value) && strcasecmp($value, 'null') == 0))
				{
					unset($this->_data[$key]);
				}
			}
		}
		else
		{
			$this->_data = $data;
		}

		// By default, data is saved instantly to the repository
		if ($save)
		{
			return $this->_save_data();
		}

		// In all other case, set_data could not be into error
		return true;
	}

	/**
	 * Get the mapper.
	 *
	 * @return Repository_Mapper
	 */
	protected function _get_mapper ()
	{
		return Repository_Mapper::factory()->initialize(array());
	}

	/**
	 * Load data from the repository and store it into $_data.
	 * 
	 * @return boolean
	 */
	protected function _load_data ()
	{
		// Load the repository mapper
		$mapper = $this->get_mapper();
		if (is_null($mapper))
		{
			return FALSE;
		}

		// Calculate the hash of the current query
		$hash = self::get_hash($mapper->get_current_query());

		// Load from cache if enable
		if (Kohana::$config->load('repository')->get('cache_query'))
		{
			$hash = self::get_hash($mapper->get_current_query());

			if (!is_null($hash))
			{
				$this->_data = Cache::instance()->get($hash);

				if (!is_null($this->_data))
				{
					return TRUE;
				}
			}
		}

		// If not found into cache, then load it
		$this->_data = $mapper->get_data_as_array();
		ksort($this->_data);

		// Save into cache if enable
		if (Kohana::$config->load('repository')->get('cache_query'))
		{
			$expire = Kohana::$config->load('repository')->get('cache_maxage');
			Cache::instance()->set($hash, $this->_data, $expire);
		}

		// Data load could not be into error
		return TRUE;
	}

	/**
	 * Save data into the file.
	 *
	 * @return boolean
	 */
	protected function _save_data ()
	{
		// Load the mapper
		$mapper = $this->get_mapper();
		if (is_null($mapper))
		{
			return FALSE;
		}

		// Save data as array into the repository
		$data = $this->get_data();
		$mapper->set_data_from_array($data);
		$result = $mapper->modify();

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

