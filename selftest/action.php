<?php
/**	op-unit-testcase:/unit/database/selftest/action.php
 *
 * @creation   2019-04-12
 * @version    1.0
 * @package    op-unit-testcase
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All rights reserved.
 */

/**	Namespace
 *
 * @creation  2019-04-12
 */
namespace OP;

/* @var $app      IF_APP      */
/* @var $selftest IF_SELFTEST */
$selftest = $app->Unit('Selftest');

//	...
$selftest->Auto(__DIR__.'/config.inc.php');

//	...
if( $_GET['debug']['selftest'] ?? null ){
	$selftest->Debug();
};
