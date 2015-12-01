<?php

 namespace Appserver\Mdu\Modules;

use Appserver\Utils\Common,
    Appserver\Utils\ImgUpload;


class UnionModule extends ModuleBase
{
    const SUCCESS = '1';
    const NON_EXIST_SHOE = 10034;
    const ADDED_SHOE = 10032;

    const FAILED_UPDATE_DATA = 10040;
    const SHOE_BINDED = 10037;
    const FAILED_UNBIND = 10036;
    const EMPTY_GET = 0;
    const FAILED_CHANGE_MODE = 10039;
    const FAILED_DEL_DEV = 10029;
    const SHOE_ADDED = 10032;
    const FAILED_ADD_DEV = 10015;
    const NEW_USER = '1';
    const NOT_NEW_USER = '3';
    const NON_IMG_UPLOAD = '55555';
    const FAILED_ADD = 10046;

    private $baby;
    private $family;
    private $devices;

    public function __construct()
    {
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->devices = $this->initModel('\Appserver\Mdu\Models\DevicesModel');
    }

    /**
     * [联合操作，添加童鞋，添加宝贝并绑定]
     * @param [type] $uid      [description]
     * @param [type] $nick     [description]
     * @param [type] $sex      [description]
     * @param [type] $birthday [description]
     * @param [type] $addtime  [description]
     * @param [type] $shoeQr   [description]
     * @param [type] $rolename [description]
     * @param [type] $file     [description]
     */
    public function add($uid, $nick, $sex, $birthday, $addtime, $shoeQr, $rolename, $file, $weight = '', $height = '')
    {
        if(!empty($file['file']['tmp_name']))
        {
            $upload = new ImgUpload($this->di);
            $imageName = substr(md5(uniqid(true)), 8, 16);
            $rePath = substr($imageName, 0, 2) . '/' . substr($imageName, 2, 2);
            $picInfo = $upload->uploadFile($file['file'], $this->di['sysconfig']['babyPic'], $imageName, $rePath);
            if(is_numeric($picInfo))
                return $picInfo;
            else
                $pic = $this->di['sysconfig']['babyPic'] . '/' . $picInfo;
        }
        else
            return self::NON_IMG_UPLOAD;

        //判断鞋子是否存在
        $shoeInfo = $this->devices->getDevInfoByQr($shoeQr);
        if(!$shoeInfo)
            return self::NON_EXIST_SHOE;
        //开始计算服务期,如果值为0，则第一次添加
        if(empty($shoeInfo['expire']))
            $expires = Common::expires($_SERVER['REQUEST_TIME']);
        else
            $expires = $shoeInfo['expire'];

        $this->di['db']->begin();
        //添加宝贝
        $babyId = $this->baby->add($nick, $sex, $birthday, $addtime, $pic, $weight, $height, '1');
        if(!$babyId)
        {
            $this->di['db']->rollback();
            return self::FAILED_ADD;
        }
        if(!$this->family->addRel($babyId, $uid, empty($rolename)?'':$rolename, '1', $addtime, '1'))
        {
            $this->di['db']->rollback();
            return self::FAILED_ADD;
        }
        //设备过期就不让添加
        if($_SERVER['REQUEST_TIME'] < $expires)
        {
            $devId = $this->devices->addShoe($uid, $shoeInfo['uuid'], $shoeInfo['imei'],
                $shoeInfo['mobi'], $shoeInfo['pass'], $shoeInfo['dver'], $expires, $shoeInfo['qr'],
                $shoeInfo['pic'], $_SERVER['REQUEST_TIME'], $babyId);
            if(!$babyId)
            {
                $this->di['db']->rollback();
                return self::FAILED_ADD;
            }
            if(!$this->devices->updateExpires($shoeInfo['uuid'], $expires))
            {
                $this->di['db']->rollback();
                return self::FAILED_ADD;
            }
            $this->di['db']->commit();
            return array('flag' => '1',
                'shoe_info' => array('shoe_id' => (string)$devId),
                'baby_info' => array('baby_id' => (string)$babyId, 'baby_pic' => $this->di['sysconfig']['babyPicServer'] . $pic)
            );

        }
        else
        {
            $this->di['db']->commit();
            return array(
                'flag' => '11077',
                'target' => 'target',
                'expires' => $expires,
                'msg' => $this->di['flagmsg']['11077'],
                'tel'=> $this->di['sysconfig']['service-phone']
            );
        }
    }

    /**
     * [检查用户是否既没有添加过宝贝也没有添加过童鞋]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function checkuser($uid, $ver)
    {
        if($ver == '0.1')
        {
            $devInfo = $this->devices->getDevByUid($uid);
            $relInfo = $this->family->checkRelByUid($uid);

            //既没有添加过宝贝也没有添加过童鞋
            if(empty($devInfo) && empty($relInfo))
                return array('flag' => self::SUCCESS, 'isnew' => self::NEW_USER);
            else
                return array('flag' => self::SUCCESS, 'isnew' => self::NOT_NEW_USER);
        }
        elseif($ver == '0.3')
        {
            if(empty($this->family->getBabysByUid($uid, '1')))
                return array('flag' => self::SUCCESS, 'isnew' => self::NEW_USER);
            else
                return array('flag' => self::SUCCESS, 'isnew' => self::NOT_NEW_USER);
        }
    }
}
