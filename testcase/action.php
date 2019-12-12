<?php
/**
 * unit-testcase:/unit/database/action.php
 *
 * @creation  2019-03-04
 * @version   1.0
 * @package   unit-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-03-04
 */
namespace OP;

/* @var $app    UNIT\App      */
/* @var $db     UNIT\database */
/* @var $form   UNIT\Form     */

//	...
$args = $app->Args();

//	...
$prod   = isset($args) ? ($args[2] ?? null): null;
$cate   = isset($args) ? ($args[3] ?? null): null;
$action = isset($args) ? ($args[4] ?? null): null;

//	...
include('action_menu.phtml');

//	...
if( file_exists($file = __DIR__."/{$cate}/action.php") ){
	include($file);
}else{
	D($prod, $cate, $action, $file);
};

/*
switch( $cate ){
	case 'orm':
		$app->Template(__DIR__.'/orm/action.php');
		break;

	case 'selftest':
		$app->Template(__DIR__.'/selftest/action.php');
		break;

	default:
		//	...
		if( file_exists(__DIR__."/{$cate}/{$action}.inc.php") ){
			include(__DIR__."/{$cate}/action.inc.php");
		};
	break;
};
*/
