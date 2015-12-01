<?php

namespace Appserver\Utils\Tasks;

use Appserver\Utils\Tasks\TasksInterface,
    Appserver\Utils\SwooleUserClient as SwooleUserClient;;

/**
 * 单任务
 *
 */
class SingleTask implements TasksInterface
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
    
    public function addTask($uid, $taskInfo)
    {
        $condition = json_decode($taskInfo['t_condition'], true);
        if(!empty($condition) && is_array($condition))
        {
            //如果添加的任务已经完成，则将用户任务状态置为3,否则置为1
            if(self::checkTaskComplete($uid, $condition))
            {
                $status = 3;
                $finishtime = $_SERVER['REQUEST_TIME'];
                $progress = 1;
                self::receive($uid, $taskInfo['t_reward']);
            }
            else
            {
                $status = 1;
                $finishtime = 0;
                $progress = 0;
            }
            
            if($this->usertasks->add($uid,
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
        else
            return self::ADD_FAILED;
    }
    
    public function completeTask($uid, $taskInfo)
    {
        //获取任务信息
        $conditionInfo = $this->tasks->getTaskByTid($taskInfo['t_id']);
        if(empty($conditionInfo))
            return self::NON_TASK;
        
        if($taskInfo['ut_progress'] >= $taskInfo['t_total'])
            $flag = 1;
        else
        {
            $condition = json_decode($conditionInfo['t_condition'], true);
            if(is_array($condition))
                $flag = self::checkTaskComplete($uid, $condition) ? 1 : 0;
            else
                return self::UPDATE_FAILED;
        }
        
        if($flag == 1)
        {
            //完成任务
            if($this->usertasks->complete($uid, $taskInfo['t_id'], $_SERVER['REQUEST_TIME']) == 0)
                return self::UPDATE_FAILED;
                
            //领取奖励
            self::receive($uid, $taskInfo['t_reward']);
            return array('flag' => self::SUCCESS, 'coins' => $taskInfo['t_reward']);
        }
        elseif($flag == 0)
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
    }
    
    public function checkTaskComplete($uid, $condition)
    {
        //查询是否满足完成任务的条件
        if($condition['field'] == 'baby')
        {
            //获取用户监护下所有宝贝
            $family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
            $babys = $family->getAuthBaby($uid);
            if(empty($babys))
                return false;

            $bids = array_column($babys, 'baby_id');
            $sql = sprintf($condition['sql'], implode(',', $bids));
        }
        elseif($condition['field'] == 'user')
        {
            $sql = sprintf($condition['sql'], $uid);
        }

        if($condition['model'] === 'fencesmodel')
            $model = '\Appserver\Mdu\Models\FencesModel';
        elseif($condition['model'] === 'babymodel')
            $model = '\Appserver\Mdu\Models\BabyModel';

        $modelObj = $this->initModel($model);
        $res = $modelObj->exec($sql);

        return !empty($res) ? TRUE : FALSE;
    }

    protected function initModel($model)
    {
        $modObj = new $model();
        return $modObj;
    }
}