<?php
/**
 * unit-testcase:/unit/database/config.db.php
 *
 * @creation  2019-03-07
 * @version   1.0
 * @package   unit-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
//	...
$configs = [];

//	mysql
$configs['mysql'] = [
	'prod'     => 'mysql',
	'host'     => 'localhost',
	'port'     => '3306',
	'user'     => 'testcase',
	'password' => 'testcase',
	'database' => 'testcase',
	'charset'  => 'utf8',
];

return $configs;
