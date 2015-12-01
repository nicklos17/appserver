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

class QQBindRpcService
{
    public function __construct($serverIP, $serverPort, $server = false)
    {
        if (! $server) // 启动client
        {
            $this->socket = new TSocket($serverIP, $serverPort);
        }
        else // 启动服务器
        {
        }
        $this->trans = new TBufferedTransport($this->socket, 1024, 1024);
        $this->protocol = new TBinaryProtocol($this->trans);
        $this->client = new \thriftrpc\QQBindThriftRpcClient($this->protocol);

        $this->trans->open();
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
    
    public function QQDeviceLocate($sn, $din, $openId)
    {
        return $this->client->QQDeviceLocate($sn, $din, $openId);
    }
    
    public function __destruct()
    {
        $this->trans->close();
    }
}