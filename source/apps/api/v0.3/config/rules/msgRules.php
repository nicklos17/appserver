
<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'count', 'max_id', 'since_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    ),
    'since_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    ),
    'max_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    )
);

return $rules;

