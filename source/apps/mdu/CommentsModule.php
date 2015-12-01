<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib,
    Appserver\Utils\SwooleUserClient,
    Appserver\Utils\UserHelper;

class CommentsModule extends ModuleBase
{
    const SUCCESS = '1';
    const NON_LOCUS = 10062;
    const NO_OAUTH = 99999;
    const NON_REPLY_USER = 10089;
    const FAILED_COMMENT = 11065;
    const NOT_USER_COMMENT = 10066;
    const FAILED_UPDATE = 33333;
    const FAILED_DEL = 10067;

    public $commentModel;
    public $locusModel;
    public $familyModel;
    public $babyModel;


    public function __construct()
    {
        $this->commentModel = $this->initModel('\Appserver\Mdu\Models\CommentsModel');
        $this->locusModel = $this->initModel('\Appserver\Mdu\Models\LocusModel');
        $this->familyModel = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->babyModel = $this->initModel('\Appserver\Mdu\Models\BabyModel');
    }

    /**
     * [添加评论]
     * @param [string`] $token    [用户登录token]
     * @param [string`] $locusId  [轨迹id]
     * @param [string`] $content  [评论内容]
     * @param [string`] $replyUid [被回复人id]
     */
    public function add($token, $locusId, $content, $replyUid)
    {

        //调用swoole
        $swoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']
        );

        //获取轨迹对应的宝贝id
        $locusInfo = $this->locusModel->getLocateInfo($locusId);
        if(empty($locusInfo))
            return self::NON_LOCUS;

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $userInfo = $redis->get('token:' . $token);

        //获取评论人关系和角色名
        $rel = $this->familyModel->getRelationByUidBabyId($userInfo['uid'], $locusInfo['baby_id']);
        if(empty($rel))
            return self::NO_OAUTH;

        $uname = $rel['family_rolename'] == '' ? $userInfo['mobi'] :  $rel['family_rolename'];
        //获取宝贝信息

        $babyInfo = $this->babyModel->getBabyInfoById($locusInfo['baby_id']);
        if(empty($babyInfo))
            return self::NON_LOCUS;

        //被回复人姓名,当不是评论时,该字段为空
        $lcName = '';
        //如果被回复人id有值，则说明这是一条回复
        if(!empty($replyUid))
        {
            //获取被回复人的称呼，如果没有则取昵称，如果没有昵称则获取手机号
            $replyRelInfo = $this->familyModel->getRelationByUidBabyId($replyUid, $locusInfo['baby_id']);
            if($replyRelInfo['family_rolename'] == '')
            {
                $replyUserInfo = $swoole->userInfoByIds($replyUid);
                if(empty($replyUserInfo['data']))
                    return self::NON_REPLY_USER;
                $lcName = $replyUserInfo['data']['u_name'] ? $replyUserInfo['data']['u_name'] : $replyUserInfo['u_mobi'];
            }
            else
                $lcName = $replyRelInfo['family_rolename'];
        }
        $this->di['db']->begin();
        if($commId = $this->commentModel->addComment(
            $userInfo['uid'],
            $uname,
            $locusId,
            $content,
            $_SERVER['REQUEST_TIME'],
            $replyUid,
            $lcName
        ))
        {
            //评论发表后，更新轨迹表里的评论总数
            if(!$this->locusModel->setCommentNum($locusId))
            {
                $this->di['db']->rollback();
                return self::FAILED_COMMENT;
            }
            //评论发表后，更新宝贝关系表里的评论总数
            if(!$this->familyModel->incCommentNum($userInfo['uid'], $locusInfo['baby_id'], $_SERVER['REQUEST_TIME']))
            {
                $this->di['db']->rollback();
                return self::FAILED_COMMENT;
            }

            $this->di['db']->commit();
            //计算评论成功后评论的数量，返回
            $count = $locusInfo['comments'] +1;

            //type = 1001到代表赞， type = 1003代表评论
            $result = array('rel_id' => (string)$userInfo['uid'],
                                    'type' => '1003',
                                    'addtime' => (string)$_SERVER['REQUEST_TIME'],
                                    'content' => $content,
                                    'rel_name' => $uname,
                                    'rel_pic' => $userInfo['pic'],
                                    'baby_id' => (string)$locusInfo['baby_id'],
                                    'locus_id' => (string)$locusId);

            //推送给$uid: $uid是宝贝对应的主号，或者监护号或者是主号和监护号的集合,如果uid为空数组，则不用执行推送的操作
            $uid = $this->getPushUid($locusId, $userInfo['uid']);
            if(!empty($uid))
            {
                $num = sizeof($uid);
                $redis = RedisLib::getRedis();
                for($i=0;$i<$num;$i++)
                {
                    if($uid[$i] != '')
                    {
                        $result['uid'] = $uid[$i];
                        $result['alert'] = sprintf($this->di['sysconfig']['commentPush'],  $babyInfo['baby_nick'], $uname,  $babyInfo['baby_nick'], date('m月d日', $locusInfo['locus_date']));
                        $redis->lPush($this->di['sysconfig']['pushForActive'], json_encode($result));
                    }
                }
            }

            if(!empty($replyUid))
            {
                //推送：如果是回复，而且回复给副号，则也要推送给对应的副号
                $rel = $this->familyModel->checkRelation($replyUid, $locusInfo['baby_id']);
                if(!empty($rel))
                {
                    if($rel['family_relation'] == 3)
                    {
                        $result['uid'] = $replyUid;
                        $result['alert'] = $this->di['sysconfig']['replyPush'];
                        $redis->lPush($this->di['sysconfig']['pushForActive'], $result);
                    }
                }
            }
        }
        else
            return self::FAILED_COMMENT;

        return array('flag' => '1', 'comments' => (string)$count, 'lc_id' => $commId, 'addtime' => (string)$_SERVER['REQUEST_TIME']);
    }

    /**
     * [删除评论]
     * @param  [type] $uid  [description]
     * @param  [type] $lcid [description]
     * @return [type]       [description]
     */
    public function delComment($uid, $lcid)
    {
        if(!$this->commentModel->getLcidByUid($uid, $lcid))
            return self::NOT_USER_COMMENT;

        $locusId = $this->commentModel->getLocusByLcid($lcid);

        if($locusId['locus_id'])
        {
            $this->di['db']->begin();
            //更新轨迹表里面的评论总数
            if(!$this->locusModel->cancelComment($locusId['locus_id']))
                return self::FAILED_UPDATE;

            $babyId = $this->locusModel->getBabyIdByLsId($locusId['locus_id']);

            //更新家庭关系表的评论总数 -1
            if(!$this->familyModel->subCommentNum($uid, $babyId['baby_id']))
            {
                $this->di['db']->rollback();
                return self::FAILED_UPDATE;
            }

            if(!$this->commentModel->delComment($lcid))
            {
                $this->di['db']->rollback();
                return self::FAILED_DEL;
            }

            $this->di['db']->commit();

            //计算点完赞后赞的数量，返回
            $count = $this->locusModel->getCommentAndPraiseCount($locusId['locus_id']);
            if(!$count)
                $comments = 0;
            else
                $comments = $count['comments'];

            return array('flag' => self::SUCCESS, 'comments' => (string)$comments);
        }
        else
            return self::NON_LOCUS;
    }

    public function getCommentList($locusId, $count, $maxId)
    {
        if($maxId == '')
            $commList = $this->commentModel->getCommentList($locusId, $count);
        //根据maxId翻页
        else
            $commList = $this->commentModel->getCommentBymaxId($locusId, $maxId, $count);

        //返回评论的数量
        $count = $this->locusModel->getCommentAndPraiseCount($locusId);
        if(!$count)
            $comments = 0;
        else
            $comments = $count['comments'];

        $userIds = array_map(function($v){return $v['u_id'];}, $commList);

        if(!empty($userIds))
        {
            $swoole = new SwooleUserClient(
                $this->di['sysconfig']['swooleConfig']['ip'],
                $this->di['sysconfig']['swooleConfig']['port']
            );
            $userInfos = $swoole->userInfoByIds(implode(',', $userIds))['data'];
            $listNum = sizeof($commList);
            $picNum = sizeof($userInfos);
            for($i=0;$i<$listNum;$i++)
            {
                for($j=0;$j<$picNum;$j++)
                {
                    if($commList[$i]['u_id'] == $userInfos[$j]['u_id'])
                    {
                        $commList[$i]['u_pic'] = UserHelper::checkPic($this->di, $userInfos[$j]['u_pic']);
                        if($userInfos[$j]['u_name'] == '')
                            $commList[$i]['u_name'] = $userInfos[$j]['u_mobi'];
                        else
                            $commList[$i]['u_name'] = $userInfos[$j]['u_name'];
                        break;
                    }
                }
            }
        }
        //将result重新排序，addtime较小的排在前面
        usort($commList, function($a, $b){return strcmp($a['time'], $b['time']);});
        return array('flag' => self::SUCCESS, 'commlist' => $commList, 'comments' => $comments);
    }
}