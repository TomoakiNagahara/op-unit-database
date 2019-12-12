<?php
/**
 * module-testcase:/unit/database/dml/count.inc.php
 *
 * @creation  2019-03-10
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
$config['where'][] = ' ai > 0 ';

//	...
if( $text = $form->GetValue('text') ){
	$config['where'][] = " text like %$text% ";
};

//	...
D( $db->Count($config) );
