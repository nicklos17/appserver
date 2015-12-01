<?php
include __DIR__.'/baseRules.php';

$rules['today'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'since_id', 'max_id', 'count')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'since_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/'
    ),
    'max_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

$rules['all'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'since_id', 'max_id', 'count')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'since_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/'
    ),
    'max_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

return $rules;