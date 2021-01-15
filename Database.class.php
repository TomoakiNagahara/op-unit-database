<?php
/**
 * unit-database:/Database.class.php
 *
 * v1.0 Single file
 * v2.0 Class
 * v3.0 onepiece-framework
 * v4.0 unit Gen1 2017
 * v4.1 unit Gen2 2018
 * v4.2 unit Gen2 2019
 *
 * @creation  2018-04-20
 * @version   4.2
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2018-04-25
 */
namespace OP\UNIT;

/** Used class
 *
 * @created   2019-03-04
 */
use PDO;
use Exception;
use OP\OP_CORE;
use OP\OP_UNIT;
use OP\IF_UNIT;
use OP\IF_DATABASE;
use OP\Unit;

/** Database
 *
 * @creation  2018-04-20
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Database implements IF_DATABASE, IF_UNIT
{
	/** trait
	 *
	 */
	use OP_CORE, OP_UNIT;

	/** Connection configuration.
	 *
	 * @var array
	 */
	private $_config = [];

	/** Stacking query history. for debug.
	 *
	 * @var array
	 */
	static $_queries = [];

	/** PHP Data Objects.
	 *
	 * @var PDO
	 */
	private $_PDO;

	/** SQL generator.
	 *
	 * @var \OP\UNIT\SQL
	 */
	private $_SQL;

	/** Construct
	 *
	 */
	function __construct()
	{
		//	Not singleton object.
		$this->_SQL = Unit::Instantiate('SQL');
		$this->_SQL->DB($this);
	}

	/** Destruct
	 *
	 * @created   2020-02-10
	 */
	function __destruct()
	{
		//	Check is admin.
		if(!\OP\Env::isAdmin() ){
			return;
		};

		//	Check if empty debug.
		if( empty($_GET['debug']) ){
			return;
		};

		//	...

		//	Check by Debug::isDebug().
		if( \OP\Debug::isDebug( get_class($this) ) ){
			$this->Debug();
		};
	}

	/** Return instantiated PDO instance. (So-called singleton)
	 *
	 * @return \PDO
	 */
	function PDO()
	{
		return $this->_PDO;
	}

	/** Return connection configuration.
	 *
	 * @see		 IF_DATABASE::Config()
	 * @return	 array		 $config
	 */
	function Config()
	{
		return $this->_config;
	}

	/** If is connect.
	 *
	 * @return	 boolean
	 */
	function isConnect()
	{
		return $this->_PDO ? true: false;
	}

	/** Connect database server.
	 *
	 * @param	 array		 $config
	 * @return	 boolean	 $io
	 */
	function Connect($config)
	{
		//	...
		if( empty($config) ){
			throw new \Exception("Database connect config is empty.");
		};

		//	...
		if( empty($config['prod']) and $config['scheme'] ?? null ){
			$config['prod'] = $config['scheme'];
		};

		//	...
		$config['prod'] = strtolower($config['prod']);

		//	...
		if( empty($config['database']) ){
			//	...
			if( isset($config['name']) ){
				//	...
				$config['database'] = $config['name'];
			}
		}

		//	...
		switch( $prod = $config['prod'] ){
			case 'mysql':
				require_once(__DIR__.'/SQL_MY.class.php');
				$this->_config    = DATABASE\MYSQL::Config ($config);
				$this->_PDO       = DATABASE\MYSQL::Connect($config);
				self::$_queries[] = DATABASE\MYSQL::DSN    ($config);
				break;

			case 'pgsql':
				require_once(__DIR__.'/SQL_PG.class.php');
				$this->_config = DATABASE\PGSQL::Config ($config);
				$this->_PDO    = DATABASE\PGSQL::Connect($config);
				break;

			case 'sqlite':
				require_once(__DIR__.'/SQL_LITE.class.php');
				$this->_config = DATABASE\SQLITE::Config ($config);
				$this->_PDO    = DATABASE\SQLITE::Connect($config);
				break;

			default:
				if( empty($prod) ){
					$prod = 'empty';
				};
				throw new \Exception("Has not been support this product. ($prod)");
		};

		//	...
		return $this->_PDO ? true: false;
	}

	/** Set/Get last time used database name.
	 *
	 * @param  string $database
	 * @return string $database
	 */
	function Database(string $database=null)
	{
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

	/** Create
	 *
	 */
	function Create()
	{

	}

	/** Change
	 *
	 */
	function Change()
	{

	}

	/** Drop
	 *
	 */
	function Drop()
	{

	}

	/** Count number of record at conditions.
	 *
	 * @see		 IF_DATABASE::Count()
	 * @param	 array		 $config
	 * @return	 integer	 $count
	 */
	function Count($config)
	{
		//	...
		if( is_string($config) ){
			Unit::Load('QQL');
			$config = \OP\UNIT\QQL::Parse($config);
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

	/** Select record at conditions.
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
	function Select($config)
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

	/** Insert new record.
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
	function Insert($config)
	{
		//	...
		$sql = $this->_SQL->DML($this)->Insert($config);

		//	...
		return $this->SQL($sql, 'insert');
	}

	/** Update record at conditions.
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
	function Update($config)
	{
		//	...
		$sql = $this->_SQL->DML($this)->Update($config);

		//	...
		return $this->SQL($sql, 'update');
	}

	/** Delete record at conditions.
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
	function Delete($config)
	{
		//	...
		$sql = $this->_SQL->DML($this)->Delete($config);

		//	...
		return $this->SQL($sql, 'delete');
	}

	/** Begin transactoin.
	 *
	 * @see		 IF_DATABASE::Transaction()
	 * @see		\PDO::beginTransaction()
	 * @return	 bool
	 */
	function Transaction()
	{
		$this->_debug['SQL'][] = 'Transaction Begin';
		return $this->_PDO->beginTransaction();
	}

	/** Commit transactoin.
	 *
	 * @see		 IF_DATABASE::Commit()
	 * @see		\PDO::commit()
	 * @return	 bool
	 */
	function Commit()
	{
		$this->_debug['SQL'][] = 'Transaction Commit';
		return $this->_PDO->commit();
	}

	/** Rollback transactoin.
	 *
	 * @see		 IF_DATABASE::Rollback()
	 * @see		\PDO::rollBack()
	 * @return	 bool
	 */
	function Rollback()
	{
		$this->_debug['SQL'][] = 'Transaction Rollback';
		return $this->_PDO->rollBack();
	}

	/** Do Quote by each product.
	 *
	 * @see		 IF_DATABASE::Quote()
	 * @param	 string		$value
	 * @return	 string		$value
	 */
	function Quote($value)
	{
		//	...
		switch( $prod = $this->_config['prod'] ){
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
				throw new Exception("Has not been support this product. ($prod)");
		}

		//	...
		return $l.trim($value).$r;
	}

	/** Quick Query Language.
	 *
	 * @param  string $qql
	 * @param  array  $options
	 * @return array
	 */
	function Quick(string $qql, array $options=[])
	{
		return $this->QQL($qql, $options);
	}

	/** SQL Query.
	 *
	 * @param  string $query
	 * @param  string $type
	 * @return array
	 */
	function Query(string $query='', string $type='')
	{
		//	...
		return $this->SQL($query, $type);

		/*
		//	...
		if( $query ){
			return $this->SQL($query, $type);
		}else{
			return array_shift(self::$_queries);
		};
		*/
	}

	/** Do QQL.
	 *
	 * @see		 IF_DATABASE::QQL()
	 * @param	 string		 $qql
	 * @param	 array		 $options
	 * @return	 array		 $record
	 */
	function QQL(string $qql, array $options=[])
	{
		include_once(__DIR__.'/QQL.class.php');
		return DATABASE\QQL::Execute($qql, $options, $this);
	}

	/** SQL is execute.
	 *
	 * @see		 IF_DATABASE::SQL()
	 * @param	 string		 $query
	 * @param	 string		 $type
	 * @return	 array		 $record
	 */
	function SQL(string $query, string $type='')
	{
		//	...
		$type = strtolower($type);

		//	...
		if(!$query){
			return ($type === 'select') ? []: false;
		}

		//	Check of PDO instantiate.
		if(!$this->_PDO ){
			throw new Exception("Has not been instantiate PDO.");
		};

		//	Remove space.
		$query = trim($query);

		//	Stacking query for developers.
		self::$_queries[] = $query;

		//	Execute SQL statement.
		$statement = $this->_PDO->query($query);

		//	In case of empty.
		if(!$statement ){
			include_once(__DIR__.'/ErrorInfo.class.php');
			DATABASE\ErrorInfo::Set( $this->_PDO->errorInfo(), debug_backtrace(false) );
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
				if(!$result = $this->_PDO->lastInsertId(/* $name is necessary at PGSQL */) ){
					$result = true;
				}
				break;

			case 'update':
			case 'delete':
				$result = $statement->rowCount();
				break;

			case 'show':
				include_once(__DIR__.'/Show.class.php');
				$result = Database\Show::Get( $statement->fetchAll(\PDO::FETCH_ASSOC), $query );
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
				throw new Exception("Has not been support this type. ($type)");
		}

		//	...
		return isset($result) ? $result: [];
	}

	/** Get SQL Server version.
	 *
	 * @return string
	 */
	function Version()
	{
		return $this->SQL('SELECT VERSION()')[0];
	}

	/** Debug
	 *
	 * @created   2020-02-10
	 */
	function Debug()
	{
		D( self::$_queries );
	}
}
