<?php
/** op-unit-database:/autoloader.php
 *
 * @created   2018-05-18
 * @license   Apache-2.0
 * @package   op-unit-database-ci
 * @copyright (C) 2025 Tomoaki Nagahara
 */
//	...
spl_autoload_register( function($name){
	//	...
	$namespace = 'OP\UNIT\DATABASE\\';

	//	...
	if( strpos($name, $namespace) !== 0 ){
		return;
	}

	//	...
	$class = str_replace($namespace, '', $name);

	//	...
	$path = __DIR__."/{$class}.class.php";

	//	...
	if( file_exists($path) ){
		include($path);
	}else{
		OP\Notice::Set("Does not exists this file. ($path)");
	}
});
