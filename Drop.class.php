<?php
/**	op-unit-database:/Drop.class.php
 *
 * @creation  2019-01-07
 * @license   Apache-2.0
 * @package   op-unit-database-ci
 * @copyright (C) 2025 Tomoaki Nagahara
 */

/**	Namespace
 *
 * @creation  2019-01-07
 */
namespace OP\UNIT\DATABASE;

/**	Use
 *
 * @creation  2019-03-04
 */
use OP\OP_CORE;
use OP\OP_CI;
use OP\IF_DATABASE;

/** Drop
 *
 * @creation  2019-01-07
 */
class Drop
{
	/** trait
	 *
	 */
	use OP_CORE;
	use OP_CI;

	/** Database object.
	 *
	 * @var IF_DATABASE
	 */
	private $_DB;

	/** Construct
	 *
	 * @param IF_DATABASE $DB
	 */
	function __construct( IF_DATABASE $DB=null )
	{
		$this->_DB = $DB;
	}

	/** Drop user
	 *
	 * @param  array       $config
	 */
	function User($config)
	{
		//	...
		if( empty($this->_DB) ){
			return;
		}

		//	...
		$sql = '\OP\UNIT\SQL\User'::Drop($config, $this->_DB);

		//	...
		$result = $this->_DB->Query($sql, 'drop');

		//	...
		return empty($result) ? false: true;
	}

	/** Drop database.
	 *
	 * @param  array       $config
	 */
	function Database($config)
	{
		//	...
		if( empty($this->_DB) ){
			return;
		}

		//	...
		$sql = '\OP\UNIT\SQL\Database'::Drop($config, $this->_DB);

		//	...
		$result = $this->_DB->Query($sql, 'drop');

		//	...
		return empty($result) ? false: true;
	}

	/** Drop table.
	 *
	 * @param  array       $config
	 */
	function Table($config)
	{
		//	...
		if( empty($this->_DB) ){
			return;
		}

		//	...
		$sql = '\OP\UNIT\SQL\Table'::Drop($config, $this->_DB);

		//	...
		$result = $this->_DB->Query($sql, 'drop');


		D($sql, $result);

		//	...
		return empty($result) ? false: true;
	}
}
