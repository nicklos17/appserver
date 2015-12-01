<?php

namespace Appserver\Utils\Tasks;

use Appserver\Utils\Tasks\TasksInterface,
    Appserver\Utils\RedisLib as RedisLib,
    Appserver\Utils\SwooleUserClient as SwooleUserClient;

/**
 * 多任务
 *
 */
class MultiTask implements TasksInterface
{

    const SUCCESS = '1';
    const NON_TASK = 11021;
    const ADD_FAILED = 11022;
    const NO_FINISHED = 11029;
    const UPDATE_FAILED = 33333;

    public $di;
    public $tasks;
    public $usertasks;

    public function __construct($di)
    {
        $this->di = $di;
        $this->tasks = $this->initModel('\Appserver\Mdu\Models\TasksModel');
        $this->usertasks = $this->initModel('\Appserver\Mdu\Models\UsertasksModel');
    }

    /**
     * [添加多任务]
     * @param [type] $uid      [description]
     * @param [type] $taskInfo [description]
     */
    public function addTask($uid, $taskInfo)
    {
        //如果领取的是围栏签到任务，则记录当前签到数，方便重新统计
        if($taskInfo['t_group'] == $this->di['sysconfig']['taskGroup']['fenceCheckin'])
        {
            $fences = $this->initModel('\Appserver\Mdu\Models\FencesModel');
            $bids = self::getAuthBaby($uid);
            if(!empty($bids))
            {
                $babyInfo = $fences->getBabyCheckin(implode(',', $bids));
                if(!empty($babyInfo))
                {
                    $redisObj = new RedisLib($this->di);
                    $redis = $redisObj->getRedis();
                    //获取该用户下所有监护宝贝签到次数
                    $maxCeckin = max(array_column($babyInfo, 'fence_checkin'));
                    if(!$redis->get($this->di['sysconfig']['redisFenceCheckin'] . $uid . ':' . $taskInfo['t_id']))
                    {
                        $redis->set($this->di['sysconfig']['redisFenceCheckin'] . $uid . ':' . $taskInfo['t_id'], $maxCeckin);
                    }
                }
            }
        }
        if($this->usertasks->add($uid,
                $taskInfo['t_id'],
                $taskInfo['tc_id'],
                $taskInfo['t_name'],
                $taskInfo['intr'],
                $taskInfo['t_type'],
                $taskInfo['t_pic'],
                1,
                $taskInfo['t_reward'],
                $_SERVER['REQUEST_TIME'],
                0,
                $taskInfo['t_total'],
                $taskInfo['t_group'],
                0))
        {
            //统计添加过该任务的人数
            $this->tasks->taskCount($taskInfo['t_id']);
            //加入redis队列，进行后台计算
            self::setRedisTasksList(array('uid' => $uid, 't_group' => $taskInfo['t_group'], 'babys' => self::getAuthBaby($uid), 'addtime' => $_SERVER['REQUEST_TIME']));
            //状态都设置为1,未完成
            return array('flag' => self::SUCCESS, 'coins' => $taskInfo['t_reward'], 'status' => '1');
        }
        else
            return self::ADD_FAILED;
    }

    /**
     * [完成任务]
     * @param  [type] $uid      [description]
     * @param  [type] $taskInfo [description]
     * @return [type]           [description]
     */
    public function completeTask($uid, $taskInfo)
    {
        if($taskInfo['ut_status'] != '1')
        {
            return self::NON_TASK;
        }
        if($taskInfo['ut_progress'] >= $taskInfo['t_total'])
        {
            //完成任务
            if($this->usertasks->complete($uid, $taskInfo['t_id'], $_SERVER['REQUEST_TIME']) == 0)
                return self::UPDATE_FAILED;
                
            //领取奖励
            self::receive($uid, $taskInfo['t_reward']);
            //删掉围栏签到任务的缓存
            if($taskInfo['t_group'] == $this->di['sysconfig']['taskGroup']['fenceCheckin'])
            {
                $redisObj = new RedisLib($this->di);
                $redis = $redisObj->getRedis();
                $redis->del($this->di['sysconfig']['redisFenceCheckin']. $uid . ':' . $taskInfo['t_id']);
            }
            
            return array('flag' => self::SUCCESS, 'coins' => $taskInfo['t_reward']);
        }
        else
        {
            //尚未完成
            return self::NO_FINISHED;
        }
    }

    /**
     * 发放任务奖励
     * @param unknown $uid
     * @param unknown $coin
     */
    public function receive($uid, $coin)
    {
        $swoole = new SwooleUserClient($this->di['sysconfig']['swooleConfig']['ip'], $this->di['sysconfig']['swooleConfig']['port']);

        //发放奖励
        $res = $swoole->checkInReceive($uid, $coin);
        if($res['data'] == 1)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 通过redis获取用户信息
     */
    public function setRedisTasksList($data)
    {
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        return $redis->lPush($this->di['sysconfig']['countProgress'], $data);
    }

    /**
     * 获取监护下的所有宝贝
     */
    public function getAuthBaby($uid)
    {
        $family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $babys = $family->getAuthBaby($uid);
        if(!empty($babys))
            return array_column($babys, 'baby_id');
        else
            return array();
    }

    protected function initModel($model)
    {
        $modObj = new $model();
        return $modObj;
    }

    public function checkTaskComplete($uid, $condition){}

}