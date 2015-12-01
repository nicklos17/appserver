<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\DevicesModule as Devices,
       Appserver\Mdu\Modules\BabyModule as Baby;

class CrontabController extends ControllerBase
{

    private $baby;
    private $devices;

    public function initialize()
    {
        $this->baby = new Baby;
        $this->devices = new Devices;
    }

    /**
     * 每天定时给所有绑定鞋子的绑定增加一天的守护填数
     */
    public function incGuardsAction()
    {
        $babyIds = $this->devices->babyBinded();
        $this->baby->incGuards($babyIds['baby_ids']);
    }
}