<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Repository class
 */
abstract class Kohana_Repository
{

	/**
	 * Data.
	 *
	 * @var array
	 */
	private $_data = null;

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
	 * Calculate a hash from a key.
	 *
	 * @param string key Key
	 * @param string salt Salt
	 * @return string
	 */
	public static function get_cache_hash ( $key, $salt = null )
	{
		if (is_null($salt))
		{
			$salt = Kohana::$config->load('repository')->get('cache_salt');
		}
		return sha1($salt.$key);
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
		if (!is_null($this->_data) || $this->load_data())
		{
			return $this->_data;
		}
		return $default;
	}

	/**
	 * Get the mapper.
	 *
	 * @return Repository_Mapper
	 */
	protected abstract function get_mapper ();

	/**
	 * Load data from the repository.
	 *
	 * @return boolean
	 */
	protected function load_data ()
	{
		$hash = null;
		$mapper = $this->get_mapper();
		if (is_null($mapper))
		{
			return false;
		}
		$cache = Kohana::$config->load('repository')->get('cache_query');
		if ($cache)
		{
			$hash = self::get_cache_hash($mapper->get_query_hash());
			if (!is_null($hash))
			{
				$this->_data = Cache::instance()->get($hash);
				if (!is_null($this->_data))
				{
					return true;
				}
			}
		}
		$this->_data = $mapper->get_as_array();
		ksort($this->_data);
		if ($cache)
		{
			$expire = Kohana::$config->load('repository')->get('cache_maxage');
			Cache::instance()->set($hash, $this->_data, $expire);
		}
		return true;
	}

	/**
	 * Save data into the file.
	 *
	 * @return boolean
	 */
	protected function save_data ()
	{
		$mapper = $this->get_mapper();
		if (is_null($mapper))
		{
			return;
		}
		$cache = Kohana::$config->load('repository')->get('cache_query');
		$mapper->set_from_array($this->get_data());
		$result = $mapper->modify();
		if ($mapper->modify())
		{
			if ($cache)
			{
				$hash = self::get_cache_hash($mapper->get_query_hash());
				if (!is_null($hash))
				{
					$data = $this->get_data();
					$expire = Kohana::$config->load('repository')->get('cache_maxage');
					if (is_null($data))
					{
						$expire = '-1';
					}
					Cache::instance()->set($hash, $data, $expire);
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Set data and save it.
	 *
	 * @param array $data New data.
	 * @param boolean $save If data have to be saved.
	 * @return boolean
	 */
	public function set_data ( $data, $save = true )
	{
		if (!is_null($this->_data) && !is_null($data))
		{
			foreach ($data as $key => $value)
			{
				if (is_null($value) || (!is_array($value) && strcasecmp($value, 'null') == 0))
				{
					unset($this->_data[$key]);
				}
				else
				{
					$this->_data[$key] = $value;
				}
			}
		}
		else
		{
			$this->_data = $data;
		}
		if ($save)
		{
			return $this->save_data();
		}
		return true;
	}

}

