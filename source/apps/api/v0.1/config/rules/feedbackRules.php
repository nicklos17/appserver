<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'content', 'os', 'version')
    ),
    'ver' => ver(),
    'token' => token(),
    'content' => array(
        'required' => 1,
        'length' => array(1, 255),
        'filters' => 'trim',
        'msg' => '10090'
    ),
    'os' => array(
        'required' => 1,
        'range' => array(1, 3),
        'filters' => 'trim',
        'msg' => '11111'
    ),
    'version' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '11111'
    )
);

return $rules;