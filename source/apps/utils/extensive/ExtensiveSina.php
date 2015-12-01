<?php

namespace Appserver\Utils\Extensive;

use Appserver\Utils\Extensive\UserInterface,
       Appserver\Utils\Extensive\ExtensiveUser,
    Appserver\Utils\SwooleUserClient as SwooleUserClient;

class ExtensiveSina extends ExtensiveUser implements UserInterface
{

    protected $es;
    protected $swoole;

    public function __construct($ip, $port)
    {
        $this->es = new ExtensiveUser($ip, $port);
        $this->swoole = new SwooleUserClient($ip, $port);
    }

    public function login($accessToken, $uTags)
    {
        $content = $this->swoole->sinaOauth($accessToken, $uTags, $_SERVER['REMOTE_ADDR']);
        if(!isset($content) || array_key_exists('error_code', $content['data']))
            return false;
        else
            return array('u_name' => $content['data']['screen_name'], 'u_pic' => $content['data']['avatar_hd']);
}

    public function register($plat, $mobi, $name, $pass, $uTags, $pic, $regtime)
    {
        return $this->es->register($plat, $mobi, $name, $pass, $uTags, $pic, $regtime);
    }

    public function bind($plat, $uid, $uTags, $pic)
    {
        return $this->es->bind($plat, $uid, $uTags, $pic);
    }

    public function unbind($plat, $uid)
    {
        return $this->es->unbind($plat, $uid);
    }

}
