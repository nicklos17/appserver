<?php
include __DIR__ . '/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'lasttime', 'times')
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
    ),
    'times' => array(
        'required' => 0,
        'length' => 10,
        'filters' => 'trim',
        'msg' => '11111'
    )
);

$rules['day'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id', 'times', 'type', 'baby_id')
    ),
    'ver' =>ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'times' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10068'
    ),
    'type' => array(
        'required' => 1,
        'filters' => 'trim',
        'range' => array('-1', '0', '1'),
        'msg' => '11111'
    ),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
);

$rules['upload'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id', 'li_id')
    ),
    'ver' =>ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'li_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
);

$rules['delpic'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'pic_id')
    ),
    'ver' =>ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'li_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
);

return $rules;