<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Repository auth driver.
 * This auth driver does not support roles nor autologin.
 *
 * @package    Kohana/Repository
 * @author     Thomas Chemineau - thomas.chemineau@gmail.com
 * @copyright  (c) 2012 Thomas Chemineau
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Auth_Repository extends Auth
{

	/**
	 * Repositories to use.
	 *
	 * @var array
	 */
	private $_repositories = array();

	/**
	 * Constructor.
	 */
	public function __construct ( $config = array() )
	{
		parent::__construct($config);

		if (!isset($config['repository']) || !is_array($config['repository']))
		{
			throw new Kohana_Exception('A repository should be configured');
		}

		foreach ($config['repository'] as $repository_name => $repository_config)
		{
			if (isset($repository_config['name']) && isset($repository_config['callback']))
			{
				$this->_repositories[$repository_name] = $repository_config;
			}
		}
	}

	/**
	 * Not implemented.
	 */
	public function password ( $username )
	{
	}

	/**
	 * Not implemented.
	 */
	public function check_password ( $password )
	{
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable autologin (not supported)
	 * @return  boolean
	 */
	protected function _login ( $username, $password, $remember )
	{
		foreach ($this->_repositories as $repository_config)
		{
			$callback = $repository_config['callback'];
			$repository = Repository::factory($repository_config['name']);
			$userdata = call_user_func(array($repository, $callback), $username, $password, $remember);

			if ($userdata !== false)
			{
				return $this->complete_login(array(
					'username' => $username,
					'userdata' => $userdata
				));
			}
		}

		return FALSE;
	}

} // End Auth Repository

