
<?php
include __DIR__.'/baseRules.php';

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'shoe_qr', 'baby_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'shoe_qr' => array(
        'required' => 1,
        'length' => 16,
        'filters' => 'trim',
        'msg' => '11001'
    ),
    'baby_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
);

$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'shoe_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'shoe_id' => array(
        'required' => 1,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10034'
    )
);

return $rules;

