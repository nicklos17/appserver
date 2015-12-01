<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'app_ver', 'type')
    ),
    'ver' => ver(),
    'app_ver' => array(
        'required' => 1,
        'length' => array(1, 10),
        'regex' => '/^[\d|\.]+$/',
        'filters' => 'trim',
        'msg' => '66666'
    ),
    'type' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '66666'
    )
);

$rules['hardware'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'time')
    ),
    'ver' => ver(),
    'token' => token(),
    'time' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11085'
    )
);

return $rules;

