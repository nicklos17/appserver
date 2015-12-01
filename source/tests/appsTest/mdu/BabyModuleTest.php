<?php
namespace Test\mdu;

use Appserver\Mdu\Modules\BabyModule as BabyModule;

class BabyModuleTest extends \UnitTestCase
{
    const SUCCESS = '1';
    const EMPTY_SHOES = 0;
    const FAILED_UPDATE = 22222;
    const FAILED_GET = 33333;
    const NON_IMG_UPLOAD =10098;
    const FAILED_ADD = 10015;
    const FAILED_EDIT = 10023;

    const BABY_ID = 24;
    const BABY_NAME = '云朵';

    protected $mdu;

    public function __construct()
    {
        parent::setUp();
        $this->mdu = new BabyModule;
        $this->mdu->baby = new \Appserver\Mdu\Models\BabyModel;
        $this->mdu->family = new \Appserver\Mdu\Models\FamilyModel;
        $this->mdu->devices = new \Appserver\Mdu\Models\DevicesModel;
    }

    public function testAddBaby()
    {
        $babyId = 1000;
        $babyName = '云朵baby';

        $birthday = mktime(0, 0, 0, 12, 4, 2014);
        $addtime = time();
        // 无头像
        $this->assertEquals(self::NON_IMG_UPLOAD, $this->mdu->addBaby($babyId, $babyName, 1, $birthday, time(), '', ''));
        // 有头像
        // $this->assertEquals(self::SUCCESS, $this->mdu->addBaby(1, 'babyName', 1, $birthday, time(), array('file'=>array('tmp_name' => 'avatar')), ''));
    }

    /**
     * @dependsd testAddBaby
     */
    public function testEditBaby()
    {
        $editData = array(
            'baby_id' => 1000,
            'baby_nick' => '云朵baby_new'
        );

        // 无头像
        $this->assertEquals(array('ret' => self::SUCCESS, 'data' => ''), $this->mdu->editBaby($editData));
        // 有头像
        $editData['baby_pic'] = 'newBabyAvatar';
        $this->assertEquals(array('ret' => self::SUCCESS, 'data' => $this->di['sysconfig']['babyPicServer'] . $editData['baby_pic']), $this->mdu->editBaby($editData));
    }

    public function testGetListByUidDev()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 1000;
        $notExistsUid = 1001;
        $this->assertCount(0, $this->mdu->getListByUidDev($notExistsUid, 20));
        $this->assertCount(1, $this->mdu->getListByUidDev($uid, 20));
    }

    public function testGetListByUid()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $uid = 1003;
        $notExistsUid = 1004;
        $this->assertCount(0,$this->mdu->getListByUid($notExistsUid, 20));
        $this->assertCount(1,$this->mdu->getListByUid($uid, 20));
    }

    public function testGetExpireDevs()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $devId = 100;
        $babyId = 1005;
        $expires = 0;
        $expireDevs = array(0 => array('dev_id' => $devId, 'dev_expires' => $expires));

        $this->assertEquals($expireDevs, $this->mdu->getExpireDevs($babyId, $_SERVER['REQUEST_TIME']));
    }

    public function testBabyIdGetShoeId()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        // 鞋子不存在
        $notExistsShoeId = 1006;
        $this->assertEquals(self::EMPTY_SHOES, $this->mdu->babyIdGetShoeId($notExistsShoeId));

        // 鞋子存在
        $existsShoeId = 1007;
        $this->assertEquals(self::SUCCESS, $this->mdu->babyIdGetShoeId($existsShoeId));
    }

    public function testGetBabyName()
    {
        $this->initData(__CLASS__, __FUNCTION__);
        $babyId = 1008;
        $babyNick = '云朵baby';
        $this->assertEquals($babyNick, $this->mdu->getBabyName($babyId)['baby_nick']);
    }
}
