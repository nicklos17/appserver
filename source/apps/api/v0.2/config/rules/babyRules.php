<?php
include __DIR__.'/baseRules.php';

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'name', 'sex', 'birthday')
    ),
    'ver' => ver(),
    'token' => token(),
    'name' => array(
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
    )
);

$rules['addrel'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'name', 'sex', 'birthday', 'weight', 'height')
    ),
    'ver' => ver(),
    'token' => token(),
    'name' => array(
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

$rules['edit'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'nick', 'sex', 'birthday', 'baby_id', 'weight', 'height')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'nick' => array(
        'required' => 0,
        'length' => array(1, 12),
        'filters' => 'trim',
        'msg' => '10011'
    ),
    'sex' => array(
        'required' => 0,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '10012'
    ),
    'birthday' => array(
        'required' => 0,
        'length' => array('9', '10'),
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10013'
    ),
    'weight' => array(
        'required' => 0,
        'filters' => 'trim',
        'between' => array(5000, 200000),
        'msg' => '10013'
    ),
    'height' => array(
        'required' => 0,
        'filters' => 'trim',
        'between' => array(40, 200),
        'msg' => '10013'
    )
);