<?php
	//
	// Choose which access method to use when creating class variables.
	// 	- If you use "private", getters and setters will be generated.
	// 	- If you use "public", getters and setters will not be generated.
	//
	//$ACCESS_METHOD = 'private';
	$ACCESS_METHOD = 'public';

	//
	// what kind of mysql code to generate
	//
	define('DO_STANDARD', false);
	define('DO_PREPARED', true);
	define('DO_STATIC', false);

	//
	// define db settings
	//
	$db_host = "127.0.0.1";
	$db_user = "root";
	$db_pw 	 = "ads6264";
	$db_name = "forms";
	$db_port = 3306;

	date_default_timezone_set ("UTC");
?>