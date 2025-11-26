<?php
/**	op-unit-database:/Alter.class.php
 *
 * @creation  2019-01-18
 * @version   1.0
 * @package   op-unit-database
 * @author    Tomoaki Nagahara
 * @copyright Tomoaki Nagahara All rights reserved.
 */

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
	function __construct(\OP\UNIT\Database $DB)
	{
		$this->_DB = $DB;
	}
}
