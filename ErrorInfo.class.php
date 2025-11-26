<?php
/**	op-unit-database:/ErrorInfo.class.php
 *
 * @creation  2018-05-08
 * @license   Apache-2.0
 * @package   op-unit-database-ci
 * @copyright (C) 2025 Tomoaki Nagahara
 */

/**	Namespace
 *
 * @creation  2018-05-08
 */
namespace OP\UNIT\DATABASE;

/**	Use
 *
 * @creation  2019-03-04
 */
use OP\OP_CORE;
use OP\OP_CI;
use OP\Notice;

/** Database
 *
 * @creation  2018-05-08
 */
class ErrorInfo
{
	/** trait
	 *
	 */
	use OP_CORE;
	use OP_CI;

	/** Set PDO error information.
	 *
	 * @param array $errorinfo
	 * @param array $backtrace
	 */
	static function Set($errorinfo, $backtrace)
	{
		$state = $errorinfo[0];
		$errno = $errorinfo[1];
		$error = $errorinfo[2];
		Notice::Set("[$state($errno)] $error", $backtrace);
	}
}
