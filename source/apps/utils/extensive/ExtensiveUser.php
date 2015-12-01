<?php

namespace Appserver\Utils\Extensive;

use Appserver\Utils\SwooleUserClient as SwooleUserClient;

class ExtensiveUser
{
    protected $swoole;

    public function __construct($ip, $port)
    {
        $this->swoole = new SwooleUserClient(
            $ip,
            $port
        );
    }

    /**
     * [注册]
     * @param  [string] $plat  [第三方类型 1-sina 3-tencent]
     * @param  [string] $mobi  [手机号]
     * @param  [string] $name  [用户名]
     * @param  [string] $pass  [密码]
     * @param  [string] $uTags [第三方uid]
     * @param  [string] $pic   [用户头像]
     * @return [type]        [description]
     */
    public function register($plat, $mobi, $name, $pass, $uTags, $pic, $regtime)
    {
        $uid = $this->swoole->oauthReg($plat, $mobi, $pass, '', $uTags, $name, $pic, $regtime);
        if($uid['flag'] != '')
        {
            //为用户创建个人钱包
            if($this->swoole->createWallet($uid['data'])['data'] != '1')
                return false;
            else
                return $uid['data'];
        }
        else
            return false;
    }

    /**
     * [第三方绑定]
     * @param  [type] $accessToken [description]
     * @param  [type] $uTags       [description]
     * @return [type]              [description]
     */
    public function bind($plat, $uid, $uTags, $pic)
    {
        if($this->swoole->bindThird($plat, $uid, $uTags, $pic)['data'] != '1')
            return false;
        else
            return true;
    }

    /**
     * 第三方解绑
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function unbind($uid, $plat)
    {
        if($this->swoole->unbindThird($uid, $plat)['data'] == 0)
            return false;
        else
            return true;
    }
}
