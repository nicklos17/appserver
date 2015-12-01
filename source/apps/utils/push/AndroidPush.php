<?php

namespace Appserver\Utils\Push;

class AndroidPush
{
    public function sendNoticeForJPush($di, $clientNotice, $userToken, $extras, $clientMess = false)
    {
        if(isset($extras['baby_id']))
            $babyId = 'babyId:' . $extras['u_id'] . ',';
        else
            $babyId = '';

        include_once('jpush/JPushClient.php');
        $appKey = $di['sysconfig']['jpushAppKey'];
        $masterSecret = $di['sysconfig']['jpushSecret'];
        $platform = 'android';
        $client = new \Appserver\Utils\Push\jpush\JPushClient($appKey,$masterSecret);
        //以别名的方式进行推送
        $params = array("receiver_type" => 3,
                "receiver_value" => $userToken,
                "sendno" => 1,
                "send_description" => "",
                "override_msg_id" => "");
        //type=1为日常消息，3为系统消息，使用发送通知模式, 9-添加亲人推送 23-手Q绑定推送 7-账号在其他地方登录
        //type=5为评论或者赞，使用发送消息模式
        if($extras['type'] == '1' || $extras['type'] == '3' || $extras['type'] == '7' || $extras['type'] == '9' || $extras['type'] == '23' || $extras['type'] == '99')
        {
            if( $extras['type'] == '7')
                $extras['content'] = '';

            //发送通知
            $msgResult1 = $client->sendNotification($clientNotice, $params, $extras);
            if($msgResult1->getCode() === 0)
                echo date('Y-m-d H:i:s',time()), ':android通知发送成功:手机型号:', $userToken, ',type:', $extras['type'], ',用户id:',$extras['u_id'], "\n";
            else
                echo date('Y-m-d H:i:s',time()), ':android通知发送失败:手机型号:', $userToken, ',type:', $extras['type'], ',用户id:',$extras['u_id'],'message:',$msgResult1->getMessage(), "\n";

        }
        elseif($extras['type'] == '5')
        {
            //发送消息
            $msgResult2 = $client->sendCustomMessage('发送推送消息', $clientNotice, $params, $extras);
            if($msgResult2->getCode() === 0)
                echo date('Y-m-d H:i:s',time()), ':android通知发送成功:手机型号:', $userToken, ',type:', $extras['type'], ',用户id:',$extras['u_id'], "\n";
            else
                echo date('Y-m-d H:i:s',time()), ':android通知发送失败:手机型号:', $userToken, ',type:', $extras['type'], ',用户id:',$extras['u_id'],'message:',$msgResult2->getMessage(), "\n";
        }
    }

    //发送单通知
    public function sendSingleNoticeForJPush($di, $clientNotice, $userToken, $extras)
    {
        include_once('jpush/JPushClient.php');
        $appKey = $di['sysconfig']['jpushAppKey'];
        $masterSecret = $di['sysconfig']['jpushSecret'];
        $platform = 'android';
        $client = new \Appserver\Utils\Push\jpush\JPushClient($appKey,$masterSecret);
        //以别名的方式进行推送
        $params = array("receiver_type" => 3,
                "receiver_value" => $userToken,
                "sendno" => 1,
                "send_description" => "",
                "override_msg_id" => "");

        return $client->sendNotification($clientNotice, $params, $extras);
    }
}