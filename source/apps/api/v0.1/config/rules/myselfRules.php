<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token')
    ),
    'ver' => ver(),
    'token' => token()
);

$rules['disturb'] = array(
    '_method' => array(
        'post' =>array('ver', 'token', 'disturb', 'start', 'end'),
    ),
    'ver' => ver(),
    'token' => token(),
    'disturb' => array(
        'required' => '1',
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11060'
    ),
    'start' => array(
        'required' => '0',
        'between' => array(0, 24),
        'filters' => 'trim',
        'msg' => '11061'
    ),
    'end' => array(
        'required' => '0',
        'between' => array(0, 24),
        'filters' => 'trim',
        'msg' => '11061'
    ),
);

return $rules;