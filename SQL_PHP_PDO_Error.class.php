<?php
/**
 * unit-database:/SQL_PHP_PDO_Error.class.php
 *
 * @creation  2019-01-09
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-01-09
 */
namespace OP\UNIT\DATABASE;

/** Used class
 *
 * @creation  2019-03-04
 */
use OP\OP_CORE;
use OP\Notice;

/** SQL_PHP_PDO_Error
 *
 * @creation  2019-01-09
 * @version   1.0
 * @package   unit-database
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class SQL_PHP_PDO_Error
{
	/** trait
	 *
	 */
	use OP_CORE;

	/** PHP PDO Error
	 *
	 * @param \PDOException $e
	 */
	static function Auto(string $prod, \PDOException $e)
	{
		//	...
		switch( $e->getCode() ){

			//	SQLite
			case 0:
				$module = 'sqlite';
				Notice::Set("php-{$module} is not installed.");
				include( ConvertPath('asset:/bootstrap/php/content.phtml') );
				break;

			//	MySQL
			case '2002':
				$key = 'pdo_mysql.default_socket';
				$ini = ini_get($key);
				$str = $e->getMessage();
				if( $ini ){
					Notice::Set($str);
				}else{
					Notice::Set('The path of socket is not set in "php.ini".'.PHP_EOL."Please set to \"{$key}\".");
				};
				break;

			default:
				Notice::Set($e);
		};
	}
}
