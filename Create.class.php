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

	/** Database object.
	 *
	 * @var \IF_DATABASE
	 */
	private $_DB;

	/** Construct
	 *
	 * @param \IF_DATABASE $DB
	 */
	function __construct($DB)
	{
		$this->_DB = $DB;
	}

	/** Create user.
	 *
	 * @param  array       $config
	 */
	function User($config)
	{
		//	...
		$sql = \OP\UNIT\SQL\User::Create($config, $this->_DB);

		//	...
		$result = $this->_DB->Query($sql, 'create');

		//	...
		return empty($result) ? false: true;
	}

	/** Create database.
	 *
	 * @param	 array	 $config
	 */
	function Database($config)
	{
		//	...
		if( $this->_DB->Config()['prod'] === 'sqlite' ){
			require_once(__DIR__.'/SQL_LITE.class.php');
			return SQLITE::Create($config);
		};

		//	...
		$sql = \OP\UNIT\SQL\Database::Create($config, $this->_DB);

		//	...
		$result = $this->_DB->Query($sql, 'create');

		//	...
		return empty($result) ? false: true;
	}

	/** Create table.
	 *
	 * @param	 array	 $config
	 * @return	 boolean
	 */
	function Table($config)
	{
		//	...
		$sql = \OP\UNIT\SQL\Table::Create($config, $this->_DB);

		//	...
		if( $result = $this->_DB->Query($sql, 'create') ){
			//	...
			if( $this->_DB->Config()['prod'] === 'sqlite' ){
				$result = $this->_SQLite($config);
			};
		};

		//	...
		return empty($result) ? false: true;
	}

	/** Search field config.
	 *
	 * @param	 array $config
	 * @return	 array
	 */
	private function _Field(array $config)
	{
		//	...
		foreach( ['field','fields','column','columns'] as $key ){
			if( isset($config[$key]) ){
				return $config[$key];
			};
		};
	}

	/** Generate trigger.
	 *
	 * @param	 array	 $config
	 * @return	 boolean
	 */
	private function _SQLite(array $config):bool
	{
		//	...
		$statement = '';

		//	...
		$table = $this->_DB->Quote($config['table']);

		//	...
		foreach( $this->_Field($config) as $name => $column ){
			//	...
			$field = $column['name'] ?? $column['field'] ?? $name;
			$field = $this->_DB->Quote($field);

			//	...
			if( ($column['ai'] ?? null) or ($column['pkey'] ?? null) ){
				$pkey = $field;
			};

			//	...
			if( $column['timestamp'] ?? null ){
				//	...
				$statement .= "  UPDATE {$table} SET {$field} = DATETIME(\"now\",\"localtime\") WHERE {$pkey} = old.{$pkey};\n";
			};
		};

		//	...
		$sql  = "CREATE TRIGGER ON_TIMESTAMP_{$table} AFTER UPDATE on {$table} \n"; // FOR EACH ROW <- for mysql
		$sql .= "BEGIN \n";
		$sql .= $statement;
		$sql .= "END \n";

		//	...
		$pdo = $this->_DB->PDO();
		$pdo->query('DELIMITER $$');
		$pdo->query($sql);
		$pdo->query('DELIMITER ;');

		//	...
		return true;
	}
}
