<?php

namespace Appserver\Utils\Extensive;

interface UserInterface{

    public function login($accessToken, $uTags);

    public function register($plat, $mobi, $name, $pass, $uTags, $pic, $regtime);

    public function bind($plat, $uid, $uTags, $pic);

    public function unbind($plat, $uid);
}

