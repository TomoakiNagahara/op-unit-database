<?php
/**	op-unit-database:/Database.class.php
 *
 * v1.0 Single file
 * v2.0 Class
 * v3.0 onepiece-framework
 * v4.0 unit Gen1 2017
 * v4.1 unit Gen2 2018
 * v4.2 unit Gen3 2019
 *
 * @created   2018-04-20
 * @license   Apache-2.0
 * @version   4.2
 * @package   op-unit-database
 * @copyright (C) 2007 Tomoaki Nagahara
 */

/**	Declare strict type
 *
 */
declare(strict_types=1);

/**	Namespace
 *
 * @created   2018-04-25
 */
namespace OP\UNIT;

/**	Use
 *
 * @created   2019-03-04
 */
use PDO;
use Exception;
use OP\OP_CORE;
use OP\OP_UNIT;
use OP\OP_CI;
use OP\IF_UNIT;
use OP\IF_DATABASE;
use OP\Unit;

/**	Database
 *
 * @created   2018-04-20
 */
class Database implements IF_DATABASE, IF_UNIT
{
	/**	trait
	 *
	 */
	use OP_CORE, OP_UNIT;
	use OP_CI;

	/**	Connection configuration.
	 *
	 * @var array
	 */
	private $_config = [];

	/**	Stacking query history. for debug.
	 *
	 * @var array
	 */
	static $_queries = [];

	/**	SQL generator.
	 *
	 * @var \OP\UNIT\SQL
	 */
	private $_SQL;

	/**	Construct
	 *
	 */
	function __construct()
	{
		//	Not singleton object.
		$this->_SQL = Unit::Instantiate('SQL');
		$this->_SQL->DB($this);
	}

	/**	Destruct
	 *
	 * @created   2020-02-10
	 */
	function __destruct()
	{
		//	Check is admin.
		if(!OP()->isAdmin() ){
			return;
		};
	}

	/**	Store PDO object access only internal.
	 *
	 * Does not access external.
	 * It is isolated from the outside world to prevent unauthorized updates.
	 *
	 */
	static private function _PDO( string $label, ?\PDO $pdo=null ) : \PDO | bool
	{
		//	...
		static $_PDO = [];

		//	...
		if( $pdo ){
			//	...
			if( isset($_PDO[$label]) ){
				OP()->Error("A PDO with this label name is already registered: {$label}");
				return false;
			}else{
				$_PDO[$label] = $pdo;
				return true;
			}
		}

		//	...
		if(!isset($_PDO[$label]) ){
			OP()->Error("No PDO with this label name has been set: {$label}");
			return false;
		}

		//	...
		if( empty($_PDO[$label]) ){
			OP()->Error("The PDO for this label name is empty: {$label}");
			return false;
		}

		//	...
		return $_PDO[$label] ?? false;
	}

	/**	It is possible to obtain a PDO object externally.
	 *
	 * @return \PDO
	 */
	static public function PDO( string $label='default' ) : \PDO | bool | null
	{
		return self::_PDO($label);
	}

	/**	Return connection configuration.
	 *
	 * @see		 IF_DATABASE::Config()
	 * @return	 array		 $config
	 */
	function Config( string $label='default' ) : array | null
	{
		return $this->_config[$label] ?? null;
	}

	/**	If is connect.
	 *
	 * @return	 boolean
	 */
	function isConnect( string $label='default' ) : bool
	{
		return $this->_PDO($label) ? true: false;
	}

	/**	Connect database server.
	 *
	 * @param	 array		 $config
	 * @return	 boolean	 $io
	 */
	function Connect( array $config, string $label='default' ) : bool
	{
		//	...
		switch( $driver = strtolower($config['driver'] ?? '(empty)') ){
			case 'mysql':
				require_once(__DIR__.'/SQL_MY.class.php');
				$_config    = DATABASE\SQL_MY::Config ($config);
				$_PDO       = DATABASE\SQL_MY::Connect($config);
				self::$_queries[] = DATABASE\SQL_MY::DSN    ($config);
				break;

			case 'pgsql':
				require_once(__DIR__.'/SQL_PG.class.php');
				$_config = DATABASE\SQL_PG::Config ($config);
				$_PDO    = DATABASE\SQL_PG::Connect($config);
				break;

			case 'sqlite':
				require_once(__DIR__.'/SQL_LITE.class.php');
				$_config = DATABASE\SQL_LITE::Config ($config);
				$_PDO    = DATABASE\SQL_LITE::Connect($config);
				break;

			default:
				OP()->Error("This driver is not support: $driver");
				return false;
		};

		//	...
		$this->_config[$label] = $_config;
		//	...
		$this->_PDO( $label, $_PDO );

		//	...
		return $_PDO ? true: false;
	}

	/**	Set/Get last time used database name.
	 *
	 * @deprecated 2025-12-01
	 * @param  string $database
	 * @return string $database
	 */
	function Database(string $database=null)
	{
		/*
		//	...
		OP()->Error('This function has been discontinued. Please manage connected databases by label names.');
		*/

		//	...
		if( $database ){
			//	...
			$this->_config['database'] = $database;

			//	...
			$database = $this->Quote($database);

			//	...
			$this->Query("use $database ", 'not');
		}

		//	...
		return $this->_config['database'];
	}

	/**	Create
	 *
	 */
	function Create()
	{

	}

	/**	Change
	 *
	 */
	function Change()
	{

	}

	/**	Drop
	 *
	 */
	function Drop()
	{

	}

	/**	Count number of record at conditions.
	 *
	 * @see		 IF_DATABASE::Count()
	 * @param	 array		 $config
	 * @return	 integer	 $count
	 */
	function Count( array $config, string $label = 'default' ) : int
	{
		//	...
		if( is_string($config) ){
			Unit::Load('QQL');
			$config = DATABASE\QQL::Parse($config);
		};

		//	...
		$config['field']  = "COUNT(*)";
		$config['limit']  = 1;

		//	...
		$sql = $this->_SQL->DML($this)->Select($config);

		//	...
		$result = $this->SQL($sql, 'select');

		//	...
		return (int)$result;
	}

	/**	Select record at conditions.
	 *
	 * <pre>
	 * //	Configuration.
	 * $config = [];
	 * $config['table'] = 't_table';
	 * $config['limit'] = 1;
	 * $config['where']['value'] = $value;
	 *
	 * //	Execute.
	 * $record = $db->Select($config);
	 * </pre>
	 *
	 * @see		 IF_DATABASE::Select()
	 * @param	 array		 $config
	 * @return	 array		 $record
	 */
	function Select( array $config, string $label = 'default' )
	{
		//	Cache feature.
		if( isset($config['cache']) ){
			//	...
			$cache = $config['cache'];

			//	...
			unset($config['cache']);

			//	...
			if(!function_exists('apcu_exists') ){
				throw new \Exception("Does not installed apcu.");
			}

			//	...
			$database = $config['database'] ?? $this->_config['database'];

			//	...
			$hash = md5( $database . ', ' . serialize($config) );

			//	...
			if( apcu_exists($hash) ){
				return apcu_fetch($hash);
			}
		}

		//	...
		$sql = $this->_SQL->DML($this)->Select($config);

		//	...
		$result = $this->SQL($sql, 'select');

		//	...
		if( isset($cache) and !empty($result) ){
			apcu_add($hash, $result, $cache);
		}

		//	...
		return $result;
	}

	/**	Insert new record.
	 *
	 * <pre>
	 * //	Configuration.
	 * $config = [];
	 * $config['table'] = 't_table';
	 * $config['set']['value'] = $value;
	 *
	 * //	Execute.
	 * $new_id = $db->Insert($config);
	 * </pre>
	 *
	 * @see		 IF_DATABASE::Insert()
	 * @param	 array		 $config
	 * @return	 integer	 $new_id
	 */
	function Insert( array $config, string $label = 'default' )
	{
		//	...
		if(!$sql = $this->_SQL->DML($this)->Insert($config) ){
			return;
		}

		//	...
		return $this->SQL($sql, 'insert');
	}

	/**	Update record at conditions.
	 *
	 * <pre>
	 * //	Configuration.
	 * $config = [];
	 * $config['table'] = 't_table';
	 * $config['limit'] = 1;
	 * $config['where']['id']  = $id;
	 * $config['set']['value'] = $value;
	 *
	 * //	Execute.
	 * $record = $db->Update($config);
	 * </pre>
	 *
	 * @see		 IF_DATABASE::Update()
	 * @param	 array		 $config
	 * @return	 integer	 $number
	 */
	function Update( array $config, string $label = 'default' )
	{
		//	...
		if(!$sql = $this->_SQL->DML($this)->Update($config) ){
			return;
		}

		//	...
		return $this->SQL($sql, 'update');
	}

	/**	Delete record at conditions.
	 *
	 * <pre>
	 * //	Configuration.
	 * $config = [];
	 * $config['table'] = 't_table';
	 * $config['limit'] = 1;
	 * $config['where']['id']  = $id;
	 *
	 * //	Execute.
	 * $record = $db->Delete($config);
	 * </pre>
	 *
	 * @see		 IF_DATABASE::Delete()
	 * @param	 array		 $config
	 * @return	 integer	 $number
	 */
	function Delete( array $config, string $label = 'default' )
	{
		//	...
		if(!$sql = $this->_SQL->DML($this)->Delete($config) ){
			return;
		}

		//	...
		return $this->SQL($sql, 'delete');
	}

	/**	Begin transaction.
	 *
	 * @see		 IF_DATABASE::Transaction()
	 * @see		\PDO::beginTransaction()
	 * @return	 bool
	 */
	function Transaction( string $label = 'default' ) : bool
	{
		//	...
		if(!$_PDO = self::_PDO($label) ){
			return false;
		}

		//	...
		return $_PDO->beginTransaction();
	}

	/**	Commit transaction.
	 *
	 * @see		 IF_DATABASE::Commit()
	 * @see		\PDO::commit()
	 * @return	 bool
	 */
	function Commit( string $label = 'default' ) : bool
	{
		//	...
		if(!$_PDO = self::_PDO($label) ){
			return false;
		}

		//	...
		return $_PDO->commit();
	}

	/**	Rollback transaction.
	 *
	 * @see		 IF_DATABASE::Rollback()
	 * @see		\PDO::rollBack()
	 * @return	 bool
	 */
	function Rollback( string $label = 'default' ) : bool
	{
		//	...
		if(!$_PDO = self::_PDO($label) ){
			return false;
		}

		//	...
		return $_PDO->rollBack();
	}

	/**	Quote the field name.
	 *
	 * {@inheritDoc}
	 * @see \OP\IF_DATABASE::Quote()
	 */
	function Quote( string $value, string $label='default' ) : string | false
	{
		//	...
		if(!$pdo = $this->_PDO($label) ){
			return false;
		}

		//	...
		$value = trim($value);

		/* @var $match array */
		if( preg_match('/([^_0-9A-Za-z-])/', $value, $match) ){
			throw new Exception("The string contains invalid characters: {$value} --> {$match[1]}");
		}

		//	...
		switch( $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) ){
			case 'mysql':
				$l = '`';
				$r = '`';
				break;

			case 'pgsql':
			case 'sqlite':
				$l = '"';
				$r = '"';
				break;

			default:
				throw new Exception("This driver is not yet supported: {$driver}");
		}

		//	...
		$value = htmlentities($value, ENT_QUOTES, 'utf-8', true);

		//	...
		return $l.trim($value).$r;
	}

	/**	Quick Query Language.
	 *
	 * @param  string $qql
	 * @param  array  $options
	 * @return array
	 */
	function Quick(string $qql, array $options=[])
	{
		return $this->QQL($qql, $options);
	}

	/**	SQL Query.
	 *
	 * @param  string $query
	 * @param  string $type
	 */
	function Query( string $query, string $type='', string $label='default' )
	{
		//	...
		return $this->SQL( $query, $type, $label );

		/*
		//	...
		if( $query ){
			return $this->SQL($query, $type);
		}else{
			return array_shift(self::$_queries);
		};
		*/
	}

	/**	Do QQL.
	 *
	 * @see		 IF_DATABASE::QQL()
	 * @param	 string		 $qql
	 * @param	 array		 $options
	 * @return	 array		 $record
	 */
	function QQL(string $qql, string|array $option='', string $label='default' )
	{
		include_once(__DIR__.'/QQL.class.php');
		return DATABASE\QQL::Execute($qql, $option, $label);
	}

	/**	SQL is execute.
	 *
	 * @see		 IF_DATABASE::SQL()
	 * @param	 string		 $query
	 * @param	 string		 $type
	 * @return	 array		 $record
	 */
	function SQL( string $query, string $type='', string $label='default' )
	{
		//	...
		$_PDO = self::_PDO($label);

		//	...
		$type = strtolower($type);

		//	...
		if(!$query){
			return ($type === 'select') ? []: false;
		}

		//	Check of PDO instantiate.
		if(!$this->_PDO($label) ){
			throw new Exception("PDO is empty: label={$label}");
		};

		//	Remove space.
		$query = trim($query);

		//	Stacking query for developers.
		self::$_queries[] = $query;

		//	Execute SQL statement.
		$statement = $_PDO->query($query);

		//	In case of empty.
		if(!$statement ){
			include_once(__DIR__.'/ErrorInfo.class.php');
			DATABASE\ErrorInfo::Set( $_PDO->errorInfo(), debug_backtrace(false) );
			return ($type === 'select') ? []: false;
		}

		//	Check of SQL type.
		if(!$type){
			$type = strtolower(substr($query, 0, strpos($query, ' ')));
		}

		//	Generate result value by type.
		switch( $type ){
			case 'select':
				//	...
				$result = $statement->fetchAll(\PDO::FETCH_ASSOC);

				//	Count fields.
				if( isset($result[0]) and count($result[0]) === 1 ){
					$temp = [];
					foreach( $result as $ar ){
						foreach( $ar as  $v ){
							$temp[] = $v;
						}
					}
					$result = $temp;
				}

				//	...
				if( strpos($query.' ', ' LIMIT 1 ') and $result ){
					/* For QQL
					if( count($result[0]) === 1 ){
						foreach( $result[0] as $result ){
							//	...
						};
					}
					*/
					$result = $result[0];
				}

				//	...
				break;
			/*
			case 'count':
				$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
				$result = $result[0]['COUNT(*)'] ?? null;
				break;
			*/
			case 'insert':
				if(!$result = $_PDO->lastInsertId(/* $name is necessary at PGSQL */) ){
					$result = true;
				}
				break;

			case 'update':
			case 'delete':
				$result = $statement->rowCount();
				break;

			case 'show':
				include_once(__DIR__.'/Show.class.php');
				$result = DATABASE\Show::Get( $statement->fetchAll(\PDO::FETCH_ASSOC), $query );
				break;

			case 'set':
			case 'alter':
			case 'grant':
			case 'create':
			case 'drop':
			case 'pragma':
			case 'trigger':
				$result = true;
				break;

			case 'password':
				$result = array_shift($statement->fetchAll(\PDO::FETCH_ASSOC)[0]);
				break;

			case 'not':
				break;

			default:
				throw new Exception("This type is has not been support yet: $type");
		}

		//	...
		return isset($result) ? $result: [];
	}

	/**	Get SQL Server version.
	 *
	 * @return string
	 */
	function Version()
	{
		return $this->SQL('SELECT VERSION()')[0];
	}

	/**	Debug
	 *
	 * @created   2020-02-10
	 */
	function Debug()
	{
		//	...
		if( OP()->isCI() ){
			return;
		}

		//	...
		D( self::$_queries );
	}
}
