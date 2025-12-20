<?php
/**	op-unit-database:/Alter.class.php
 *
 * @created   2019-01-18
 * @license   Apache-2.0
 * @package   op-unit-database-ci
 * @copyright (C) 2025 Tomoaki Nagahara
 */

/**	Declare strict type
 *
 */
declare(strict_types=1);

/**	Namespace
 *
 */
namespace OP\UNIT\DATABASE;

/**	Use
 *
 * @creation  2019-03-04
 */
use OP\OP_CORE;
use OP\OP_CI;
use OP\IF_DATABASE;

/**	Alter
 *
 * @creation  2019-01-18
 */
class Alter
{
	/**	trait
	 *
	 */
	use OP_CORE;
	use OP_CI;

	/**	Database object.
	 *
	 * @var \OP\UNIT\Database
	 */
	private $_DB;

	/**	Construct
	 *
	 * @param \OP\UNIT\Database $DB
	 */
	function __construct( ?IF_DATABASE $DB = null )
	{
		$this->_DB = $DB;
	}
}
