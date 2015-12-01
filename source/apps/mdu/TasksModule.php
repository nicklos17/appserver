<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\TaskFactory as TaskFactory;

class TasksModule extends ModuleBase
{

    const SUCCESS = '1';
    const DEL_FAILED = 11023;
    const INVALID_OPERATE = 11111;

    public $tasks;
    public $usertasks;

    public function __construct()
    {
        $this->tasks = $this->initModel('\Appserver\Mdu\Models\TasksModel');
        $this->usertasks = $this->initModel('\Appserver\Mdu\Models\UsertasksModel');
    }

    /**
     * [返回所有任务分类]
     * @return [type] [description]
     */
    public function getCats()
    {
        return $this->tasks->getTaskCats();
    }

    /**
     * [获取任务列表]
     * @param  [type] $uid  [description]
     * @param  [type] $tcid [description]
     * @return [type]       [description]
     */
    public function getTasksList($uid, $tcid)
    {
        //获取用户已领取的任务
        $received = $this->usertasks->receivedTasks($uid, $tcid);
        
        //如果tcid为1,则为系统任务，系统任务需要全部显示;非系统任务根据进度显示
        if($tcid == '1')
        {
            $tasklist = $this->tasks->getListByTcid($tcid);
            if(!empty($received))
            {
                $taskStatus = array();
                foreach($received as $v)
                {
                    $taskStatus[$v['t_id']] = $v['status'];
                    $tids[] = $v['t_id'];
                }
                foreach($tasklist as $k => $val)
                {
                    if(in_array($val['t_id'], $tids))
                    {
                        $tasklist[$k]['status'] = $taskStatus[$val['t_id']];
                    }
                    else
                    {
                        $tasklist[$k]['status'] = '5';
                    }
                    
                    $tasklist[$k]['t_pic'] = $this->di['sysconfig']['tasksPicServer'] . $val['t_pic'];
                }
            }
            else
            {
                foreach($tasklist as $k => $value)
                {
                    $tasklist[$k]['status'] = '5';
                    $tasklist[$k]['t_pic'] = $this->di['sysconfig']['tasksPicServer'] . $value['t_pic'];
                }
            }

        }
        else
        {
            //如果已领取，设置status = 1;已完成status=3;如果未领取，设置status = 5
            if(!empty($received))
            {
                //取出各任务组最进添加的任务信息
                $lastInfo = $this->usertasks->getLastTaskByGroup($uid, $tcid);
                $where = '';
                $noGroup = '';
                //拼凑sql语句
                foreach($lastInfo as $k => $v)
                {
                    $noGroup .= 't_group != "'. $v['t_group'] .'" AND ';
                    if($v['ut_status'] == 3)
                    {
                        $where .= '(t_group="' . $v['t_group'] . '" AND t_total >' . $v['t_total'] . ') OR ';
                    }
                }
                if(!empty($where))
                    $where = substr($where, 0, -4) . ' OR (' . substr($noGroup, 0, -5) .')';
                else
                    $where = substr($noGroup, 0, -5);
                
                //获取未领取，但是可以领取的任务
                $noAddTask = $this->tasks->getTaskByWhere($tcid, $where);
                if(!empty($noAddTask))
                {
                    foreach($noAddTask as $k => $v)
                    {
                        $noAddTask[$k]['status'] = '5'; 
                    }
                    $tasklist = array_merge($noAddTask, $received);
                }
                else
                {
                    $tasklist = $received;
                }
                
                foreach($tasklist as $k=>$v)
                {
                    $tasklist[$k]['t_pic'] = $this->di['sysconfig']['tasksPicServer'] . $v['t_pic'];
                }
            }
            else
            {
                //用户没有领取任何任务，取出各任务组第一条返回
                $tasklist = $this->tasks->getLastTaskByGroup($tcid);
                foreach($tasklist as $k=>$v)
                {
                    $tasklist[$k]['status'] = '5';
                    $tasklist[$k]['t_pic'] = $this->di['sysconfig']['tasksPicServer'] . $v['t_pic'];
                }
            }
        }

        return array('flag' => '1', 'tasklist' => $tasklist);
    }

    /**
     * [返回用户任务列表]
     * @param  [string] $segment       [segment为空，返回用户进行中的和未完成的任务列表，seg为1返回已完成的任务列表]
     * @param  [type] $count     [返回的任务个数]
     * @param  [type] $sinceTime [要查询的起始时间]
     * @param  [type] $maxTime   [要查询的最大时间]
     * @return [type]            [description]
     */
    public function getUserTasksList($segment, $uid, $count, $sinceTime, $maxTime)
    {
        if($sinceTime == 0 && $maxTime == 0)
        {
            if($segment == '')
            {
                $result = $this->usertasks->getUserTasks($uid, 1, $count);
                foreach($result as $k => $v)
                {
                    if($v['t_total'] > 1)
                    {
                        $result[$k]['name'] = $v['name'] . '('. $v['ut_progress'] .'/' . $v['t_total'] . ')';
                    }
                }
            }
            //segment=1,返回用户已完成的任务列表
            elseif($segment == 1)
                $result = $this->usertasks->getUserTasks($uid, 3, $count);
        }
        elseif($sinceTime != 0 && $maxTime == 0)
        {
            if($segment == '')
             {
                $result = $this->usertasks->getUserTasksBySTime($uid, 1, $count, $sinceTime);
                foreach($result as $k => $v)
                {
                    if($v['t_total'] > 1)
                    {
                        $result[$k]['name'] = $v['name'] . '('. $v['ut_progress'] .'/' . $v['t_total'] . ')';
                    }
                }
             }
            //segment=1,返回用户已完成的任务列表
            elseif($segment == 1)
                $result = $this->usertasks->getUserTasksBySTime($uid, 3, $count, $sinceTime);
        }
        elseif($sinceTime == 0 && $maxTime != 0)
        {
            if($segment == '')
            {
               $result = $this->usertasks->getUserTasksByMTime($uid, 1, $count, $maxTime);
               foreach($result as $k => $v)
                {
                    if($v['t_total'] > 1)
                    {
                        $result[$k]['name'] = $v['name'] . '('. $v['ut_progress'] .'/' . $v['t_total'] . ')';
                    }
                }
            }
            //segment=1,返回用户已完成的任务列表
            elseif($segment == 1)
                $result = $this->usertasks->getUserTasksByMTime($uid, 3, $count, $maxTime);
        }
        foreach($result as $k=>$v)
        {
            $result[$k]['pic'] = $this->di['sysconfig']['tasksPicServer'] . $v['pic'];
        }
        return array('flag' =>self::SUCCESS, 'usertasks' => $result);
    }

    /**
     * [添加任务]
     * @param [string] $uid   [用户id]
     * @param [string] $tid   [任务id]
     * @param [string] $token [登录token]
     */
    public function addTask($uid, $tid, $token)
    {
        $taskObj = new TaskFactory();
        return $taskObj->add($uid, $tid, $token);
    }

    /**
     * [完成任务]
     * @param  [type] $uid   [用户id]
     * @param  [type] $tid   [任务id]
     * @param  [type] $token [登录token]
     */
    public function completeTask($uid, $tid, $token)
    {
        $taskObj = new TaskFactory();
        return $taskObj->complete($uid, $tid, $token);
    }

    /**
     * [删除任务]
     * @param  [type] $uid [description]
     * @param  [type] $tid [description]
     * @return [type]      [description]
     */
    public function delTask($uid, $tid)
    {
        if($this->usertasks->del($uid, $tid))
            return self::SUCCESS;
        else
            return self::DEL_FAILED;
    }
}