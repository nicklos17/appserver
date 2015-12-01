<?php
include __DIR__ . '/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'lasttime')
    ),
    'ver' =>ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'lasttime' => array(
        'required' => 0,
        'length' => 10,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

$rules['day'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id')
    ),
    'ver' =>ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
);

$rules['shoecoor'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id')
    ),
    'ver' =>ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    )
);

$rules['find'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'type')
    ),
    'ver' =>ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'type' => array(
        'required' => 0,
        'length' => 1,
        'filters' => 'trim',
        'range' => array('1', '3'),
        'msg' => '11111'
    )
);

$rules['circle'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'md5')
    ),
    'ver' =>ver(),
    'token' => token(),
    'md5' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '11111'
    ),
);

return $rules;