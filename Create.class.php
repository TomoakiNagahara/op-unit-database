<?php
/**
 * unit-database:/Create.class.php
 *
 * @creation  2018-12-19
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2018-12-19
 */
namespace OP\UNIT\DATABASE;

/** Database
 *
 * @creation  2018-12-19
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Create
{
	/** trait
	 *
	 */
	use \OP_CORE;

	function SQL()
	{

	}

	/**
	 *
	 * @param  array       $config
	 * @param \IF_DATABASE $DB
	 */
	function User($config, $DB)
	{
		//	...
		$sql = \OP\UNIT\SQL\User::Create($config, $DB);

		//	...
		$result = $DB->Query($sql, 'create');


		D($sql, $result);
	}

	function Database()
	{

	}

	function Table()
	{

	}

	function Grant()
	{

	}
}
