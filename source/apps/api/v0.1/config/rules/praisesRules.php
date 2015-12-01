<?php
include __DIR__.'/baseRules.php';

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10062'
    )
);

$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    )
);

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id', 'count', 'max_id', 'since_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10062'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    ),
    'since_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    ),
    'max_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    )
);

return $rules;