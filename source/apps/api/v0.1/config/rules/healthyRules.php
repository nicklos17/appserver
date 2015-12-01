<?php
include __DIR__.'/baseRules.php';

$rules['summary'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    )
);

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

$rules['fore'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'province', 'city', 'City', 'district')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'province' => array(
        'required' => 0,
        'length' => array(1, 50),
        'filters' => 'trim',
        'msg' => '11087'
    ),
    'city' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11087'
    ),
    'City' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11087'
    ),
    'district' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11087'
    ),
);

return $rules;