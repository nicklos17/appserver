<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib as RedisLib,
    Appserver\Utils\Push\AndroidPush as AndroidPush,
    Appserver\Utils\Push\IosPush as IosPush;

class CliPushModule extends ModuleBase
{

    const SUCCESS = '1';

    private $deviceTagsModel;
    private $redis;
    private $chunkRes;
    private $family;
    private $baby;
    public $di;
    public $AndroidPush;
    public $IosPush;

    /**
     * [初始化方法，获得存在redis的推送队列]
     * @param  [string] $redisKey      [取到存在redis的推送目标的key]
     * @param  [string] $redisPushBase [redis的库]
     */
    public function __construct($di, $redisKey, $redisPushBase)
    {
        $this->di = $di;
        $this->AndroidPush = new AndroidPush();
        $this->IosPush = new IosPush();
        $this->deviceTagsModel = $this->initModel('\Appserver\Mdu\Models\DevtagsModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $redisObj = new RedisLib($di);
        $this->redis = $redisObj->getRedis();
        $resultRow = $redisObj->queueIntoArray($redisKey, $redisPushBase);
        //队列值为空，则不进行推送
        if(empty($resultRow))
            exit();
        //将得到的数组信息，以100为单位分段处理，防止卡死
        $this->chunkRes = array_chunk($resultRow, 100);
    }

    /**
     * ios和安卓推送系统消息：主要是日常消息和系统消息
     * 备注：队列中的type：
     *     日常消息类型：1-到达提醒 3-离开提醒 
     *     系统消息类型：5-继续充电 11-低电提醒 13-电量为0 15-满电提醒
     */
    public function system()
    {
        echo '推送系统消息';
        $pushtime = date('Y-m-d H:i:s', time());
        //将系统消息从队列中取出组成一个新数组
        /**************************
         * 日常消息和系统消息队列的格式 ： type = 1日常消息，type = 3 系统消息
         * $redis->lPush('push:daily:system', array('babyId' => '13', 'type' => '1', content => '偏离了正常轨迹'))
         **************************/
        //准备ios推送
        $fpForIOS = $this->IosPush->readyToSendForIOS($this->di['sysconfig']['pemUrl'], $this->di['sysconfig']['passphrase'], $this->di['sysconfig']['sslUrl']);
        if(!$fpForIOS)
        {
            echo 'IOSError:建立ssl连接失败\n';
            exit();
        }
        foreach($this->chunkRes as $result)
        {
            //根据数组中的宝贝id，获取要推送的所有用户id
            foreach($result as $v)
            {
                if(isset($v['babyId']))
                {
                    $babyIds[] = $v['babyId'];
                }
            }
            $userInfo = $this->family->getAuthUids(implode(',', array_unique(array_filter($babyIds))));
            if(!empty($userInfo))
            {
                $num = sizeof($userInfo);
                $cou = sizeof($result);
                for($j=0;$j<$cou;$j++)
                {
                    for($i=0;$i<$num;$i++)
                    {
                        if($userInfo[$i]['baby_id'] == $result[$j]['babyId'])
                        {
                            $userIds[] = $userInfo[$i]['u_id'];
                            $users[] = array('u_id' => $userInfo[$i]['u_id'], 'content' => (string)trim($userInfo[$i]['family_rolename'].','.$result[$j]['content']), 'type' => (string)$result[$j]['type'],'baby_id' => (string)$result[$j]['babyId']);
                        }
                    }
                }
                //根据用户要求的推送时段进行推送
                $disturb = (string)date('H', $_SERVER['REQUEST_TIME']);
                //拿到多条token列表进行推送
                $tokens = $this->deviceTagsModel->getUserHaveCver(implode(',', array_unique(array_filter($userIds))), '3', $disturb, $disturb);
                if(empty($tokens))
                {
                    exit($pushtime . "系统消息没有用户对象可推送" . PHP_EOL);
                }
                $tokensNum = sizeof($tokens);
                $usersNum = sizeof($users);
                for($i=0;$i<$usersNum;$i++)
                {
                    for($j=0;$j<$tokensNum;$j++)
                    {
                        if($users[$i]['u_id'] == $tokens[$j]['u_id'])
                        {
                            if($users[$i]['type'] == '1' || $users[$i]['type'] == '3')
                            {
                                $type = '1';
                            }
                            elseif($users[$i]['type'] == '5' || $users[$i]['type'] == '7' || $users[$i]['type'] == '9' || $users[$i]['type'] == '11' || $users[$i]['type'] == '13' || $users[$i]['type'] == '15' || $users[$i]['type'] == '21')
                            {
                                $type = '3';
                            }
                            if($type == '1' || $type == '3')
                            {
                                if($tokens[$j]['type'] == '1')
                                {
                                    //如果deviceken存在，一条一条将消息推送到apns
                                    if($this->IosPush->sendSingleForIOS((string)$users[$i]['content'],
                                                $tokens[$j]['deviceToken'],
                                                $fpForIOS,
                                                $this->di['sysconfig']['pushKey'],
                                                (string)$type,
                                                json_encode(array('baby_id' => (string)$users[$i]['baby_id'], 'msg' => (string)$users[$i]['content'])),
                                                $pushtime,
                                                $tokens[$j]['cver']))
                                    {
                                        echo $pushtime, ':ios推送成功:babyId:', $users[$i]['baby_id'],', content:', json_encode(array('baby_id' => (string)$users[$i]['baby_id'], 'msg' => (string)$users[$i]['content'])), ',系统消息类型:', $users[$i]['type'], ',用户id:',$tokens[$j]['u_id'], "\n";
                                    }
                                    else
                                    {
                                        echo $pushtime, ':ios推送失败:babyId:', $users[$i]['baby_id'],', content:', json_encode(array('baby_id' => (string)$users[$i]['baby_id'], 'msg' => (string)$users[$i]['content'])), ',系统消息类型:', $users[$i]['type'], ',用户id:',$tokens[$j]['u_id'], "\n";
                                    }
                                }
                                elseif($tokens[$j]['type'] == '3')
                                {
                                    $pushMsg = array('type' => (string)$type, 'content' => (string)$users[$i]['content'], 'baby_id' => (string)$users[$i]['baby_id'], 'u_id' => (string)$tokens[$j]['u_id'], 'builder_id' => '1');
                                    $this->AndroidPush->sendNoticeForJPush($this->di, (string)$users[$i]['content'], $tokens[$j]['deviceToken'], $pushMsg);
                                }
                            }
                            else
                                echo $pushtime, ':不推送错误的类型:babyId:', $users[$i]['baby_id'],',系统消息类型:', $users[$i]['type'], ',用户id:',$tokens[$j]['u_id'], "\n";
                        }
                    }
                }
            }
        }
        fclose($fpForIOS);
    }

    /**
     * ios和android推送互动消息：点赞和评论的消息:用于app2.0
     * 备注：队列名：activeMsg；在队列中的type:1001:赞；1003：评论；
     * 整理后，推送中的type=5表示赞和评论
     */
    public function interact()
    {
        $pushtime = date('Y-m-d H:i:s', time());

        //根据uid重新组合一个dataByUid数组，把id相同的放在一起组成一个新数组;同时写进redis，方便轨迹消息列表获取
        foreach($this->chunkRes as $v)
        {
            foreach($v as $val)
            {
                $this->redis->lPush(sprintf($this->di['sysconfig']['tracksMsg'], $val['uid'], $val['baby_id']), $val);
            }
        }

        foreach($this->chunkRes as $result)
        {
            foreach($result as $v)
            {
                if(!isset($babyPraises[$v['baby_id']]))
                {
                    $babyPraises[$v['baby_id']] = 0;
                }
                if(!isset($babyComments[$v['baby_id']]))
                {
                    $babyComments[$v['baby_id']] = 0;
                }
                if(!isset($praises[$v['locus_id']]))
                {
                    $praises[$v['locus_id']] = 0;
                }
                if(!isset($comments[$v['locus_id']]))
                {
                    $comments[$v['locus_id']] = 0;
                }
                if($v['type'] == 1001)
                {
                    $babyPraises[$v['baby_id']] = $babyPraises[$v['baby_id']] + 1;
                    $praises[$v['locus_id']] = $praises[$v['locus_id']] + 1;
                }
                elseif($v['type'] == 1003)
                {
                    $babyComments[$v['baby_id']] = $babyComments[$v['baby_id']] + 1;
                    $comments[$v['locus_id']] = $comments[$v['locus_id']] + 1;
                }

                $uids[] = $v['uid'];

                $loucsInfo[$v['uid']][$v['baby_id']][] = array(
                        'msg' => $v['alert'],
                        'locus_id' => $v['locus_id'],
                        'praises' => (string)$praises[$v['locus_id']],
                        'comments' => (string)$comments[$v['locus_id']]);
            }

            //根据用户要求的推送时段进行推送
            $disturb = (string)date('H', $_SERVER['REQUEST_TIME']);
            //获取有上传cver的用户
            $pushInfoHaveCver = $this->deviceTagsModel->getUserHaveCver(implode(',', array_unique(array_filter($uids))), '3', $disturb, $disturb);
            
            if(!empty($pushInfoHaveCver))
            {
                //准备ios推送
                $fpForIOS = $this->IosPush->readyToSendForIOS($this->di['sysconfig']['pemUrl'], $this->di['sysconfig']['passphrase'], $this->di['sysconfig']['sslUrl']);
                if(!$fpForIOS)
                {
                    exit($pushtime . ":推送ssl连接失败:" . PHP_EOL);
                }
                //评论和赞的推送类型为5
                $type = '5';
                foreach($loucsInfo as $uid => $v)
                {
                    $babyId = (string)key($v);
                    $num = sizeof($v[$babyId]);
                    $alert = $v[$babyId][$num-1]['msg'];
                    foreach($pushInfoHaveCver as $hcver)
                    {
                        if($uid == $hcver['u_id'])
                        {
                            if($hcver['type'] == '1')
                            {
                                //=====================ios推送=============================//
                                //一条一条将消息推送到apns
                                if($this->IosPush->sendSingleForIOS($alert,
                                        $hcver['deviceToken'],
                                        $fpForIOS,
                                        $this->di['sysconfig']['pushKey'],
                                        $type,
                                        json_encode(array('baby_id' => $babyId, 'locus' => $v[$babyId])),
                                        $pushtime,
                                        $hcver['cver']))
                                {
                                    echo date('Y-m-d H:i:s', time()),':ios推送成功:用户id:',$uid,'推送类型：type:'.$type, "\n";
                                }
                                else
                                {
                                    echo date('Y-m-d H:i:s', time()), ':ios推送失败:用户id:',$uid,'推送类型：type:'.$type, "\n";
                                }
                                //=====================ios推送=============================//
                            }
                            //=====================android推送=============================//
                            elseif($hcver['type'] == '3')
                            {
                                $v[$babyId]['praflag'] = '0';
                                $v[$babyId]['commflag'] = '0';
                                if($v[$babyId][0]['praises'] != 0)
                                {
                                    $v[$babyId]['praflag'] = '1';
                                }
                                if($v[$babyId][0]['comments'] != 0)
                                {
                                    $v[$babyId]['commflag'] = '1';
                                }
                                $pushMsg = array('type' => $type, 'content' => array('baby_id' => $babyId, 'locus' => $v[$babyId] , 'praises' => (string)$babyPraises[$babyId], 'comments' => (string)$babyComments[$babyId]), 'u_id' => $uid);
                                $this->AndroidPush->sendNoticeForJPush($this->di, $alert, $hcver['deviceToken'], $pushMsg);
                            }
                        }
                    }
                }
            }
            else
                exit();
        }
    }

    /**
     * 失效推送推送：只需发送通知，让用户知悉即可，无须其他操作
     * 注：1.帐号在其他地方登录的推送
     */
    public function untoken()
    {
        $pushtime = date('Y-m-d H:i:s', time());

        foreach($this->chunkRes as $val)
        {
            foreach($val as $v)
            {
                $uids[] =  $v['uid'];
                $pushInfos[$v['deviceToken']] = $v;
            }
        }

        //根据用户要求的推送时段进行推送
        $disturb = (string)date('H', $_SERVER['REQUEST_TIME']);
        //拿到多条token列表进行推送
        $tokens = $this->deviceTagsModel->getUserByUid(implode(',', array_unique(array_filter($uids))), '3', $disturb, $disturb);
        if(empty($tokens))
        {
            exit($pushtime . "系统消息没有用户对象可推送" . PHP_EOL);
        }
        foreach($tokens as $v)
        {
            unset($pushInfos[$v['deviceToken']]);
        }

        $fpForIOS = $this->IosPush->readyToSendForIOS($this->di['sysconfig']['pemUrl'], $this->di['sysconfig']['passphrase'], $this->di['sysconfig']['sslUrl']);
        if(!$fpForIOS)
        {
            echo date('Y-m-d H:i:s', time()), ':IOSError:建立ssl连接失败:用户id'.$v['uid']."\n";
            exit();
        }
        //设置推送帐号被登录的类型为7
        $type = '7';
        foreach($pushInfos as $v)
        {
            if($v['type'] == '1')
            {
                //如果deviceken存在，一条一条将消息推送到apns
                if($this->IosPush->sendSingleForIOS($v['content'],
                        $v['deviceToken'],
                        $fpForIOS,
                        $this->di['sysconfig']['pushKey'],
                        $type,
                        '',
                        $pushtime,
                        ''))
                {
                    echo date('Y-m-d H:i:s', time()), ':ios推送成功:content:',$v['content'],',type:',$type,',用户id:',$v['uid'],"\n";
                }
                else
                {
                    echo date('Y-m-d H:i:s', time()), ':ios推送失败:content:',$v['content'],',type:',$type,',用户id:',$v['uid'],"\n";
                    continue;
                }
            }
            elseif($v['type'] == '3')
            {
                $pushMsg = array('type' => $type, 'content' => $v['content'], 'u_id' => $v['uid']);
                $this->AndroidPush->sendNoticeForJPush($this->di, $v['content'], $v['deviceToken'], $pushMsg);
            }
        }
    }
    
    /**
     * 添加亲人推送：只需发送通知，让用户知悉即可，无须其他操作
     */
    public function addfamily()
    {
        $pushtime = date('Y-m-d H:i:s', time());
        foreach($this->chunkRes as $result)
        {
            foreach($result as $v)
            {
                $uids[] = $v['uid'];
            }
            //根据用户要求的推送时段进行推送
            $disturb = (string)date('H', $_SERVER['REQUEST_TIME']);
            $pushInfo = $this->deviceTagsModel->getUserByUid(implode(',', array_filter(array_unique($uids))), '3', $disturb, $disturb);
            $num = sizeof($pushInfo);
            $cou = sizeof($result);
            for($i = 0; $i < $num; $i++)
            {
                for($j = 0;$j < $cou; $j++)
                {
                if($pushInfo[$i]['u_id'] == $result[$j]['uid'])
                {
                $pushInfo[$i]['alert'] =  $result[$j]['content'];
                $pushInfo[$i]['data'] =  $result[$j]['data'];
                $pushInfo[$i]['baby_id'] =  $result[$j]['data']['baby_id'];
                break;
        }
        }
        }
        //准备ios推送
        $fpForIOS = $this->IosPush->readyToSendForIOS($this->di['sysconfig']['pemUrl'], $this->di['sysconfig']['passphrase'], $this->di['sysconfig']['sslUrl']);
            if(!$fpForIOS)
            {
            echo time(), ':IOSError:建立ssl连接失败！',"\n";
                exit();
        }
        //设置推送亲人添加的类型为9
        $type = '9';
        foreach($pushInfo as $v)
        {
            if($v['type'] == '1')
            {
                    //如果deviceken存在，一条一条将消息推送到apns
                    if($this->IosPush->sendSingleForIOS($v['alert'],
                        $v['deviceToken'],
                        $fpForIOS,
                        $this->di['sysconfig']['pushKey'],
                        $type,
                        json_encode($v['alert']),
                        $pushtime,
                          $v['cver']))
                    {
                    echo date('Y-m-d H:i:s', time()), ':ios推送成功:content:',$v['alert'],',type:',$type,',用户id:',$v['u_id'],"\n";
                    }
                    else
                    {
                    echo date('Y-m-d H:i:s', time()), ':ios推送成功:content:',$v['alert'],',type:',$type,',用户id:',$v['u_id'],"\n";
                    continue;
                    }
                    }
                    elseif($v['type'] == '3')
                    {
                        $pushMsg = array('type' => $type, 'content' => $v['alert'], 'u_id' => $v['u_id'], 'baby_id' => $v['baby_id']);
                        $this->AndroidPush->sendNoticeForJPush($this->di, $v['alert'], $v['deviceToken'], $pushMsg);
                    }
                }
            }
        }
    
    /**
     * qq和设备绑定推送：只需发送通知，让用户知悉即可，无须其他操作
     * 注：1.帐号在其他地方登录的推送
     */
    public function devBindQQ()
    {
        $pushtime = date('Y-m-d H:i:s', time());

        foreach($this->chunkRes as $val)
            $uids[] =  $val['uid'];

        //根据用户要求的推送时段进行推送
        $disturb = (string)date('H', $_SERVER['REQUEST_TIME']);
        //拿到多条token列表进行推送
        $tokens = $this->deviceTagsModel->getUserByUid(implode(',', array_unique(array_filter($uids))), '3', $disturb, $disturb);
        if(empty($tokens))
        {
            exit($pushtime . "系统消息没有用户对象可推送" . PHP_EOL);
        }

        $fpForIOS = $this->IosPush->readyToSendForIOS($this->di['sysconfig']['pemUrl'], $this->di['sysconfig']['passphrase'], $this->di['sysconfig']['sslUrl']);
        if(!$fpForIOS)
        {
            echo date('Y-m-d H:i:s', time()), ':IOSError:建立ssl连接失败:用户id'.$v['uid']."\n";
            exit();
        }

        foreach($this->chunkRes as $v)
        {
            foreach($tokens as $val)
            {
                $type = $v['type'];
                if($v['uid'] == $val['u_id'])
                if($v['type'] == '1')
                {
                    //如果deviceken存在，一条一条将消息推送到apns
                    if($this->IosPush->sendSingleForIOS($v['content'],
                            $val['deviceToken'],
                            $fpForIOS,
                            $this->di['sysconfig']['pushKey'],
                            $type,
                            '',
                            $pushtime,
                            ''))
                    {
                        echo date('Y-m-d H:i:s', time()), ':ios推送成功:content:',$v['content'],',type:',$type,',用户id:',$v['uid'],"\n";
                    }
                    else
                    {
                        echo date('Y-m-d H:i:s', time()), ':ios推送失败:content:',$v['content'],',type:',$type,',用户id:',$v['uid'],"\n";
                        continue;
                    }
                }
                elseif($v['type'] == '3')
                {
                    $pushMsg = array('type' => $type, 'content' => $v['content']);
                    $this->AndroidPush->sendNoticeForJPush($this->di, $v['content'], $val['deviceToken'], $pushMsg);
                }
            }
        }
    }
}
