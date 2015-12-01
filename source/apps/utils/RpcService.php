<?php

namespace Appserver\Utils;

require_once dirname(dirname(__FILE__)) . '/utils/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', dirname(dirname(__FILE__)) . '/utils');
$loader->registerDefinition('thriftrpc', dirname(__FILE__) . '/gen-php');
$loader->register();

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TBufferedTransport;
use Thrift\Exception\TException;

class RpcService
{
    public function __construct($serverIP, $serverPort, $timeout = '10000', $server = false)
    {
        if (! $server) // 启动client
        {
            $this->socket = new TSocket($serverIP, $serverPort);
            $this->socket->setRecvTimeout($timeout);
            $this->socket->setSendTimeout(500);
        }
        else // 启动服务器
        {
        }
        $this->trans = new TBufferedTransport($this->socket, 1024, 1024);
        $this->protocol = new TBinaryProtocol($this->trans);
        $this->client = new \thriftrpc\ThriftRpcClient($this->protocol);
        
        $this->trans->open();
    }
    
    // 发送短信验证码
    public function smsSend($mobile, $msg)
    {
        $this->client->SmsSend($mobile, $msg);
    }
    
    // 关机
    public function setDevHalt($devId, $mobi)
    {
        $this->client->SetDevHalt($devId, $mobi);
    }
    
    // 设置童鞋模式
    public function setDevMod($devId, $mobi, $mod)
    {
        $this->client->SetDevMod($devId, $mobi, $mod);
    }
    
    // 获取假点
    public function getPoint($babyId)
    {
        return $this->client->GetPoint($babyId);
    }
    
    // 获取宝贝今天排行
    public function GetStepDayRank($babyId, $day, $length)
    {
        return $this->client->GetStepDayRank($babyId, $day, $length);
    }
    
    // 获取今日排行所要的名次
    public function GetStepDayRankByOffset($start, $stop, $day)
    {
        return $this->client->GetStepDayRankByOffset($start, $stop, $day);
    }
    
    // 获取宝贝地理位置
    public function GetBabyLocation($babyId)
    {
        return $this->client->GetBabyLocation($babyId);
    }
    
    // 获取宝贝总排行
    public function GetStepAllRank($babyId, $length)
    {
        return $this->client->GetStepAllRank($babyId, $length);
    }
    
    // 获取总排行所要的名次
    public function GetStepAllRankByOffset($start, $stop)
    {
        return $this->client->GetStepAllRankByOffset($start, $stop);
    }
    
    public function QQBindDevice($sn, $openId, $accessToken)
    {
        $this->client->QQBindDevice($sn, $openId, $accessToken);
    }
    
    public function QQBindDevices($snData, $openId, $accessToken)
    {
        $this->client->QQBindDevices($snData, $openId, $accessToken);
    }
    public function QQUnbindDevice($sn, $openId)
    {
        $this->client->QQUnbindDevice($sn, $openId);
    }
    
    public function QQUnbindDevices($snData, $openId)
    {
        $this->client->QQUnbindDevices($snData, $openId);
    }
    
    public function QQSendMessage($sn, $openId, $message)
    {
        $this->client->QQSendMessage($sn, $openId, $message);
    }
    
    //获取用户体验数据
    public function GetTrialData($lat, $lng, $time)
    {
        return $this->client->GetTrialData($lat, $lng, $time);
    }
    
    //获取定位信息
    public function LocateFind($uuid)
    {
        return $this->client->LocateFind($uuid);
    }
    
    public function __destruct()
    {
        $this->trans->close();
    }
}