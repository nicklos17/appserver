<?php

namespace Appserver\Utils\Push;

class IosPush
{
    /**
     * ios推送消息准备
     * @param str $pem pem文件的路径
     * @param str $passphrase  私钥
     * @param str $ssl  推送消息的ssl地址
     * @return resource
     */
    public function readyToSendForIOS($pem, $passphrase, $ssl)
    {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'allow_self_signed', true);
        stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
        stream_context_set_option($ctx, 'ssl', 'local_cert', $pem);
        stream_context_set_option($ctx, "ssl", "passphrase", $passphrase);
        $fp = stream_socket_client($ssl, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if(!$fp)
        {
            return FALSE;
        }
        return $fp;
    }
    
    /**
     * ios推送单条消息
     * @param str $message 消息内容
     * @param str $deviceToken  用户的设备标签
     * @param str fp 
     */
    public function sendSingleForIOS($clientMess, $deviceToken, $fp, $pushKey, $type, $message, $pushtime, $cver = '')
    {
        //如果有上传客户端版本，则将message反json一次
        if($cver != '')
        {
            $message = json_decode($message, true);
        }
        $body = array('aps' => array('alert' => $clientMess,'badge' => 1,'sound' => 'default', 'type' => $type, $pushKey => $message));
        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
        if(!$result)
            return false;
        else
            return true;
    }
}