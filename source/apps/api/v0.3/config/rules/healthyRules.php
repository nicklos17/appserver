<?php
include __DIR__.'/baseRules.php';

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'since_date', 'count')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'since_date' => array(
        'required' => 0,
        'length' => 10,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11085'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    ),
);

return $rules;