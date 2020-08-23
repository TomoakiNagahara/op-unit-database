<?php
/**
 * unit-testcase:/unit/database/selftest/action.php
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

/* @var $app      UNIT\App      */
/* @var $selftest UNIT\Selftest */
$selftest = $app->Unit('Selftest');

//	...
$selftest->Auto(__DIR__.'/config.inc.php');

//	...
if( $_GET['debug']['selftest'] ?? null ){
	$selftest->Debug();
};
