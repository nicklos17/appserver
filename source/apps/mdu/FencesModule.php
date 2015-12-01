<?php

namespace Appserver\Mdu\Modules;

class FencesModule extends ModuleBase
{
    const SUCCESS = '1';
    const FAILED_UPDATE_FENCES = 10080;
    const FAILED_DEL_FENCES = 10081;
    // const FAILED_GET = 33333;

    protected $fences;

    public function __construct()
    {
        $this->fences = $this->initModel('\Appserver\Mdu\Models\FencesModel');
    }

    public function addFences($babyId, $coordinates, $name, $radius, $place, $validtime, $addTime)
    {
        if($this->fences->add($babyId, $coordinates, $name, $radius, $place, $validtime, $addTime))
            return self::SUCCESS;
        else
            return self::FAILED_UPDATE_FENCES;
    }

    public function editFences($fenceId, $coordinates, $name, $radius, $place, $validtime, $addTime)
    {
        if($this->fences->edit($fenceId, $coordinates, $name, $radius, $place, $validtime, $addTime))
            return self::SUCCESS;
        else
            return self::FAILED_UPDATE_FENCES;
    }

    public function delFences($fencesId, $delTime)
    {
        if($this->fences->del($fencesId, $delTime))
            return self::SUCCESS;
        else
            return self::FAILED_DEL_FENCES;
    }

    /**
     * 围栏列表显示
     * @param str $babyId
     */
    public function showFenList($babyId, $count)
    {
        return $this->fences->getFenList($babyId, $count);
    }
    
}

