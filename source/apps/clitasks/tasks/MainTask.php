<?php

use Appserver\Mdu\Modules\CliPushModule as Push,
    Appserver\Mdu\Modules\DevicesModule as Devices,
    Appserver\Mdu\Modules\BabyModule as Baby,
    Appserver\Utils\RedisLib,
    Appserver\Utils\UserHelper,
    \Phalcon\CLI\Task as CliTask;

class MainTask extends CliTask
{

    protected $push;
    protected $di;

    public function initialize()
    {
        $this->di = $this->console->getDI(); 
    }

    /**
     * [推送系统消息]
     * @return [type] [description]
     */
    public function systemAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['dayAndSys'], $this->di['sysconfig']['redisPushBase']);
        $this->push->system();
    }

    /**
     * [推送互动消息]
     * @return [type] [description]
     */
    public function interactAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['pushForActive'], 0);
        $this->push->interact();
    }

    /**
     * [账号在其他地方登录]
     * @return [type] [description]
     */
    public function untokenAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['untoken'], 0);
        $this->push->untoken();
    }

    /**
     * [添加亲人推送]
     * @return [type] [description]
     */
    public function addfamilyAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['addfamily'], 0);
        $this->push->addfamily();
    }

    /**
     * [qq和设备绑定推送]
     */
    public function bindQQAction()
    {
        $this->push = new Push($this->di, $this->di['sysconfig']['devBindQQ'], 0);
        $this->push->devBindQQ();
    }

    /**
     * 每天定时给所有绑定鞋子的绑定增加一天的守护填数
     */
    public function incGuardsAction()
    {
        $deviceObj = new Devices;
        $babyObj = new Baby;
        $babyIds = $deviceObj->babyBinded();
        $babyObj->incGuards($babyIds['baby_ids']);
    }

    /**
     * 获取空气质量指数
     */
    public function getaqiAction()
    {
        //获取当前小时
        $hour = date('H', $_SERVER['REQUEST_TIME']);
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $aqiInfo = $redis->get('app:aqi');
        $aqiRes = json_decode($aqiInfo['data'],true);
        if($aqiInfo['time'] != $hour || empty($aqiRes))
        {
            //如果一次获取不到，则继续获取;接口一个小时只能获取15次，
            for($i=0;$i<15;$i++)
            {
                $newAqi['data'] = UserHelper::curlRequest('http://www.pm25.in/api/querys/aqi_ranking.json?&token=5Hx7pxpULLvULw6xMD5J');
                $res = json_decode($newAqi['data'],true);
                if(!empty($res) || is_array($res))
                {
                    $i=14;
                }
            }
            $newAqi['time'] = $hour;
            $redis->setex('app:aqi', 4000, $newAqi);
            exit(date('Y-m-d H:i:s', time()) . ':' . '完成获取最新的天气');
        }
        else
        {
            $redis->setex('app:aqi', 4000, $aqiInfo);
            exit(date('Y-m-d H:i:s', time()) . ':' . '获取最新的天气失败，使用的是早前的天气');
        }
    }

    /**
     * 更新任务进度
     */
    public function updateprogressAction()
    {
        $usertasksmodel = new \Appserver\Mdu\Models\UsertasksModel();

        //更新完成步行目标任务的进度
        $stepTaskInfo = $usertasksmodel->getUserByGroup($this->di['sysconfig']['taskGroup']['stepGoal']);
        if(!empty($stepTaskInfo))
        {
            $babystepsmodel = new \Appserver\Mdu\Models\BabyStepsModel();
            foreach($stepTaskInfo as $v)
            {
                $maxProgress = $babystepsmodel->getGoalCount($v['u_id'], strtotime(date('Y-m-d', $v['addtime'])));
                if(!empty($maxProgress))
                    $usertasksmodel->editProgress($v['u_id'], $this->di['sysconfig']['taskGroup']['stepGoal'], $maxProgress['count']);
            }
        }

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        //更新围栏签到任务的进度
        $fenceTaskInfo = $usertasksmodel->getUserByGroup($this->di['sysconfig']['taskGroup']['fenceCheckin']);
        if(!empty($fenceTaskInfo))
        {
            $fencesmodel = new \Appserver\Mdu\Models\FencesModel();
            foreach($fenceTaskInfo as $v)
            {
                $checkinInfo = $fencesmodel->getBabyCheckinByUid($v['u_id']);
                $progress = $checkinInfo['fence_checkin'] - intval($redis->get($this->di['sysconfig']['redisFenceCheckin']. $v['u_id'] . ':' . $v['t_id']));
                if(!empty($checkinInfo) && $progress > 0)
                {
                    $usertasksmodel->editProgress($v['u_id'],
                        $this->di['sysconfig']['taskGroup']['fenceCheckin'],
                        $progress
                    );

                }
            }
            echo '共更新' . sizeof($fenceTaskInfo) . '条任务进度';
        }
        else
            echo '没有需要更新的任务进度'. '\n';
    }
}
