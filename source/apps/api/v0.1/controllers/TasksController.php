<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\TasksModule as Tasks;

class TasksController extends ControllerBase
{

    const SUCCESS = '1';

    protected $userInfo;
    protected $tasks;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->tasks = new Tasks;
    }

    /**
     * [任务分类]
     * @return [type] [description]
     */
    public function catsAction()
    {
        $res = $this->tasks->getCats();
        foreach ($res as $k => $val)
        {
            $res[$k]['tc_pic'] = $this->di['sysconfig']['url'] . $val['tc_pic'];
        }
        $this->_returnResult(array('flag' => self::SUCCESS, 'cats' => $res));
    }

    /**
     * [任务列表]
     */
    public function listAction()
    {
        $this->_returnResult($this->tasks->getTasksList($this->userInfo['uid'], $this->_sanReq['tc_id']));
    }

    /**
     * [添加任务]
     */
    public function addAction()
    {
        $res = $this->tasks->addTask($this->userInfo['uid'], $this->_sanReq['t_id'], $this->_sanReq['token']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [删除任务]
     */
    public function delAction()
    {
        $res = $this->tasks->delTask($this->userInfo['uid'], $this->_sanReq['t_id']);
        if($res == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);

    }

    /**
     * [完成任务]
     */
    public function completeAction()
    {
        $res = $this->tasks->completeTask($this->userInfo['uid'], $this->_sanReq['t_id'], $this->_sanReq['token']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [用户任务列表]
     */
    public function userAction($seg = '')
    {
        $res = $this->tasks->getUserTasksList(
            $seg,
            $this->userInfo['uid'],
            $this->_sanReq['count'],
            isset($this->_sanReq['since_time']) ? $this->_sanReq['since_time'] : '',
            isset($this->_sanReq['max_time']) ? $this->_sanReq['max_time'] : ''
        );
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}