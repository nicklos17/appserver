<?php

namespace Appserver\Utils;

use \swoole_client as swoole_client;
use \Phalcon\Acl\Exception as E;

class SwooleUserClient
{
    public $method = array(
        'checkMobi',
        'regFromSwoole',
        'upload',
        'login',
        'edit',
        'reset',
        'getUserInfoByMobi',
        'getUserInfo',
        'createWallet',
        'coinsInfo',
        'checkUserName',
        'setUserName',
        'updateLevel',
        'checkInReceive',   //签到获取云币奖励
        'checkThirdLogin',   //检查用户是否用第三方登录过
        'unbindThird',    //第三方解绑
        'bindThird',       //第三方绑定
        'modifyUser',      //编辑用户信息
        'sinaOauth', 	//新浪授权
        'qqOauth',      //qq授权
        'oauthReg',     //第三方登录补充资料
        'userInfoByIds'  //根据多个亲人的id获取亲人信息
        );

    public $timeOut = 2.5;

    public function __construct($host, $port, $sync = true)
    {
        $this->host = $host;
        $this->port = $port;
        $this->runMethod = $sync ? SWOOLE_SOCK_SYNC : SWOOLE_SOCK_ASYNC;
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, $this->runMethod);
        $this->swooleConnect();
    }

    private function swooleConnect()
    {
        if($this->client->connect($this->host, $this->port, $this->timeOut))
            return true;
        else
            return $this->throwException('Connect server ' . $this->host . 'failed on port ' . $this->port);
    }

    public function __destruct()
    {
        unset($this->client);
    }

    private function throwException($msg, $errCode = NULL)
    {
        throw new E($msg, $errCode);
        return false;
    }

    public function __call($name, $args)
    {
        if(!in_array($name, $this->method))
        {
            throw new E('call undefined function');
            return false;
        }

        $send = $this->client->send(json_encode(array('cmd' => $name, 'args' => $args)));
        if(!$send)
            return $this->throwException($name . ' failed', $this->client->errCode);

        $recv = $this->client->recv();
        if(!$recv)
            return $this->throwException($name . ' failed', $this->client->errCode);

        return json_decode($recv, true);
    }

    /**
     * 上传文件
     * @return Bool
     */
    public function uploadAvatar($fileName, $size, $rename, $savePath)
    {
        $res = $this->client->send(json_encode(array('cmd' => 'upload', 'path' => $savePath, 'name' => $rename, 'size' => $size)));
        if(!$res)
            return $this->throwException('UploadAvatar failed', $this->client->errCode);

        $recv = $this->client->recv();
        if(!$recv)
            return $this->throwException('UploadAvatar failed', $this->client->errCode);

        $res = json_decode($recv, true);
        if(!$res['flag'])
            return $res;

        // 读取文件内容进行上传
        $fp = fopen($fileName, 'r');
        while(!feof($fp))
        {
            if(!$this->client->send(fread($fp, 8000)))
                return $this->throwException('UploadAvatar failed', $this->client->errCode);
        }

        $recv = $this->client->recv();
        if(!$recv)
            return $this->throwException('UploadAvatar failed', $this->client->errCode);

        $res = json_decode($recv, true);
        return $res['flag'];
    }
}