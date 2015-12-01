<?php
include __DIR__.'/baseRules.php';

$rules['captcha'] = array(
    '_method' =>array(
        'post' => array('ver', 'mobi', 'captcha'),
    ),
    'ver' => ver(),
    'token' => mobile(),
    'captcha' => captcha()
);

$rules['reset'] = array(
    '_method' =>array(
        'post' => array('session', 'passnew'),
    ),
    'session' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'pass' => password(),
);

return $rules;