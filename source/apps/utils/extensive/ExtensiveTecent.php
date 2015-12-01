<?php

namespace Appserver\Utils\Extensive;

use Appserver\Utils\Extensive\UserInterface,
       Appserver\Utils\Extensive\ExtensiveUser,
    Appserver\Utils\SwooleUserClient as SwooleUserClient;

class ExtensiveTecent extends ExtensiveUser implements UserInterface
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
        $content = $this->swoole->qqOauth($accessToken, $uTags);
        if($content['data']['ret'] != '0')
            return false;
        else
            return array('u_name' => $content['data']['nickname'], 'u_pic' => $content['data']['figureurl']);
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
