<?php
include __DIR__.'/baseRules.php';

$rules['reg'] = array(
    '_method' => array(
        'post' => array('ver', 'mobi', 'captcha', 'pass', 'file', 'type', 'deviceToken', 'cver')
    ),
    'ver' => ver(),
    'mobi' => mobile(),
    'captcha' => captcha(),
    'pass' => password(),
    'type' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'deviceToken' => array(
        'required' => 0,
        'length' => array(14, 64),
        'regex' => '/^[a-zA-Z0-9]+$/',
        'filters' => 'trim',
        'msg' => '10010'
    ),
    'cver' =>array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11111'
    )
);

$rules['login'] = array(
    '_method' => array(
        'post' => array('ver', 'mobi', 'pass', 'type', 'deviceToken', 'cver')
    ),
    'ver' => ver(),
    'mobi' => mobile(),
    'pass' => password(),
    'type' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'deviceToken' => array(
        'required' => 0,
        'length' => array(14, 64),
        'regex' => '/^[a-zA-Z0-9]+$/',
        'filters' => 'trim',
        'msg' => '10010'
    ),
    'cver' =>array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11111'
    )
);

$rules['change'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'captcha', 'passnew')
    ),
    'ver' => ver(),
    'token' => token(),
    'captcha' => captcha(),
    'passnew' => password(),
);

$rules['edit'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'uname')
    ),
    'ver' => ver(),
    'token' => token(),
    'uname' => array(
        'required' => 0,
        'length' => array(1, 12),
        'filters' => 'trim',
        'msg' => '10011'
    ),
);

$rules['trial'] = array(
    '_method' => array(
        'post' => array('ver', 'lat', 'lng')
    ),
    'ver' => ver(),
    'lat' => array(
            'required' => '1',
            'filters' => 'trim',
            'msg' => '10024'
        ),
    'lng' => array(
            'required' => '1',
            'filters' => 'trim',
            'msg' => '10024'
        )
);

$rules['logout'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'deviceToken')
    ),
    'ver' => ver(),
    'token' => mobile(),
    'token' => array(
        'required' => 0,
        'filters' => 'trim',
        'length' => 32,
        'msg' => '00000'
    ),
    'deviceToken' => array(
        'required' => 0,
        'length' => array(14, 64),
        'regex' => '/^[a-zA-Z0-9]+$/',
        'filters' => 'trim',
        'msg' => '10010'
    ),
);

return $rules;
