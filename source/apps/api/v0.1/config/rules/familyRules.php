
<?php
include __DIR__.'/baseRules.php';

$rules['list'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'count')
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
        'msg' => '11111'
    )
);

$rules['gua'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'fam_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'fam_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10079'
    )
);

$rules['add'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'name', 'ishost', 'flag', 'mobi', 'user_qr', 'captcha')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'name' => array(
        'required' => 1,
        'filters' => 'trim',
        'length' => array(1, 12),
        'msg' => '10078'
    ),
    'ishost' => array(
        'required' => 1,
        'filters' => 'trim',
        'range' => array(3, 5),
        'msg' => '11111'
    ),
    'flag' => array(
        'required' => 0,
        'filters' => 'trim',
        'range' => array(1),
        'msg' => '11111'
    ),
    'mobi' => array(
        'required' => 0,
        'length' => 11,
        'filters' => 'trim',
        'regex' => '/^1[3,4,5,7,8]+\\d{9}$/',
        'msg' => '10002'
    ),
    'user_qr' => array(
        'required' => 0,
        'filters' => 'trim',
        'msg' => '11081'
    ),
    'captcha' => array(
        'required' => 0,
        'filters' => 'trim',
        'length' => 4,
        'regex' => '/^\d+$/',
        'msg' => '10005'
    ),
);

$rules['del'] = array(
    '_method' => array(
        'post' => array('ver', 'token', 'baby_id', 'fam_id')
    ),
    'ver' => ver(),
    'token' => token(),
    'baby_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10021'
    ),
    'fam_id' => array(
        'required' => 1,
        'filters' => 'trim',
        'regex' => '/^\d+$/',
        'msg' => '10079'
    )
);

return $rules;

