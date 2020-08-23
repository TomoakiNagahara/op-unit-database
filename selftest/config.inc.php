<?php
/**
 * unit-testcase:/unit/database/selftest/config.inc.php
 *
 * @creation  2019-04-12
 * @version   1.0
 * @package   unit-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-04-12
 */
namespace OP;

/* @var $selftest \OP\UNIT\Selftest          */
$selftest = Unit::Instantiate('Selftest');

/* @var $configer \OP\UNIT\SELFTEST\Configer */
$configer = $selftest->Configer();

//	...
if( $configs = include(__DIR__.'/../config.db.php') ){
	//	...
	$prod     = Unit::Singleton('Router')->Args()[2];
	$config   = $configs[$prod];
	$host     = $config['host'];
	$user     = $config['user'];
	$password = $config['password'];
	$database = $config['database'];
}else{
	throw new \Exception("Selftest config file has not been exists.");
};

//  DSN configuration.
$configer->DSN([
	'host'     => $host,
	'product'  => $prod,
	'port'     => '3306',
]);

//  User configuration.
$configer->User([
	'host'     => $host,
	'name'     => $user,
	'password' => $password,
	'charset'  => 'utf8',
]);

//  Privilege configuration.
$configer->Privilege([
	'user'     => $user,
	'database' => $database,
	'table'    => '*',
	'privilege'=> 'insert, select, update, delete',
	'column'   => '*',
]);

//  Database configuration.
$configer->Database([
	'name'     => $database,
	'charset'  => 'utf8',
	'collate'  => 'utf8mb4_general_ci',
]);

//  Add table configuration.
$configer->Set('table', [
	'name'    => 't_table',
	/* Can be omitted. To be inherited from database.
	 'charset' => 'utf8',
	 'collate' => 'utf8mb4_general_ci',
	 */
	'comment' => 'This is test table.',
]);

//  Add auto incrment id column configuration.
$configer->Set('column', [
	'name'    =>  'ai',
	/* Automatically
	 'type'    => 'int',
	 'length'  =>    10,
	 'null'    => false,
	 'default' =>  null,
	 'unsigned'=>  true,
	 */
	'comment' => 'Auto increment id.',
	'ai'      =>  true,
]);

//  Add type of set column configuration.
$configer->Set('column', [
	'name'    =>   'id',
	'type'    => 'char',
	'length'  =>     10,
	'null'    =>   true,
	'default' =>   null,
	'collate' => 'ascii_general_ci', // Change collate.
	'comment' => 'Unique ID.',
	'unique'  =>   true,
]);

//  Add type of set column configuration.
$configer->Set('column', [
	'name'    => 'flags',
	'type'    => 'set',
	'length'  => 'a, b, c',
	'null'    =>  true,
	'default' =>  null,
	'collate' => 'ascii_general_ci', // Change collate.
	'comment' => 'Ideal for form of checkbox values. (Multiple choice)',
]);

//  Add type of enum column configuration.
$configer->Set('column', [
	'name'    => 'choice',
	'type'    => 'enum',
	'length'  => 'a, b, c',
	/*
	 'null'    =>  true, // Can be omitted.
	 'default' =>  null, // Can be omitted.
	 */
	'comment' => 'Ideal for form of select or radio mono value. (Single choice)',
]);

//  Add type of timestamp configuration.
$configer->Set('column', [
	'name'    => 'timestamp',
	'type'    => 'timestamp',
	'comment' => 'On update current timestamp.',
]);

//  Add auto incrment id configuration.
$configer->Set('index', [
	'name'    => 'ai',
	'type'    => 'ai',
	'column'  => 'ai',
	'comment' => 'auto incrment',
]);

//  Add search index configuration.
$configer->Set('index', [
	'name'    => 'search index',
	'type'    => 'index',
	'column'  => 'flags, choice',
	'comment' => 'Indexed by two columns.',
]);

//  Return selftest configuration.
return $configer->Get();
