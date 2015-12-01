<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib as RedisLib,
    Appserver\Utils\Push\AndroidPush as AndroidPush,
    Appserver\Utils\Push\IosPush as IosPush,
    Appserver\Utils\Common;

class PushModule extends ModuleBase
{

    const SUCCESS = '1';
    const FAILED_IOS_PUSH = 11010;
    const FAILED_ANDROID_PUSH = 11011;

    private $deviceTagsModel;
    private $family;
    private $baby;
    public $di;
    public $AndroidPush;
    public $IosPush;

    public function __construct()
    {
        $this->AndroidPush = new AndroidPush();
        $this->IosPush = new IosPush();
        $this->deviceTagsModel = $this->initModel('\Appserver\Mdu\Models\DevtagsModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
    }

    /**
     * [推送消息：通知主号给宝贝绑定]
     * @param  [type] $babyId [description]
     * @param  [string] $uname [用户名字]
     * @return [type]         [description]
     */
    public function pushBinddevMsg($babyId, $uname)
    {
        //获取宝贝的信息
        $babyInfo = $this->baby->getBabyInfoById($babyId);
        //获取宝贝的主号信息
        $hostInfo = $this->family->getHostByBabyId($babyId);

        //根据用户要求的推送时段进行推送
        $disturb = (string)date('H', $_SERVER['REQUEST_TIME']);
        //拿到用户推送相关的信息
        $tokenInfo = $this->deviceTagsModel->getUserInfoByUid($hostInfo['u_id'], '3', $disturb, $disturb);
        if(empty($tokenInfo))
        {
            return self::SUCCESS;
        }
        $content = sprintf($this->di['sysconfig']['bindDevForHost'], $uname, $babyInfo['baby_nick']);
        if($tokenInfo['type'] == '1')
        {
            $fpForIOS = $this->IosPush->readyToSendForIOS($this->di['sysconfig']['pemUrl'], $this->di['sysconfig']['passphrase'], $this->di['sysconfig']['sslUrl']);
            if(!$fpForIOS)
            {
                return self::FAILED_IOS_PUSH;
            }

            //如果deviceken存在，一条一条将消息推送到apns
            if($this->IosPush->sendSingleForIOS($content,
                    $tokenInfo['deviceToken'],
                    $fpForIOS,
                    $this->di['sysconfig']['pushKey'],
                    '',
                    '',
                    $_SERVER['REQUEST_TIME'],
                    ''))
            {
                Common::writeLog(dirname(dirname(dirname(__FILE__))) .'/public/logs/askbind.log', '通知发送成功:对方id:' . $hostInfo['u_id']);
                return self::SUCCESS;
            }
            else
            {
                Common::writeLog(dirname(dirname(dirname(__FILE__))) .'/public/logs/askbind.log', '通知发送失败:对方id:' . $hostInfo['u_id']);
                return self::FAILED_IOS_PUSH;
            }
        }
        elseif($tokenInfo['type'] == '3')
        {
            $pushMsg = array('type' => '', 'content' => $content);
            $resObj = (array)$this->AndroidPush->sendSingleNoticeForJPush($this->di, $content, $tokenInfo['deviceToken'], $pushMsg);
            foreach($resObj as $val)
            {
                $res[] = $val;
            }
            if($res[0] === 0)
            {
                Common::writeLog(dirname(dirname(dirname(__FILE__))) .'/public/logs/askbind.log', '通知发送成功:对方id:' . $hostInfo['u_id']);
                return self::SUCCESS;
            }
            else
            {
                Common::writeLog(dirname(dirname(dirname(__FILE__))) .'/public/logs/askbind.log', '通知发送失败:对方id:' . $hostInfo['u_id'] . ';message:');
                return self::FAILED_ANDROID_PUSH;
            }
        }
    }

}
