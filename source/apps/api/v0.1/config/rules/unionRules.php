<?php
include __DIR__.'/baseRules.php';

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'nick', 'sex', 'birthday', 'shoe_qr', 'name', 'weight', 'height')
    ),
    'ver' => ver(),
    'token' => token(),
    'nick' => array(
        'required' => 1,
        'length' => array(1, 12),
        'filters' => 'trim',
        'msg' => '10011'
    ),
    'sex' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '10012'
    ),
    'birthday' => array(
        'required' => 1,
        'length' => array('9', '10'),
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10013'
    ),

    'shoe_qr' => array(
        'required' => 1,
        'length' => 16,
        'filters' => 'trim',
        'msg' => '11001'
    ),

    'name' => array(
        'required' => 1,
        'length' => array(1, 12),
        'filters' => 'trim',
        'msg' => '10011'
    ),

    'weight' => array(
        'required' => 1,
        'filters' => 'trim',
        'between' => array(5000, 200000),
        'msg' => '10013'
    ),
    'height' => array(
        'required' => 1,
        'filters' => 'trim',
        'between' => array(40, 200),
        'msg' => '10013'
    )
);

$rules['checkuser'] = array(
    '_method' => array(
        'post' => array('ver', 'token')
    ),
    'ver' => ver(),
    'token' => token()
);

return $rules;