<?php
include __DIR__.'/baseRules.php';

$rules['captcha'] = array(
    '_method' => array(
        'post' => array('ver', 'mobi', 'type', 'baby_id', 'token')
    ),
    'ver' => ver(),
    'mobi' => array(
        'required' => 0,
        'length' => 11,
        'filters' => 'trim',
        'regex' => '/^1[3,4,5,7,8]+\\d{9}$/',
        'msg' => '10002'
    ),
    'type' => array(
        'required' => 1,
        'range' => array(1, 3, 5, 7, 9, 11),
        'filters' => 'trim',
        'msg' => '11111',
    ),
    'baby_id' => array(
        'required' => 0,
        'regex' => '/^\d*$/',
        'filters' => 'trim',
        'msg' => '11111',
    ),
    'token' => array(
        'required' => 0,
        'filters' => 'trim',
        'length' => 32,
        'msg' => '00000'
    )

);

return $rules;