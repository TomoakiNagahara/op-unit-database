<?php
/**
 * module-testcase:/unit/database/dml/delete.inc.php
 *
 * @creation  2019-03-05
 * @version   1.0
 * @package   module-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-03-07
 */
namespace OP;

/* @var $app    UNIT\App      */
/* @var $form   UNIT\Form     */
/* @var $db     UNIT\Database */
/* @var $args   array         */

//	...
$config = [];
$config['table'] = 't_testcase';
$config['limit'] = 1;
$config['ordeer']= 'updated asc';
$config['where'][] = 'updated not null';

//	...
if( $text = $form->GetValue('text') ){
	$config['where'][] = " text like %$text% ";
};

//	...
return $db->Delete($config);
