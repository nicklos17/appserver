<?php

namespace Appserver\v3\Controllers;
use Appserver\Mdu\Modules\FamilyModule as Family,
    Appserver\Mdu\Modules\CaptchaModule as Captcha,
    Appserver\Mdu\Modules\BabyModule as Baby,
    Appserver\Utils\RedisLib,
    Appserver\Utils\UserHelper,
    Appserver\Utils\Common;

class FamilyController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;
    const INVALID_USER_QR = 11081;
    const NOT_REGISTER_MOBI = '00001';
    const HAVE_FAMILY_RELATION = 10077;
    const NOT_BABY_OWN = 10020;
    const GET_NIL = 22222;
    const FORBID_DEL_HOST = 10048;
    const NOT_BABY_FAMILY = 10079;
    const FAILED_SET_GUARDIAN = 10072;
    const FAILED_UNSET_GUARDIAN = 10073;
    const IS_HOST = 10071;
    const IS_NOT_HOST = 10074;
    const HAVED_HOST = 10075;
    const FAILED_GET_FAMILY_LIST = 10053;
    const NO_OAUTH = 99999;

    private $family;
    private $baby;
    private $userInfo;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->family = new Family;
        $this->baby = new Baby;
    }

    /**
     *亲人添加
     */
    public function addAction()
    {
        //获取亲人号的信息，如果为空，则说明该手机号尚未注册
        $famInfo = $this->family->getUserInfoByMobi($this->_sanReq['mobi']);

        if(!empty($famInfo) && !empty($famInfo['data']))
        {
            $famInfoByUid = $famInfo['data'];
            if($this->_sanReq['ishost'] == 5)
            {
                //查看之前是否有监护号
                $gua = $this->family->getGuaInfo($this->_sanReq['baby_id']);
                if(!empty($gua))
                    $this->_showMsg(self::HAVED_HOST, $this->di['flagmsg'][self::HAVED_HOST]);
            }

            $relation = $this->family->checkRelation($famInfoByUid['u_id'], $this->_sanReq['baby_id']);
            if($relation)
                $this->_showMsg(self::HAVE_FAMILY_RELATION, $this->di['flagmsg'][self::HAVE_FAMILY_RELATION]);

            $res = $this->family->addRel($this->_sanReq['baby_id'], $famInfoByUid['u_id'], $this->_sanReq['name'],
            $this->_sanReq['ishost'], $_SERVER['REQUEST_TIME'], 1);
            if($res == self::SUCCESS)
            {
                $babyInfo = $this->baby->getBabyName($this->_sanReq['baby_id']);
                if(!$babyInfo)
                    $this->_showMsg(self::NOT_BABY_OWN, $this->di['flagmsg'][self::NOT_BABY_OWN]);

                //组装推送数据
                //推送alert
                $content = sprintf($this->di['sysconfig']['addfamilyMsg'], $babyInfo['baby_nick'], $babyInfo['baby_nick']);
                //推送内容
                $data = array(
                    'baby_id' => (string)$this->_sanReq['baby_id'],
                    'nick' => $babyInfo['baby_nick'],
                    'baby_pic' => $this->di['sysconfig']['babyPicServer'].$babyInfo['baby_pic'],
                    'nearly' => $babyInfo['baby_nearly'],
                    'nearlyTime' => (string)$babyInfo['baby_nearlytime'],
                    'devs' => (string)$babyInfo['baby_devs'],
                    'sex' => $babyInfo['baby_sex'],
                    'relation' => (string)$this->_sanReq['ishost'],
                    'birthday' => (string)$babyInfo['baby_birthday'],
                    'battery' => (string)$babyInfo['baby_nearbattery']
                );

                $redisObj = new RedisLib($this->di);
                $redis = $redisObj->getRedis();
                $redis->lPush($this->di['sysconfig']['addfamily'],
                        json_encode(array('uid' => $famInfoByUid['u_id'],
                                'content' => $content,
                                'data' => $data,
                                'type' => '19')));
                $uStatus = '1';
            }
            else
            {
                $this->_showMsg($res, $this->di['flagmsg'][$res]);
            }
        }
        else
        {
            $this->family->remeberUnregUser($this->_sanReq['mobi'], $this->_sanReq['baby_id'], $this->_sanReq['name'], 3);
            $uStatus = '3';
        }

        $this->_returnResult(array('flag' => '1', 'u_status' => $uStatus));
    }


    /**
     * 监护号设置
     * @param str $code ： $code = set 表所设置监护号；$code = del 表示取消监护号
     * 判断用户对该宝贝是否有操作权限
     */
    public function guaAction($code = '')
    {
        $relation = $this->family->checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        if($relation['family_relation'] != '1')
            $this->_showMsg(self::NO_OAUTH, $this->di['flagmsg'][self::NO_OAUTH]);

        //判断宝贝与要添加的亲人关系，如果必须是副号才允许设置为监护人
        $famRel = $this->family->checkRelation($this->_sanReq['fam_id'], $this->_sanReq['baby_id']);

        if($code == 'set')
        {
            if($famRel['family_relation'] == '3')
            {
                $gua =  $this->family->getGuaInfo($this->_sanReq['baby_id']);
                //若存在监护号，则不让其设置
                if(!empty($gua))
                    $this->_showMsg(self::HAVED_HOST, $this->di['flagmsg'][self::HAVED_HOST]);

                if($this->family->guardian(5, $this->_sanReq['baby_id'], $this->_sanReq['fam_id']) == '1')
                {
                    $babyInfo = $this->baby->getBabyName($this->_sanReq['baby_id']);
                    if(!$babyInfo)
                        $this->_showMsg(self::NOT_BABY_OWN, $this->di['flagmsg'][self::NOT_BABY_OWN]);

                    //组装推送数据
                    //推送alert
                    $content = sprintf($this->di['sysconfig']['setGuaMsg'], $famRel['family_rolename'], $babyInfo['baby_nick']);
                    //推送内容
                    $data = array(
                        'baby_id' => (string)$this->_sanReq['baby_id'],
                        'nick' => $babyInfo['baby_nick'],
                        'baby_pic' => $this->di['sysconfig']['babyPicServer'].$babyInfo['baby_pic'],
                        'nearly' => $babyInfo['baby_nearly'],
                        'nearlyTime' => (string)$babyInfo['baby_nearlytime'],
                        'devs' => (string)$babyInfo['baby_devs'],
                        'sex' => $babyInfo['baby_sex'],
                        'relation' => (string)'5',
                        'birthday' => (string)$babyInfo['baby_birthday'],
                        'battery' => (string)$babyInfo['baby_nearbattery']
                    );

                    $redisObj = new RedisLib($this->di);
                    $redis = $redisObj->getRedis();
                    $redis->lPush($this->di['sysconfig']['addfamily'],
                            json_encode(array('uid' => $this->_sanReq['fam_id'],
                                    'content' => $content,
                                    'data' => $data,
                                    'type' => '19')));
                    $this->_returnResult(array('flag' => self::SUCCESS));
                }
                else
                    $this->_showMsg(self::FAILED_SET_GUARDIAN, $this->di['flagmsg'][self::FAILED_SET_GUARDIAN]);
            }
            else
                $this->_showMsg(self::IS_HOST, $this->di['flagmsg'][self::IS_HOST]);
        }

        elseif($code == 'del')
        {
            if($famRel['family_relation'] == '5')
            {
                if($this->family->guardian(3, $this->_sanReq['baby_id'], $this->_sanReq['fam_id']) == '1')
                    $this->_returnResult(array('flag' => self::SUCCESS));
                else
                    $this->_showMsg(self::FAILED_UNSET_GUARDIAN, $this->di['flagmsg'][self::FAILED_UNSET_GUARDIAN]);
            }
            else
                $this->_showMsg(self::IS_NOT_HOST, $this->di['flagmsg'][self::IS_NOT_HOST]);
        }
        else
            $this->_showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);
    }

    /**
     * 亲人列表
     *
     */
    public function listAction()
    {
        if($famList = $this->family->showFamList($this->_sanReq['baby_id'], $this->_sanReq['count']))
        {
            $userIds = array_map(function($v){return $v['u_id'];}, $famList);
            if(!empty($userIds))
            {
                $res = $this->family->userInfoByIds(implode(',', $userIds));
                if(empty($res['data']))
                    $this->_showMsg(self::FAILED_GET_FAMILY_LIST, $this->di['flagmsg'][self::FAILED_GET_FAMILY_LIST]);
                $userNicks = $res['data'];
                $num = sizeof($famList);
                $cou = sizeof($userNicks);
                for($i = 0; $i < $num; $i++)
                {
                    unset($famList[$i]['praises']);
                    unset($famList[$i]['comments']);
                    unset($famList[$i]['actime']);
                    for($j=0;$j<$cou;$j++)
                    {
                        if($famList[$i]['u_id'] == $userNicks[$j]['u_id'])
                        {
                            $famList[$i]['rel_pic'] = UserHelper::checkPic($this->di, $userNicks[$j]['u_pic']);
                            $famList[$i]['mobi'] = $userNicks[$j]['u_mobi'];
                            if($userNicks[$j]['u_name'] == '')
                                $famList[$i]['nick'] = $userNicks[$j]['u_mobi'];
                            else
                                $famList[$i]['nick'] = $userNicks[$j]['u_name'];
                            break;
                        }
                        else
                        {
                            $famList[$i]['rel_pic'] = '';
                            $famList[$i]['nick'] = '';
                        }
                    }
                }
            }
        }

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        //获取虚拟的用户信息
        $unregUsers = $redis->get(sprintf($this->di['sysconfig']['unregUserByBid'], $this->_sanReq['baby_id']));
        if(!empty($unregUsers))
        {
            foreach($unregUsers as $mobi => $val)
            {
                $unregList[] = array('mobi' => (string)$mobi, 'nick' => $val['uname'], 'ishost' => (string)$val['relation'], 'u_id' => '', 'name' => '', 'rel_pic' => '');
            }
            $famList = array_merge($famList, $unregList);
        }
        $this->_returnResult(array('flag' => self::SUCCESS, 'familylist' => $famList));
    }

    /**
     * 删除亲人
     * 判断用户对该宝贝是否有操作权限
     */
    public function delAction()
    {
        $this->_oauthrity($this->userInfo['uid'], $this->_sanReq['baby_id']);
        if(isset($this->_sanReq['fam_id']))
        {
            $rel = $this->family->checkRelation($this->_sanReq['fam_id'], $this->_sanReq['baby_id']);
            if(!$rel)
                $this->_showMsg(self::GET_NIL, $this->di['flagmsg'][self::GET_NIL]);
            else
            {
                if($rel['family_relation'] == 1)
                    $this->_showMsg(self::FORBID_DEL_HOST, $this->di['flagmsg'][self::FORBID_DEL_HOST]);
            }
            //如果亲人已注册，清理数据库
            if(($res = $this->family->cancelRel($this->_sanReq['baby_id'], $this->_sanReq['fam_id'],
            $_SERVER['REQUEST_TIME'])) == self::SUCCESS)
                $this->_showMsg($res);
            else
                $this->_showMsg($res, $this->di['flagmsg'][$res]);
        }
        elseif(isset($this->_sanReq['mobi']))
        {
            //如果亲人未注册，清理缓存
            $redisObj = new RedisLib($this->di);
            $redis = $redisObj->getRedis();
            $data = $redis->get(sprintf($this->di['sysconfig']['unregUserByBid'], $this->_sanReq['baby_id']));
            if(!empty($data))
            {
                unset($data[$this->_sanReq['mobi']]);
                if(!empty($data))
                    $redis->set(sprintf($this->di['sysconfig']['unregUserByBid'], $this->_sanReq['baby_id']), $data);
                else
                    $redis->del(sprintf($this->di['sysconfig']['unregUserByBid'], $this->_sanReq['baby_id']));
            }
            $userData = $redis->get(sprintf($this->di['sysconfig']['unregUser'], $this->_sanReq['mobi']));
            if(!empty($userData))
            {
                unset($userData[$this->_sanReq['baby_id']]);
                if(!empty($userData))
                    $redis->set(sprintf($this->di['sysconfig']['unregUser'], $this->_sanReq['mobi']), $userData);
                else
                    $redis->del(sprintf($this->di['sysconfig']['unregUser'], $this->_sanReq['mobi']));
            }

            $this->_showMsg(self::SUCCESS);
        }
    }
}
