<?php

 include __DIR__ . '/baseRules.php';

 $rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'locus_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

return $rules;