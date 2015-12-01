<?php

 include __DIR__ . '/baseRules.php';

 $rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'count', 'max_id', 'since_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    ),
    'max_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    ),
    'since_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

$rules['callist'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'month')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'month' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d{4}\-([1-9]|1[0-2])$/',
        'msg' => '10068'
    ),
);

$rules['mark'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id', 'mark', 'tags')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'mark' => array(
        'required' => 1,
        'length' => array(1, 100),
        'filters' => 'trim',
        'msg' => '10064'
    ),
);

$rules['message'] = array(
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

$rules['fresh'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
);

return $rules;