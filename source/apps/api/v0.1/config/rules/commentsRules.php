
<?php
include __DIR__.'/baseRules.php';

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id', 'lc_content', 'lc_uid')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10062'
    ),
    'lc_content' => array(
        'required' => 1,
        'length' => array(1, 510),
        'filters' => 'trim',
        'msg' => '10069'
    ),
    'lc_uid' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '66666'
    )
);

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'locus_id', 'count', 'max_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'locus_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10062'
    ),
    'count' => array(
        'required' => 1,
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


$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'lc_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'lc_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10066'
    )
);

return $rules;

