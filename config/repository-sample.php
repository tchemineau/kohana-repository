<?php defined('SYSPATH') or die('No direct script access.');

return array
(

	/**
	 * Enable query cache.
	 */
	'cache_query' => false,

	/**
	 * Cache max age if query cache is enabled.
	 */
	'cache_maxage' => 600,

	/**
	 * Salt use to calculate key from query mapper, if cache is enabled.
	 */
	'cache_salt' => 'CHANGE_IT',

	/**
	 * Default data mapper to use.
	 * Case-sensitive
	 */
	'mapper_default' => 'Json',

	/**
	 * Old used data mapper.
	 * Enable this when you need to migrate your data
	 * from a mapper to a new one.
	 */
	// 'mapper_from' => 'Xml',

);

