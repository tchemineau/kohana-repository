<?php defined('SYSPATH') or die('No direct access allowed.');

return array(

	// Specify that auth should use repository driver
	'driver'       => 'Repository',

	// Classic auth configuration parameter
	'lifetime'     => 1209600,
	'session_type' => Session::$default,
	'session_key'  => 'auth_user',

	// Specific configuration for Auth Repository driver
	'repository' => array (

		// Declare a repository to use. All repository will be checked
		// in the order they are declared here.
		'default' => array (

			// Name of the repository. This will be used to invoke
			// Repository::factory()
			'name' => 'user',

			// The function to call to authenticate a user. This
			// function have to be implemented by the repository.
			// It takes 3 parameters: username, password and
			// remember.
			'callback' => 'login'

		),

	),

);

