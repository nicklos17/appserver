
<?php
include __DIR__.'/baseRules.php';
$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'coordinates', 'name', 'place', 'radius', 'validtime')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'coordinates' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^[\d|\.]+\,[\d|\.]+$/',
        'msg' => '10083'
    ),
    'name' => array(
        'required' => 1,
        'length' => array(1, 100),
        'filters' => 'trim',
        'msg' => '10084'
    ),
    'place' => array(
        'required' => 1,
        'length' => array(1, 280),
        'filters' => 'trim',
        'msg' => '10085'
    ),
    'radius' => array(
        'required' => 1,
        'filters' => 'trim',
        'between' => array(50, 500),
        'regex' => '/^\d{1,2}0$/',
        'msg' => '10086'
    ),
    'validtime' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^[\d|,]+$/',
        'msg' => '10087'
    ),
);

$rules['edit'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'fence_id', 'coordinates', 'name', 'place', 'radius', 'validtime')
    ),
    'ver' => ver(),
    'token' => token(),
    'fence_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10082'
    ),
    'coordinates' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^[\d|\.]+\,[\d|\.]+$/',
        'msg' => '10083'
    ),
    'name' => array(
        'required' => 1,
        'length' => array(1, 100),
        'filters' => 'trim',
        'msg' => '10084'
    ),
    'place' => array(
        'required' => 1,
        'length' => array(1, 280),
        'filters' => 'trim',
        'msg' => '10085'
    ),
    'radius' => array(
        'required' => 1,
        'filters' => 'trim',
        'between' => array(50, 500),
        'regex' => '/^\d{1,2}0$/',
        'msg' => '10086'
    ),
    'validtime' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^[\d|,]+$/',
        'msg' => '10087'
    ),
);

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'count')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^[\d|\.]+$/',
        'msg' => '10021'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'fences_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'fences_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10082'
    ),
);

return $rules;

