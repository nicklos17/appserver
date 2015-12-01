<?php

namespace Appserver\Utils\Tasks;

use Appserver\Utils\RedisLib,
    Appserver\Utils\Tasks\TasksInterface,
    Appserver\Utils\SwooleUserClient as SwooleUserClient;

/**
 * 需要通过第三方求证的任务
 * 如：通过swoole查询用户编辑情况，查询排行榜名次
 *
 */
class ThirdTask implements TasksInterface
{

    const SUCCESS = '1';
    const NON_TASK = 11021;
    const ADD_FAILED = 11022;
    const NO_FINISHED = 11029;
    const UPDATE_FAILED = 33333;

    protected $di;
    protected $tasks;
    protected $usertasks;

    public function __construct($di)
    {
        $this->di = $di;
        $this->tasks = $this->initModel('\Appserver\Mdu\Models\TasksModel');
        $this->usertasks = $this->initModel('\Appserver\Mdu\Models\UsertasksModel');
    }

    public function addTask($token, $taskInfo)
    {
        //如果添加的任务已经完成，则将用户任务状态置为3,否则置为1
        $userInfo = self::checkTaskComplete($token, $taskInfo);
        if(!empty($userInfo['res']))
        {
            $status = 3;
            $finishtime = $_SERVER['REQUEST_TIME'];
            $progress = 1;
            self::receive($userInfo['uid'], $taskInfo['t_reward']);
        }
        else
        {
            $status = 1;
            $finishtime = 0;
            $progress = 0;
        }

        if($this->usertasks->add($userInfo['uid'],
                $taskInfo['t_id'],
                $taskInfo['tc_id'],
                $taskInfo['t_name'],
                $taskInfo['intr'],
                $taskInfo['t_type'],
                $taskInfo['t_pic'],
                $status,
                $taskInfo['t_reward'],
                $_SERVER['REQUEST_TIME'],
                $progress,
                $taskInfo['t_total'],
                $taskInfo['t_group'],
                $finishtime))
        {
            //统计添加过该任务的人数
            $this->tasks->taskCount($taskInfo['t_id']);
            return array('flag' => self::SUCCESS, 'coins' => $taskInfo['t_reward'], 'status' => (string)$status);
        }
        else
            return self::ADD_FAILED;
    }

    public function completeTask($token, $taskInfo)
    {
        $userInfo = self::checkTaskComplete($token, $taskInfo);
        if(!empty($userInfo['res']))
        {
            //完成任务
            if($this->usertasks->complete($userInfo['uid'], $taskInfo['t_id'], $_SERVER['REQUEST_TIME']) == 0)
                return self::UPDATE_FAILED;
            
            //领取奖励
            self::receive($userInfo['uid'], $taskInfo['t_reward']); 
            return array('flag' => '1', 'coins' => $taskInfo['t_reward']);
        }
        else
            return self::NO_FINISHED;
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
    }
    
    public function checkTaskComplete($token, $taskInfo)
    {
        if(in_array($taskInfo['t_group'], $this->di['sysconfig']['redisCheckTaskGroup']))
        {
             $userInfo = self::checkFromRedis($token);
             if(empty($userInfo))
             {
                $res = 0;
             }
             elseif($taskInfo['t_group'] == 'userEdit' && $userInfo['uname'] != '' && $userInfo['pic'] != '')
             {
                $res = 1;
             }
             elseif($taskInfo['t_group'] == 'bindQQ' && !empty($userInfo['qq_status']))
             {
                $res = 1;
             }
             elseif($taskInfo['t_group'] == 'rank')
             {
                $res = 0;
             }
             else
                $res = 0;
             
             return array('uid' => $userInfo['uid'], 'res' => $res);
        }
    }
    
    /**
     * 通过redis获取用户信息
     */
    private function checkFromRedis($token)
    {
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        return $redis->get('token:' . $token);
    }

    protected function initModel($model)
    {
        $modObj = new $model();
        return $modObj;
    }
}