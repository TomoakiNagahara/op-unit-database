<?php
/**
 * module-testcase:/unit/database/orm/action.php
 *
 * @creation  2019-04-19
 * @version   1.0
 * @package   module-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-04-19
 */
namespace OP;

/* @var $app \OP\UNIT\App */
/* @var $orm \OP\UNIT\ORM */
$args = Unit::Singleton('Router')->Args();
$orm  = Unit::Singleton('ORM');
$prod = $args[2] ?? null;
$configs = include(__DIR__.'/../config.db.php');
$config  = $configs[$prod] ?? null;
$request = $app->Request();
$ai      = $request['ai'] ?? null;
$table   = 't_table';
$qql     = $ai ? "{$table}.ai = {$ai}": $table;

//	...
define('_DSN_', 'mysql://orm@localhost:80?pass=password&database='._DATABASE_NAME_, true);

//	...
if( $_GET['selftest'] ?? null ){
	//	...
	$orm->Selftest(__DIR__.'/selftest.config.php');

	//	...
	return;
};

//  Connect database.
$orm->Connect($config);

//  Generate "Record" object at database table defined.
$record = $orm->Find($qql);

//  Is found?
Html('Is record found? - '. $record->isFound() ? 'Yes':'No');

//  Change value.
$record->required = 2;
D($record->required);

//	Check if delete order.
if( $app->Request()['delete'] ?? null ){
	//	Do delete record.
	if( $orm->Delete($record) ){
		//	Get empty record.
		$record = $orm->Create('t_orm');
	};
};

//	Automatically. (Validate is includion)
$orm->Save($record);

//  Display form of html.
include(__DIR__.'/form.phtml');

//	...
$orm->Debug();
$record->Form()->Debug();
