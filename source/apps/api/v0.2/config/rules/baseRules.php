<?php

//验证手机号
function mobile()
{
    return array(
        'required' => 1,
        'length' => 11,
        'filters' => 'trim',
        'regex' => '/^1[3,4,5,7,8]+\\d{9}$/',
        'msg' => '10002'
    );
}

//验证版本协议号
function ver()
{
    return array(
        'required' => 1,
        'filters' => 'trim',
        'values' => '0.2',
        'msg' => '10001'
    );
}

//验证token
function token()
{
    return array(
        'required' => 1,
        'filters' => 'trim',
        'length' => 32,
        'msg' => '00000'
    );
}

//验证验证码
function captcha()
{
    return array(
        'required' => 1,
        'length' => 4,
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10005'
    );
}

//验证密码
function password()
{
    return array(
        'required' => 1,
        'length' => array(6, 20),
        'regex' => '/^\d+$/',
        'filters' => 'trim',
        'msg' => '10004'
    );
}