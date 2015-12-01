<?php

include __DIR__ . '/baseRules.php';

$rules['login'] = array(
    '_method' => array(
        'post' => array('ver', 'access_token', 'deviceToken', 'plat', 'type', 'u_tags')
    ),
    'ver' => ver(),
    'access_token' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '11051'
    ),
    'deviceToken' => array(
        'required' => 0,
        'length' => array(14, 64),
        'regex' => '/^[a-zA-Z0-9]+$/',
        'filters' => 'trim',
        'msg' => '11051'
    ),
    'plat' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11051'
    ),
    'type' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'u_tags' => array(
        'required' => 1,
        'filters' => 'trim',
        'length' => array(10, 32),
        'msg' => '11051'
    ),
);

$rules['reg'] = array(
    '_method' => array(
        'post' => array('ver', 'mobi', 'pass', 'session', 'deviceToken', 'u_tags', 'plat', 'name', 'pic', 'type', 'cver', 'captcha')
    ),
    'ver' => ver(),
    'mobi' => mobile(),
    'pass' => password(),
    'session' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'deviceToken' => array(
        'required' => 1,
        'length' => array(14, 64),
        'regex' => '/^[a-zA-Z0-9]+$/',
        'filters' => 'trim',
        'msg' => '10010'
    ),
    'u_tags' => array(
        'required' => 1,
        'filters' => 'trim',
        'length' => array(10, 32),
        'msg' => '11051'
    ),
    'plat' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11051'
    ),
    'name' => array(
        'required' => 1,
        'length' => array(1, 12),
        'filters' => 'trim',
        'msg' => '10011'
    ),
    'pic' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '10011'
    ),
    'type' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'cver' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'captcha' => captcha(),
);

$rules['bind'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'access_token', 'plat', 'u_tags')
    ),
    'ver' => ver(),
    'token' => token(),
    'access_token' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '11051'
    ),
    'plat' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11051'
    ),
    'u_tags' => array(
        'required' => 1,
        'filters' => 'trim',
        'length' => array(10, 32),
        'msg' => '11051'
    ),
);

$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'plat')
    ),
    'ver' => ver(),
    'token' => token(),
    'plat' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11051'
    )
);

return $rules;