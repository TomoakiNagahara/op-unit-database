<?php
/**
 * module-testcase:/unit/database/dml/update.inc.php
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
if(!$text = $form->GetValue('text') ){
	return;
};

//	...
$datetime = date(_OP_DATE_TIME_, Env::Time());

//	...
$config = [];
$config['table'] = 't_testcase';
$config['limit'] = 1;
$config['order'] = 'timestamp asc';
$config['set'][] = " text    = $text     ";
$config['set'][] = " updated = $datetime ";
$config['where'][] = 'updated is null';

//	...
if( $io = $db->Update($config) ){
	$form->SetValue('text', '');
};

//	...
return $io;
