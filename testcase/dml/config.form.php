<?php
/**
 * module-testcase:/unit/database/dml/config.form.php
 *
 * @creation  2019-03-07
 * @version   1.0
 * @package   module-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
//	...
$form = [];
$form['name'] = 'testcase-unit-database';

//	...
$input = [];
$input['name']    = 'text';
$input['type']    = 'text';
$input['label']   = 'Text';
$input['cookie']  =  true;
$input['session'] =  true;
$input['placeholder'] = 'one line text';
$input['validate']= [
	'required'    => true,
];
$form['input'][]  = $input;

//	...
$input = [];
$input['name']    = 'transaction';
$input['type']    = 'checkbox';
$input['label']   = 'Transaction';
$input['cookie']  =  true;
$input['session'] =  true;
$input['option']  = [
	'transaction' => 'Do Transaction',
];
$form['input'][]  = $input;

//	...
$input = [];
$input['name']    = 'commit';
$input['type']    = 'radio';
$input['label']   = 'Commit / Rollback';
$input['cookie']  =  true;
$input['session'] =  true;
$input['option']  = [
	['value'=>1, 'label'=>'Commit'],
	['value'=>0, 'label'=>'Rollback'],
];
$form['input'][]  = $input;

//	...
$input = [];
$input['name']   = 'button';
$input['type']   = 'submit';
$input['value']  = 'Submit';
$form['input'][] = $input;

//	...
return $form;
