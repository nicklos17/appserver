<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\CliPushModule as Push;

class PushmsgController extends ControllerBase
{

    public function initialize()
    {
        set_time_limit(0);
    }

    /**
     * [推送系统消息]
     * @return [type] [description]
     */
    public function SystemAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['dayAndSys'], $this->di['sysconfig']['redisPushBase']);
        $this->push->system();
    }

    /**
     * [推送互动消息]
     * @return [type] [description]
     */
    public function InteractAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['pushForActive'], 0);
        $this->push->interact();
    }

    /**
     * [账号在其他地方登录]
     * @return [type] [description]
     */
    public function UntokenAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['untoken'], 0);
        $this->push->untoken();
    }

    /**
     * [添加亲人推送]
     * @return [type] [description]
     */
    public function AddfamilyAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['addfamily'], 0);
        $this->push->addfamily();
    }

    /**
     * [qq和设备绑定推送]
     */
    public function BindQQAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['devBindQQ'], 0);
        $this->push->devBindQQ();
    }

}
