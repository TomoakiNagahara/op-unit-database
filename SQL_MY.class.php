<?php
/**
 * unit-database:/SQL_MY.class.php
 *
 * @creation  2019-01-07
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-01-07
 */
namespace OP\UNIT\DATABASE;

/** Used class
 *
 * @creation  2019-03-04
 */
use Exception;
use OP\OP_CORE;
use OP\Notice;
use function OP\ConvertPath;

/** MYSQL
 *
 * @creation  2019-01-07
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class MYSQL
{
	/** trait
	 *
	 */
	use OP_CORE;

	/** Config
	 *
	 * @param	 array		 $config
	 * @throws	 Exception	 $e
	 * @return	 array		 $config
	 */
	static function Config(array $config)
	{
		//	...
		if(!defined('\PDO::MYSQL_ATTR_INIT_COMMAND') ){
			$module = 'mysql';
			include( ConvertPath('asset:/bootstrap/php/content.phtml') );
			throw new Exception("php-{$module} is not installed.");
		};

		//	...
		if( empty($config['charset']) ){
			$config['charset'] = 'utf8';
		};

		//	...
		if( $config['host'] === 'localhost' and !empty($config['port']) ){
		//	D("If host is localhost, Use socket connect. Not use TCP/IP Port. (port={$config['port']})");
		}

		//	...
		return $config;
	}

	/** Data Source Name
	 *
	 * @param	 array		 $config
	 * @throws	 Exception	 $e
	 * @return	 string		 $dsn
	 */
	static function DSN(array $config)
	{
		//	...
		$prod = $config['prod'];

		/** Connect to an ODBC database using driver invocation
		 *
		 * @see http://php.net/manual/en/pdo.construct.php
		 */
		if( $uri = $config['uri'] ?? null ){
			/*
			if(!file_exists($uri) ){
				throw new Exception("File has not been exists. ($uri)");
			};
			*/
			return "uri:file://{$uri}";
		};

		//	...
		if(!$host = $config['host'] ?? null ){
			throw new Exception("Has not been set host name.");
		};

		//	port
		$port = $config['port'] ?? 3306;

		//	Data Source Name
		$dsn = "{$prod}:host={$host};port={$port}";

		//	Database
		if( $database = $config['database'] ?? null ){
			$dsn .= ";dbname={$database}";
		}

		//	...
		return $dsn;
	}

	/** Option
	 *
	 * @param	 array		 $config
	 * @throws	 Exception	 $e
	 * @return	 array		 $option
	 */
	static function Option(array $config)
	{
		//	...
		$charset = empty($config['charset']) ? 'utf8': $config['charset'];

		//	...
		$option = [];

		//	Character set. (指定字符代码, 指定字符代碼)
		$option[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$charset}";

		//	Multi statement. (多个指令, 多個指令)
		$option[\PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false;

		//	Persistent connect. (持续连接, 持續連接)
		$option[\PDO::ATTR_PERSISTENT] = false;

		//	...
		return $option;
	}

	/** Connect
	 *
	 * @param	 array		 $config
	 * @throws	 Exception	 $e
	 * @return	\PDO		 $pdo
	 */
	static function Connect($config)
	{
		//	...
		try{
			//	...
			if( empty($config['user']) or empty($config['password']) ){
				D($config);
				throw new \Exception("Empty user or password.");
			}

			//	...
			$dsn      = self::DSN($config);
			$option   = self::Option($config);
			$user     = $config['user'];
			$password = $config['password'];
		//	$database = $config['database'] ?? null;

			//	...
			return new \PDO($dsn, $user, $password, $option);

		}catch( \PDOException $e ){
			require_once(__DIR__.'/SQL_PHP_PDO_Error.class.php');
			SQL_PHP_PDO_Error::Auto($config, $e);
		}catch( \Throwable $e ){
			Notice::Set($e->getMessage());
		};
	}

	/** Parse grant
	 *
	 */
	static function Grant($records)
	{
		//	...
		$result = [];

		//	...
		foreach( $records as $record ){
			foreach( $record as $sql ){
				/*
			//	$preg = "GRANT (.+) ON (.+)\.(.+) TO '(.+)'@'(.+)' IDENTIFIED BY PASSWORD '(.+)'";
				$preg = "GRANT (.+) ON (.+)\.(.+) TO '(.+)'@'(.+)'";
				*/
			//	$preg = "GRANT (.+) ON (.+)\.(.+) TO (.+)@(.+) IDENTIFIED BY PASSWORD '(.+)'";
				$preg = "GRANT (.+) ON (.+)\.(.+) TO (.+)@(.+)";
				$m    = null;
				if(!preg_match("/$preg/i", $sql, $m) ){
					Notice::Set("Unmatch: {$preg} → {$sql}");
				};

				//	...
				$privileges = $m[1];
				$database   = $m[2];
				$table      = $m[3];
				/*
				$user       = $m[4];
				$host       = $m[5];
				$password   = $m[6];
				*/

				//	...
				$database   = trim($database, '`');
				$table      = trim($table   , '`');
				/*
				$user       = trim($user    , '`');
				$host       = trim($host    , '`');
				*/

				//	...
				foreach( explode(',', $privileges.',') as $privilege ){
					if( $privilege ){
						$result[$database][$table][] = trim($privilege);
					};
				};
			};
		};

		//	...
		return $result;
	}
}
