<?php
/**	op-unit-database:/QQL.class.php
 *
 * @created   2017-01-24
 * @license   Apache-2.0
 * @package   op-unit-database-ci
 * @copyright (C) 2025 Tomoaki Nagahara
 */

/**	namespace
 *
 * @created   2017-12-18
 */
namespace OP\UNIT\DATABASE;

/**	Used class
 *
 * @creation  2019-03-04
 */
use OP\OP_CORE;
use OP\OP_CI;
use OP\IF_DATABASE;

/**	QQL
 *
 * @created   2017-01-24
 */
class QQL
{
	/**	trait
	 *
	 */
	use OP_CORE;
	use OP_CI;

	/**	Parse option.
	 *
	 * @param  array|string  $options
	 * @return array         $result
	 */
	static private function _ParseOption($options=[])
	{
		//	...
		if( gettype($options) === 'string' ){
			$options = self::_ParseOptionString($options);
		}

		//	...
		$result = ['','',''];

		//	...
		foreach( $options ?? [] as $key => $val ){
			switch( $key = trim($key) ){
				case 'limit':
					$result[0] = 'LIMIT '.(int)$val;
					break;

				case 'order':
					if( $pos = strpos($val, ' ') ){
						$field = substr($val, 0, $pos);
						$order = substr($val, $pos);
						$result[1] = "ORDER BY `{$field}` $order";
					}else{
						$result[1] = "ORDER BY `{$val}`";
					}
					break;

				case 'offset':
					$result[2] = 'OFFSET '.(int)$val;
					break;
			}
		}

		//	...
		return $result;
	}

	/**	Parse option string.
	 *
	 * @param  string $options
	 * @return array  $result
	 */
	static private function _ParseOptionString( ?string $options = '' ) : array
	{
		//	...
		$result = null;

		//	...
		foreach( explode(',', $options) as $option ){
			//	...
			$option = trim($option);

			//	...
			if( $pos = strpos($option, '=') ){
				$key = substr($option, 0, $pos);
				$val = substr($option, $pos +1);
			}else{
				continue;
			}

			//	...
			$result[$key] = $val;
		}

		//	...
		return $result ?? [];
	}

	static private function _ParseField( string $field, string $label='default' )
	{
		//	...
		$join = [];

		//	...
		if( strpos($field, ',') ){
			//	Many fields.
			foreach( explode(',', $field) as $temp ){
				$join[] = self::_ParseFieldFunc($temp, $label);
			}
		}else{
			//	Single field.
			$join[] = self::_ParseFieldFunc($field, $label);
		}

		//	...
		return join(',', $join);
	}

	static private function _ParseFieldFunc( string $field, string $label='default' )
	{
		//	...
		$match = NULL;

		//	...
		if( preg_match('|([_a-z0-9]+)\(([_a-z0-9]+)\)|i', $field, $match) ){
			$match[2] = OP()->Unit()->Database()->Quote($match[2], $label);
			$field = "{$match[1]}({$match[2]})";
		}else{
			$field = OP()->Unit()->Database()->Quote( $field, $label );
		}

		//	...
		return $field;
	}

	/**	Convert to SQL from QQL.
	 *
	 * @param   string      $qql
	 * @param   string      $opt
	 * @param   string      $label
	 * @return  array       $sql
	 */
	static function Parse( string $qql, string|array $opt='', string $label='default' )
	{
		//	...
		$field  = '*';
		$dbname = null;
		$table  = null;
		$where  = null;
		$limit  = null;
		$order  = null;
		$offset = null;
		$group  = null;

		//	...
		if(!$pdo = OP()->Unit()->Database()->PDO($label) ){
			return;
		}

		//	field
		if( $pos = strpos($qql, '<-') ){
			list($field, $qql) = explode('<-', $qql);
			$field = self::_ParseField(trim($field), $label);
		}else{
			$field = '*';
		}

		//	...
		if( $pos = strrpos($qql, ' = ') ){
		}else if( $pos = strrpos($qql, '>') ){
		}else if( $pos = strrpos($qql, '<') ){
		}else if( $pos = strrpos($qql, '>=') ){
		}else if( $pos = strrpos($qql, '<=') ){
		}else if( $pos = strrpos($qql, '!=') ){
		}else{    $pos = false; }

		//	QQL --> database.table, value
		if( $pos === false ){
			$db_table = trim($qql);
		}else{
			$where    = true;
			$db_table = trim(substr($qql, 0, $pos));
			$evalu    = trim(substr($qql, $pos, 2));
			$value    = trim(substr($qql, $pos +2));
		}

		//	database.table --> database, table
		$pos = strpos($db_table, '.');
		if( $pos === false ){
			$table = $db_table;
		}else{
			$temp = explode('.', $db_table);
			if( $where ){
				switch( count($temp) ){
					case 2:
						$table = $temp[0];
						$which = $temp[1];
						break;
					case 3:
						$dbname= $temp[0];
						$table = $temp[1];
						$which = $temp[2];
						break;
					default:
						d($temp);
				}

				//	...
				if( $value === 'null' ){
					$value  =  'NULL';
					$evalu  =  'IS';
				}else{
					$value = $pdo->quote($value);
				}

				//	...
				$which = OP()->Unit()->Database()->Quote( $which, $label );
				$where = "WHERE {$which} {$evalu} {$value}";
			}else{
				switch( count($temp) ){
					case 1:
						$table = trim($temp);
						break;
					case 2:
						$dbname= $temp[0];
						$table = $temp[1];
						break;
					default:
						d($temp);
				}
			}
		}

		//	...
		$dbname = $dbname ? OP()->Unit()->Database()->Quote($dbname, $label) : null;
		$table  = OP()->Unit()->Database()->Quote($table, $label);

		//	...
		if( $opt ){
		list($limit, $order, $offset) = self::_ParseOption($opt);
		}

		//	...
		return [
			'database' => $dbname,
			'table'    => $table,
			'field'    => $field,
			'where'    => $where,
			'order'    => $order,
			'limit'    => $limit,
			'offset'   => $offset,
			'group'    => $group,
		];
	}

	/**	Execute Select.
	 *
	 * @param   array       $select
	 * @param   IF_DATABASE $_db
	 * @return  array       $record
	 */
	static function Select( array $select, string $label='default' )
	{
		//	...
		$database = $table = $field = $where = $order = $limit = $offset = $group = null;

		//	...
		foreach( ['database','table','field','where','order','limit','offset', 'group'] as $key ){
			${$key} = $select[$key];
		}

		//	...
		if( $database ){
			$database .= ' .';
		}

		//	...
		$query = "SELECT $field FROM $database $table $where $group $order $limit $offset";

		/*
		//	"LIMIT 1" --> 1
		$limit = (int)substr($limit, strpos($limit, ' ')+1);
		*/

		//	...
		$record = OP()->Unit()->Database()->Query($query, 'select', $label);

		/*
		//	QQL is " name <- t_table.id = $id " and limit is 1.
		if( $limit === 1 ){
			//	In case of empty.
			if( count($record) === 0 ){
				//	Empty.
				$record = null;
			}else if( $field === '*' ){
				//	No adjust.
			}else{
				//	Has value.
				$record = array_shift($record);
			};
		};
		*/

		/*
		//	Result has many record, and single field.
		if( is_array($record) and ($field !== '*') and (strpos($field, ',') === false) ){
			//	...
			$result = [];

			//	...
			foreach( $record as $temp ){
				//	...
				$result[] = is_array($temp) ? array_shift($temp): $temp;
			};

			//	Overwrite
			$record = $result;
		};
		*/

		//	...
		return $record;
	}

	/**	Execute QQL.
	 *
	 * @param   string       $qql
	 * @param   string|array $opt
	 * @param   IF_DATABASE  $DB
	 * @return  array        $record
	 */
	static function Execute( string $qql, string|array $opt='', string $label='default' )
	{
		//	...
		$select = self::Parse( $qql, $opt, $label );

		//	...
		return self::Select( $select, $label );
	}
}
