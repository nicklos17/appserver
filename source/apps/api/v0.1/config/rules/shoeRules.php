
<?php
include __DIR__.'/baseRules.php';

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'count')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 0,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'count' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '11111'
    )
);

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'shoe_qr')
    ),
    'ver' => ver(),
    'token' => token(),
    'shoe_qr' => array(
        'required' => 1,
        'length' => 16,
        'filters' => 'trim',
        'msg' => '11001'
    )
);

$rules['bind'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'shoe_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'shoe_id' => array(
        'required' => 1,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10034'
    )
);

$rules['mode'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'mode', 'shoe_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'mode' => array(
        'required' => 1,
        'range' => array(1, 3, 5),
        'filters' => 'trim',
        'msg' => '10033'
    ),
    'shoe_id' => array(
        'required' => 1,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10021'
    )
);

$rules['getmode'] = array(
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

$rules['off'] = array(
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

$rules['unbind'] = array(
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

