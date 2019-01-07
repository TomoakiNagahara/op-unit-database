<?php
/**
 * unit-database:/Database.class.php
 *
 * @creation  2018-04-20
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2018-04-25
 */
namespace OP\UNIT;

/** Database
 *
 * @creation  2018-04-20
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Database implements \IF_DATABASE
{
	/** trait
	 *
	 */
	use \OP_CORE;

	/** Connection configuration.
	 *
	 * @var array
	 */
	private $_config = [];

	/** Stack past queries.
	 *
	 * @var array
	 */
	private $_queries = [];

	/** PHP Data Objects.
	 *
	 * @var \PDO
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
		$this->_SQL = \Unit::Instance('SQL');
	}

	/** Generate Data Source Name.
	 *
	 * @return string
	 */
	private function _DSN()
	{
		//	...
		$dsn = null;

		//	...
		switch( $prod = strtolower($this->_config['prod']) ){
			case 'mysql':
			case 'pgsql':
				//	...
				$host = $this->_config['host'];
				$dsn  = "{$prod}:host={$host}";

				//	...
				if( $port = $this->_config['port'] ?? null ){
					$dsn .= ";port={$port}";
				};

				//	Database
				if( $database = $this->_config['database'] ?? null ){
					$dsn .= ";dbname={$database}";
				}
			break;
			case 'sqlite':
				$dsn = "{$prod}:{$this->_config['path']}";
			break;
			default:
			\Notice::Set("Has not been support this product yet. ($prod)");
		}

		//	...
		return $dsn;
	}

	/** Generate PDO options.
	 *
	 * @return	 array	 $options
	 */
	private function _Options()
	{
		//	...
		$options = [];

		//	...
		switch( strtolower($this->_config['prod']) ){
			case 'mysql':
				//	Character set. (指定字符代码, 指定字符代碼)
				$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES ".($this->_config['charset'] ?? 'utf8');

				//	Multi statement. (多个指令, 多個指令)
				$options[\PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false;

				//	Persistent connect. (持续连接, 持續連接)
				$options[\PDO::ATTR_PERSISTENT] = false;
			break;
		}

		//	...
		return $options;
	}

	/** If is connect.
	 *
	 * @return	 boolean
	 */
	function isConnect()
	{
		return $this->_PDO ? true: false;
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
	 * @see		\IF_DATABASE::Config()
	 * @return	 array		 $config
	 */
	function Config()
	{
		return $this->_config;
	}

	/** Connect database server.
	 *
	 * <pre>
	 * //  MySQL Configuration.
	 * $config = [];
	 * $conifg['prod']     = 'mysql';
	 * $conifg['host']     = 'localhost';
	 * $conifg['port']     = '3306';
	 * $conifg['user']     = 'username';
	 * $conifg['password'] = 'password';
	 * $conifg['charset']  = 'utf8';
	 *
	 * //  SQLite Configuration.
	 * $config = [];
	 * $conifg['prod']     = 'sqlite';
	 * $conifg['path']     = ':memory:';
	 * </pre>
	 *
	 * @param	 array		 $config
	 * @return	 boolean	 $io
	 */
	function Connect($config)
	{
		//	...
		if( isset($this->_config['driver']) and empty($this->_config['prod']) ){
			$this->_config['prod'] = $this->_config['driver'];
			unset($this->_config['driver']);
		};

		//	...
		$this->_config = $config;

		//	...
		switch( $prod = strtolower($config['prod']) ){
			case 'mysql':
				$this->_ConnectMySQL();
				break;
			case 'pgsql':
				$this->_ConnectPGSQL();
				break;
			case 'sqlite':
				$this->_ConnectSQLite();
				break;
			default:
				\Notice::Set("Has not been support this product. ($prod)");
		};

		//	...
		return $this->_PDO ? true: false;
	}

	/** Connect MySQL Database server.
	 *
	 */
	function _ConnectMySQL()
	{
		try {
			//	...
			if(!defined('\PDO::MYSQL_ATTR_INIT_COMMAND') ){
				throw new \Exception("Please install MySQL driver for PHP PDO.");
			}

			//	...
			$user     = $this->_config['user']     ?? null;
			$password = $this->_config['password'] ?? null;

			//	...
			$dsn     = $this->_DSN();
			$options = $this->_Options();

			//	...
			$this->_queries[] = $dsn;
			$this->_PDO = new \PDO($dsn, $user, $password, $options);
		}catch( \PDOException $e ){
			switch( $e->getCode() ){
				case '2002':
					$key = 'pdo_mysql.default_socket';
					$ini = ini_get($key);
					$str = $e->getMessage();
					if( $ini ){
						\Notice::Set("{$str} ({$ini})");
					}else{
						\Notice::Set("Has not been set '{$key}'.");
					};
					break;
				default:
					\Notice::Set($e);
			};
		}catch( \Exception $e ){
			\Notice::Set($e);
		};
	}

	/** Connect MySQL Database server.
	 *
	 */
	function _ConnectPGSQL()
	{
		//	...
		if( isset($this->_config['role']) and empty($this->_config['user']) ){
			$this->_config['user'] = $this->_config['role'];
			unset($this->_config['role']);
		};

		//	...
		try {
			//	...
			if(!defined('\PDO::PGSQL_ATTR_DISABLE_PREPARES') ){
				throw new \Exception("Please install PostgreSQL driver for PHP PDO.");
			}

			//	...
			$user     = $this->_config['user']     ?? null;
			$password = $this->_config['password'] ?? null;

			//	...
			$dsn     = $this->_DSN();
			$options = $this->_Options();

			//	...
			$this->_queries[] = $dsn;
			$this->_PDO = new \PDO($dsn, $user, $password, $options);
		}catch( \PDOException $e ){
			switch( $e->getCode() ){
				default:
					\Notice::Set($e);
			};
		}catch( \Exception $e ){
			\Notice::Set($e);
		};
	}

	/** Connect SQLite Database server.
	 *
	 */
	function _ConnectSQLite()
	{
		try {
			//	...
			$dsn     = $this->_DSN();

			//	...
			$this->_queries[] = $dsn;
			$this->_PDO = new \PDO($dsn);
		}catch( \PDOException $e ){
			switch( $e->getCode() ){
				case 0:
					$module = 'sqlite';
					\Notice::Set("php-{$module} is not installed.");
					include( ConvertPath('asset:/bootstrap/php/content.phtml') );
					break;
				default:
					\Notice::Set($e);
			};
		}catch( \Exception $e ){
			\Notice::Set($e);
		};
	}

	/**
	 *
	 * @return \OP\UNIT\DATABASE\Create
	 */
	function Create()
	{
		//	...
		static $_create;
		//	...
		if(!$_create ){
			include('Create.class.php');
			$_create = new \OP\UNIT\DATABASE\Create();
		};

		//	...
		return $_create;
	}

	/** Set/Get last time used database name.
	 *
	 * @param  string $database
	 * @return string $database
	 */
	function Database(string $database=null)
	{
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

	/** Do SQL.
	 *
	 * @param	 array		 $config
	 * @param	 string		 $function
	 * @return	 mixed		 $result
	 */
	function SQL($config, $function)
	{
		//	...
		if(!$this->_SQL ){
			return false;
		}

		//	...
		$query = $this->_SQL->{$function}($config, $this);

		//	...
		return $this->Query($query, $function);
	}

	/** Count number of record at conditions.
	 *
	 * @see		\IF_DATABASE::Count()
	 * @param	 array		 $config
	 * @return	 integer	 $count
	 */
	function Count($config)
	{
		$count = $this->SQL($config, __FUNCTION__);
		return empty($count) ? 0: (int)$count;
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
	 * @see		\IF_DATABASE::Select()
	 * @param	 array		 $config
	 * @return	 array		 $record
	 */
	function Select($config)
	{
		return $this->SQL($config, __FUNCTION__);
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
	 * @see		\IF_DATABASE::Insert()
	 * @param	 array		 $config
	 * @return	 integer	 $new_id
	 */
	function Insert($config)
	{
		return $this->SQL($config, __FUNCTION__);
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
	 * @see		\IF_DATABASE::Update()
	 * @param	 array		 $config
	 * @return	 integer	 $number
	 */
	function Update($config)
	{
		return $this->SQL($config, __FUNCTION__);
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
	 * @see		\IF_DATABASE::Delete()
	 * @param	 array		 $config
	 * @return	 integer	 $number
	 */
	function Delete($config)
	{
		return $this->SQL($config, __FUNCTION__);
	}

	/** Get database or table or user.
	 *
	 * @param	 array	 $config
	 * @return	 array	 $array
	 */
	function Show($config)
	{
		return $this->Query($this->_SQL->Show($config, $this), 'show');
	}

	/** Get field name of primary key.
	 *
	 * @param	 string	 $database
	 * @param	 string	 $table
	 * @return	 string	 $pkey
	 */
	function PKey($database, $table)
	{
		$database = $this->Quote($database);
		$table    = $this->Quote($table);
		return $this->Query("SHOW INDEX FROM {$database}.{$table}", 'show')['PRIMARY'][1]['Column_name'] ?? null;
	}

	/** Do QQL.
	 *
	 * @see		\IF_DATABASE::Quick()
	 * @param	 string		 $qql
	 * @param	 array		 $options
	 * @return	 array		 $record
	 */
	function Quick($qql, $options=[])
	{
		include_once(__DIR__.'/QQL.class.php');
		return Database\QQL::Execute($qql, $options, $this);
	}

	/** Do Quote by each product.
	 *
	 * @see		\IF_DATABASE::Quote()
	 * @param	 string		$value
	 * @return	 string		$value
	 */
	function Quote($value)
	{
		//	...
		switch( $this->_config['prod'] ){
			case 'mysql':
				$l = '`';
				$r = '`';
				break;
			default:
		}

		//	...
		return $l.trim($value).$r;
	}

	/** Execute SQL statement.
	 *
	 * @see		\IF_DATABASE::Query()
	 * @param	 string		 $query
	 * @param	 string		 $type
	 * @return	 array		 $record
	 */
	function Query($query, $type=null)
	{
		//	...
		if(!$query){
			return $type==='Select' ? []: false;
		}

		//	Check of PDO instantiate.
		if(!$this->_PDO ){
			\Notice::Set("Has not been instantiate PDO.", debug_backtrace(false));
			return $type==='Select' ? []: false;
		}

		//	Remove space.
		$query = trim($query);

		//	Stacking query for developers.
		$this->_queries[] = $query;

		//	Execute SQL statement.
		$statement = $this->_PDO->query($query);

		//	In case of empty.
		if(!$statement ){
			include_once(__DIR__.'/ErrorInfo.class.php');
			DATABASE\ErrorInfo::Set( $this->_PDO->errorInfo(), debug_backtrace(false) );
			return [];
		}

		//	Check of SQL type.
		if(!$type){
			$type = substr($query, 0, strpos($query, ' '));
		}

		//	Generate result value by type.
		switch( strtolower($type) ){
			case 'select':
				$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
				if( strpos($query.' ', ' LIMIT 1 ') and $result ){
					$result = $result[0];
				}
				break;

			case 'count':
				$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
				$result = $result[0]['COUNT(*)'];
				break;

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
				$result = true;
				break;

			case 'password':
				$result = array_shift($statement->fetchAll(\PDO::FETCH_ASSOC)[0]);
				break;

			case 'not':
				break;

			default:
				\Notice::Set("Has not been support this type. ($type)", debug_backtrace(false));
		}

		//	...
		return isset($result) ? $result: [];
	}

	/** Get past stacked queries.
	 *
	 * @see		\IF_DATABASE::Queries()
	 * @return	 array		 $queries
	 */
	function Queries()
	{
		return $this->_queries;
	}

	/** Display debug information.
	 *
	 * @see \IF_DATABASE::Debug()
	 */
	function Debug()
	{
		D($this->_queries);
	}
}
