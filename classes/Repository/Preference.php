<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This is a example of a simple repository.
 *
 * A Preference repository manages preferences (like settings) by saving and
 * loading data from a file into the default Kohana cache directory (this is the
 * query). A type is defined, but not use by JSON mapper.
 *
 * To enable it, copy the config/repository-sample.php into the config directory
 * of your application, and rename it to repository.php. Choose your default
 * mapper type into this configuration file.
 *
 * Into your project, use this repository like the following:
 *
 * 1/ If you want to save your preferences:
 *
 *   $preferences = Repository::factory('Preference');
 *   $preferences->set_data(array(
 *     'var1' => 'sample value 1',
 *     'var2' => array(
 *       'key1' => 'sample value 2'
 *     ),
 *   ));
 *
 * 2/ If you want to retrieve your preferences previously saved:
 *
 *   $preferences = Repository::factory('Preference');
 *   $data = $preferences->get_data();
 *
 * Note that you can enable cache by editing the configuration file. If so, be
 * sure to give a unique salt. When using cache, repository took data from the
 * cache server configured into your Kohana application. It also save data into
 * the cache server when you saved data.
 *
 * The benefit of using this kind of technic is to abstract the way to access
 * the model. You could in that way develop a MySQL mapper that feets your need
 * to store data into your MySQL server, without redeveloping all your
 * application (just changing the mapper type).
 */
class Repository_Preference extends Repository
{

	/**
	 * Get the mapper of this repository.
	 *
	 * @return Repository_Mapper
	 */
	protected function _get_mapper ()
	{
		return Repository_Mapper::factory()->initialize(array(
			'query' => Kohana:$cache_dir.'repository/preference',
			'type' => 'preference'
		));
	}

}

