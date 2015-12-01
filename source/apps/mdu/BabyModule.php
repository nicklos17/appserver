<?php
namespace Appserver\Mdu\Modules;

use Appserver\Utils\ImgUpload;

class BabyModule extends ModuleBase
{
    const SUCCESS = '1';
    const EMPTY_SHOES = 0;
    const FAILED_UPDATE = 22222;
    const FAILED_GET = 33333;
    const NON_IMG_UPLOAD =10098;
    const FAILED_ADD = 10015;
    const FAILED_EDIT = 10023;
    const FAILED_SET_STEPS = 11086;

    public $baby;
    public $family;
    public $devices;

    public function __construct()
    {
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->usertasks = $this->initModel('\Appserver\Mdu\Models\UsertasksModel');
        $this->babysteps = $this->initModel('\Appserver\Mdu\Models\BabyStepsModel');
        $this->devices = $this->initModel('\Appserver\Mdu\Models\DevicesModel');
    }

    public function addBaby($uid, $name, $sex, $birthday, $addtime, $file, $rolename, $weight = '', $height = '')
    {
        if(!empty($file['file']['tmp_name']))
        {
            $upload = new ImgUpload($this->di);
            $imageName = substr(md5($_SERVER['REQUEST_TIME'] . $uid), 8, 16);
            $rePath = substr($imageName, 0, 2) . '/' . substr($imageName, 2, 2) . '/';
            $picInfo = $upload->uploadFile($file['file'], $this->di['sysconfig']['babyPic'], $imageName, $rePath);
            if(is_numeric($picInfo))
                return $picInfo;
            else
                $pic = $this->di['sysconfig']['babyPic'] . '/' . $picInfo;
        }
        else
            return self::NON_IMG_UPLOAD;
        $this->di['db']->begin();
        if($babyId = $this->baby->add($name, $sex, $birthday, $addtime, $pic, $weight, $height))
        {
            if($this->family->addRel($babyId, $uid,empty($rolename)?'':$rolename, '1', $addtime, '1'))
            {
                $this->di['db']->commit();
                return array('flag' =>self::SUCCESS, 'baby_id' => $babyId);
            }
            else
            {
                $this->di['db']->rollback();
                return self::FAILED_ADD;
            }
        }
        else
            return self::FAILED_ADD;
    }

    /**
     * [宝贝编辑]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editBaby($data)
    {
        if(!empty($data['baby_pic']['file']['tmp_name']))
        {
            $upload = new ImgUpload($this->di);
            $imageName = substr(md5($_SERVER['REQUEST_TIME'] . $data['baby_id']), 8, 16);
            $rePath = substr($imageName, 0, 2) . '/' . substr($imageName, 2, 2) . '/';
            $picInfo = $upload->uploadFile($data['baby_pic']['file'], $this->di['sysconfig']['babyPic'], $imageName, $rePath);
            if(is_numeric($picInfo))
                return $picInfo;
            else
                $data['baby_pic'] = $this->di['sysconfig']['babyPic'] . '/' . $picInfo;
        }

        if($this->baby->edit($data))
            return array(
                'ret' => self::SUCCESS,
                'data' => isset($data['baby_pic'])? $this->di['sysconfig']['babyPicServer'] . $data['baby_pic']:''
            );
        else
            return array(
                'ret' => self::FAILED_EDIT
            );
    }

    public function getListByUidDev($uid, $count)
    {
        return $this->baby->getListByUidDev($uid, $count);
    }

    public function getListByUid($uid, $count)
    {
        return $this->baby->getListByUid($uid, $count);
    }

    public function getExpireDevs($babyId, $nowtime)
    {
        return $this->devices->getExpireDevsByBabyId($babyId, $nowtime);
    }

    public function babyIdGetShoeId($babyId)
    {
        if($this->devices->getShoeIdByBabyId($babyId))
            return self::SUCCESS;
        else
            return self::EMPTY_SHOES;
    }

    /**
     * 根据宝贝id获取宝贝的信息
     * @param int babyId
     */
    public function getBabyName($babyId)
    {
        return $this->baby->getBabyName($babyId);
    }

    /**
     * 根据宝贝id增加宝贝的守护天数(crontab控制器)
     */
    public function incGuards($babyIds)
    {
        return $this->baby->setGuardsByBabyId($babyIds);
    }

    /**
     * [设置宝贝目标步数]
     * @param [string] $uid    [用户id]
     * @param [string] $babyId [宝贝id]
     * @param [string] $steps  [设置步数]
     */
    public function setSteps($uid, $babyId, $steps)
    {
        $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));

        $this->di['db']->begin();

        if(!$this->baby->setSteps($babyId, $steps))
        {
            $this->di['db']->rollback();
            return self::FAILED_SET_STEPS;
        }

        if(!$this->babysteps->updateBabySteps($babyId, $steps, $today))
        {
            $this->di['db']->rollback();
            return self::FAILED_SET_STEPS;
        }

        $this->usertasks->updateProgress($uid, $this->di['sysconfig']['taskGroup']['setGoal'], '1');
        $this->di['db']->commit();

        return self::SUCCESS;
    }
}
