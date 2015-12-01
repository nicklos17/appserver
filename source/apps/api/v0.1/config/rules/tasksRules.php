<?php
include __DIR__.'/baseRules.php';

$rules['cats'] = array(
    '_method' => array(
        'post' => array('ver', 'token')
    ),
    'ver' => ver(),
    'token' => token()
);

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'tc_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'tc_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11021'
    )
);

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 't_id')
    ),
    'ver' => ver(),
    'token' => token(),
    't_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11021'
    )
);

$rules['complete'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 't_id')
    ),
    'ver' => ver(),
    'token' => token(),
    't_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11021'
    )
);

$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 't_id')
    ),
    'ver' => ver(),
    'token' => token(),
    't_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11021'
    )
);

$rules['user'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'count', 'since_time', 'max_time')
    ),
    'ver' => ver(),
    'token' => token(),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11021'
    ),
    'since_time' => array(
        'required' => 0,
        'length' => 10,
        'filters' => 'trim',
        'regex' => '/^\d+$/'
    ),
    'max_time' => array(
        'required' => 0,
        'length' => 10,
        'filters' => 'trim',
        'regex' => '/^\d+$/'
    )
);

return $rules;