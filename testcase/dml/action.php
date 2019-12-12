<?php
/**
 * module-testcase:/unit/database/dml/action.inc.php
 *
 * @creation  2019-03-04
 * @version   1.0
 * @package   module-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-03-04
 */
namespace OP;

/* @var $app    UNIT\App      */
/* @var $db     UNIT\database */
/* @var $form   UNIT\Form     */
/* @var $args   array         */

//	...
$prod   = isset($args) ? ($args[2] ?? null): null;
$dir    = isset($args) ? ($args[3] ?? null): null;
$action = isset($args) ? ($args[4] ?? null): null;

//	...
$db = $app->Unit('Database');

//	...
$form = $app->Unit('Form');
$form->Config(__DIR__.'/config.form.php');
$form->Validate();

//	...
if( $token       = $form->Token() ){
	$transaction = $form->GetValue('transaction');
	$commit      = $form->GetValue('commit');
};

//	...
$configs = include(__DIR__.'/../config.db.php');

//	...
if( $config = $configs[$prod] ?? null ){
	if(!$db->Connect($config) ){
		return;
	}
};

//	...
if( $token and file_exists($path = "{$dir}/{$action}.inc.php") ){
	//	...
	if( $transaction ?? null ){
		$db->Transaction();
	};

	//	...
	$result = include($path);

	//	...
	if( $transaction ?? null ){
		if( $commit  ?? null ){
			$db->Commit();
		}else{
			$db->Rollback();
		};
	};
};

//	...
include(__DIR__.'/action_form.phtml');

//	...
if( $result ?? null ){
	D('result', $result);
};
