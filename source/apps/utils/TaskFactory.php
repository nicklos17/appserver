<?php

namespace Appserver\Utils;

use Appserver\Utils\Tasks\SingleTask as SingleTask,
    Appserver\Utils\Tasks\MultiTask as MultiTask,
    Appserver\Utils\Tasks\ThirdTask as ThirdTask;

class TaskFactory
{

    const NON_TASK = 11021;
    const WORKING = 11030;

    public $di;
    public $tasks;
    public $usertasks;

    public function __construct()
    {
        $this->tasks = $this->initModel('\Appserver\Mdu\Models\TasksModel');
        $this->usertasks = $this->initModel('\Appserver\Mdu\Models\UsertasksModel');

        $this->di = $this->tasks->getDI();
    }
    
    /**
     * 添加任务
     * @param str $uid 用户id
     * @param str $tid 任务id
     */
    public function add($uid, $tid, $token)
    {
        //查看是否添加任务
        $tasks = $this->usertasks->checkTask($uid, $tid);
        if(!empty($tasks))
            return self::WORKING;
        //获取任务信息
        $taskInfo = $this->tasks->getTaskByTid($tid);
        if(empty($taskInfo))
            return self::NON_TASK;

        if($taskInfo['t_type'] == 1)
        {
            $obj = new SingleTask($this->di);
            return $obj->addTask($uid, $taskInfo);
        }
        elseif($taskInfo['t_type'] == 3)
        {
            $obj = new MultiTask($this->di);
            return $obj->addTask($uid, $taskInfo);
        }
        elseif($taskInfo['t_type'] == 5)
        {
            $obj = new THirdTask($this->di);
            return $obj->addTask($token, $taskInfo);
        }
    }

    /**
     * [完成任务]
     * @param  [type] $uid   [description]
     * @param  [type] $tid   [description]
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function complete($uid, $tid, $token)
    {
        $taskInfo = $this->usertasks->getTaskInfo($uid, $tid);
        if(empty($taskInfo))
            return self::NON_TASK;

        if($taskInfo['t_type'] == 1)
        {
            $obj = new SingleTask($this->di);
            return $obj->completeTask($uid, $taskInfo);
        }
        elseif($taskInfo['t_type'] == 3)
        {
            $obj = new MultiTask($this->di);
            return $obj->completeTask($uid, $taskInfo);
        }
        elseif($taskInfo['t_type'] == 5)
        {
            $obj = new THirdTask($this->di);
            return $obj->completeTask($token, $taskInfo);
        }
    }

    protected function initModel($model)
    {
        $modObj = new $model();
        return $modObj;
    }
}