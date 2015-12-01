<?php
include __DIR__.'/baseRules.php';

$rules['index'] = array(
    '_method' => array(
        'post' => array('ver', 'token')
    ),
    'ver' => ver(),
    'token' => token()
);

return $rules;