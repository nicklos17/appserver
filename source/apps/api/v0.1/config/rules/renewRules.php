<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'shoe_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'shoe_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10034'
    )
);

$rules['alipay'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'shoe_id', 'service_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'shoe_id' => array(
        'required' => 1,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10034'
    ),
    'service_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    )
);

$rules['wechat'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'shoe_id', 'service_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'shoe_id' => array(
        'required' => 1,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10034'
    ),
    'service_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    )
);

return $rules;

