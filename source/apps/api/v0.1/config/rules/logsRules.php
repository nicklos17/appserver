<?php
include __DIR__.'/baseRules.php';

$rules['android'] = array(
    '_method' => array(
        'post' => array('ver', 'mode', 'time')
    ),
    'ver' => ver(),
    'mode' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '66666'
    ),
    'time' => array(
        'required' => 1,
        'filters' => 'trim',
        'msg' => '10078'
    )
);

return $rules;